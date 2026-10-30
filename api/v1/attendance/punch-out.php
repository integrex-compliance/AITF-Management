<?php
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/attendance_security.php';
require_once __DIR__ . '/../helpers/attendance_punch.php';
attendanceEndpoint(function () use ($db) { processAttendancePunch($db, 'punch_out'); });
