<?php
function processAttendancePunch(PDO $db, string $purpose): void {
    date_default_timezone_set('Asia/Kolkata');
    $user = requireAuth($db);
    $uid = (int)$user['user_id'];
    $sys = $user['system_code'];
    $challenge = consumeAttendanceChallenge($db, $user, $purpose, $_POST['challenge_id'] ?? null);
    $location = attendanceLocation($_POST);
    $captureTime = attendanceTimestamp($_POST['selfie_captured_at'] ?? null, 120);
    if ($location['captured_at']->getTimestamp() < $captureTime->getTimestamp() - 30) {
        throw new AttendanceRejected('Acquire a fresh GPS fix after capturing your selfie.');
    }
    $bio = $db->prepare('SELECT enrollment_photo_url, is_verified FROM aitf_user_biometrics WHERE user_id = ? AND system_code = ?');
    $bio->execute([$uid, $sys]);
    $baseline = $bio->fetch(PDO::FETCH_ASSOC);
    if (!$baseline || (int)$baseline['is_verified'] !== 1) throw new AttendanceRejected('Complete face enrollment before marking attendance.', 409);
    if (!empty($_FILES['selfie_image']) && empty($_FILES['selfie_start'])) {
        $singleImage = attendanceUpload('selfie_image');
        $images = [$singleImage, $singleImage, $singleImage];
    } else {
        $images = [attendanceUpload('selfie_start'), attendanceUpload('selfie_turn'), attendanceUpload('selfie_image')];
    }
    $verified = runAttendanceVerifier($images, $challenge['direction'], attendanceBaselinePath($baseline['enrollment_photo_url']));
    $photo = saveAttendancePhoto($images[2]);
    $committed = false;
    try {
        $db->beginTransaction();
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $stmt = $db->prepare('SELECT id, in_time, out_time, status, in_address, in_latitude, in_longitude FROM aitf_attendance
            WHERE user_id = ? AND system_code = ? AND attendance_date = ? FOR UPDATE');
        $stmt->execute([$uid, $sys, $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($purpose === 'punch_in' && $record && !empty($record['in_time'])) {
            throw new AttendanceRejected('You have already clocked in today. Refresh your attendance.', 409);
        }
        if ($purpose === 'punch_out' && (!$record || empty($record['in_time']))) {
            throw new AttendanceRejected('Clock in before clocking out.', 409);
        }
        if ($purpose === 'punch_out' && !empty($record['out_time'])) {
            throw new AttendanceRejected('You have already clocked out today. Refresh your attendance.', 409);
        }
        $evidenceId = saveAttendanceEvidence($db, $user, $purpose, $challenge, $location, $captureTime, $photo, $verified);
        // Photo is only readable through an authenticated endpoint.
        $photoUrl = 'attendance/selfie.php?evidence_id=' . $evidenceId;
        if ($purpose === 'punch_in') {
            $status = $time <= '09:45:00' ? 'present' : ($time <= '11:00:00' ? 'late' : 'halfday');
            $label = ['present' => 'Present', 'late' => 'Late Login', 'halfday' => 'Half Day'][$status];
            $params = [$time, $status, $photoUrl, $verified['similarity_score'], $location['latitude'],
                $location['longitude'], $location['address'], 'Photo and GPS checked'];
            if ($record) {
                $stmt = $db->prepare('UPDATE aitf_attendance SET in_time = ?, status = ?, punch_in_selfie_url = ?,
                    face_match_score = ?, in_latitude = ?, in_longitude = ?, in_address = ?, remarks = ?, updated_at = NOW()
                    WHERE id = ? AND in_time IS NULL');
                $params[] = $record['id'];
            } else {
                $stmt = $db->prepare('INSERT INTO aitf_attendance
                    (in_time, status, punch_in_selfie_url, face_match_score, in_latitude, in_longitude, in_address, remarks,
                     user_id, system_code, attendance_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                array_push($params, $uid, $sys, $date);
            }
            $stmt->execute($params);
            if ($stmt->rowCount() !== 1) throw new AttendanceRejected('Attendance changed. Refresh today\'s status.', 409);
            $response = [
                'date' => $date, 
                'in_time' => date('h:i A', strtotime($time)),
                'status' => $status, 
                'status_label' => $label,
                'in_address' => $location['address'],
                'clock_in_location' => $location['address'],
                'in_latitude' => $location['latitude'],
                'in_longitude' => $location['longitude']
            ];
        } else {
            $seconds = time() - strtotime($date . ' ' . $record['in_time']);
            if ($seconds < 0) throw new AttendanceRejected('Punch-in time is invalid. Contact your administrator.', 409);
            $hours = round($seconds / 3600, 2);
            $stmt = $db->prepare('UPDATE aitf_attendance SET out_time = ?, total_working_hours = ?,
                out_latitude = ?, out_longitude = ?, out_address = ?, punch_out_selfie_url = ?, updated_at = NOW()
                WHERE id = ? AND out_time IS NULL');
            $stmt->execute([$time, $hours, $location['latitude'], $location['longitude'], $location['address'], $photoUrl, $record['id']]);
            if ($stmt->rowCount() !== 1) throw new AttendanceRejected('Attendance changed. Refresh today\'s status.', 409);
            $response = [
                'date' => $date, 
                'out_time' => date('h:i A', strtotime($time)),
                'working_hours' => $hours, 
                'status' => $record['status'], 
                'status_label' => 'Clocked Out',
                'out_address' => $location['address'],
                'clock_out_location' => $location['address'],
                'out_latitude' => $location['latitude'],
                'out_longitude' => $location['longitude'],
                'in_address' => $record['in_address'] ?? null,
                'clock_in_location' => $record['in_address'] ?? null,
                'in_latitude' => isset($record['in_latitude']) ? (float)$record['in_latitude'] : null,
                'in_longitude' => isset($record['in_longitude']) ? (float)$record['in_longitude'] : null
            ];
        }
        $db->commit();
        $committed = true;

        // Sync with tenant database (e.g. qmscare) so web dashboard updates instantly
        if (isset($GLOBALS['aitf_identity_db']) && $GLOBALS['aitf_identity_db'] instanceof PDO && $sys !== 'AITF_MAIN') {
            try {
                $tenantDb = $GLOBALS['aitf_identity_db'];
                if ($purpose === 'punch_in') {
                    $tStmt = $tenantDb->prepare("
                        INSERT INTO aitf_attendance 
                        (user_id, system_code, attendance_date, status, in_time, in_latitude, in_longitude, in_address, punch_in_selfie_url, face_match_score, remarks)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        status = VALUES(status), in_time = VALUES(in_time), in_latitude = VALUES(in_latitude),
                        in_longitude = VALUES(in_longitude), in_address = VALUES(in_address),
                        punch_in_selfie_url = VALUES(punch_in_selfie_url), face_match_score = VALUES(face_match_score)
                    ");
                    $tStmt->execute([$uid, $sys, $date, $status, $time, $location['latitude'], $location['longitude'], $location['address'], $photoUrl, $verified['similarity_score'], 'Mobile punch in']);
                } else {
                    $tStmt = $tenantDb->prepare("
                        UPDATE aitf_attendance SET
                        out_time = ?, total_working_hours = ?, out_latitude = ?, out_longitude = ?, out_address = ?, punch_out_selfie_url = ?, updated_at = NOW()
                        WHERE user_id = ? AND attendance_date = ?
                    ");
                    $tStmt->execute([$time, $hours, $location['latitude'], $location['longitude'], $location['address'], $photoUrl, $uid, $date]);
                }
            } catch (Throwable $e) {
                error_log("Tenant attendance sync notice: " . $e->getMessage());
            }
        }
    } finally {
        if (!$committed) {
            if ($db->inTransaction()) $db->rollBack();
            @unlink(attendanceBaselinePath($photo));
        }
    }
    $response['similarity_score'] = $verified['similarity_score'];
    $response['location_accuracy_m'] = $location['location_accuracy_m'];
    $response['selfie_url'] = $photoUrl;
    $response['evidence_id'] = $evidenceId;
    jsonResponse(true, 'Attendance recorded successfully.', $response);
}
