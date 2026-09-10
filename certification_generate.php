<?php

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

function getNextAllotmentSequence($db, $customerId, $year, $month) {
    $month = str_pad((int)$month, 2, '0', STR_PAD_LEFT);
    $year = (string)(int)$year;
    $monthName = date('M', mktime(0, 0, 0, (int)$month, 1));
    $yy = substr($year, -2);
    $monthPrefix = 'AITF/' . $monthName . '-' . $yy . '/%';

    // 1. Get max client_count from allotment_count as a number.
    $maxCountFromAllotment = 0;
    $sql1 = "SELECT MAX(CAST(client_count AS UNSIGNED)) as max_count FROM allotment_count WHERE year = :year AND CAST(month AS UNSIGNED) = :month";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindParam(':year', $year);
    $monthIntForCount = (int)$month;
    $stmt1->bindParam(':month', $monthIntForCount, PDO::PARAM_INT);
    $stmt1->execute();
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    if ($row1 && $row1['max_count'] !== null) {
        $maxCountFromAllotment = (int)$row1['max_count'];
    }

    // 2. Get max sequence from certification_aggrement for the same Agreement month/year.
    // Explicitly exclude RC agreements so RC recertifications never bump new client sequence
    $maxCountFromAgreement = 0;
    $sql2 = "SELECT aggrement_no FROM certification_aggrement WHERE YEAR(aggrement_date) = :year AND MONTH(aggrement_date) = :month AND aggrement_no LIKE :prefix AND aggrement_no NOT LIKE '%Rc-%' AND aggrement_no NOT LIKE '%-RC-%'";
    $stmt2 = $db->prepare($sql2);
    $monthInt = (int)$month;
    $stmt2->bindParam(':year', $year);
    $stmt2->bindParam(':month', $monthInt, PDO::PARAM_INT);
    $stmt2->bindParam(':prefix', $monthPrefix);
    $stmt2->execute();
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $row2) {
        $agNo = $row2['aggrement_no'];
        if ($agNo && preg_match('/^AITF\/' . preg_quote($monthName, '/') . '-' . preg_quote($yy, '/') . '\/(\d+)$/i', $agNo, $matches)) {
            $seq = (int)$matches[1];
            if ($seq > $maxCountFromAgreement) {
                $maxCountFromAgreement = $seq;
            }
        }
    }

    $candidate = max($maxCountFromAllotment, $maxCountFromAgreement) + 1;

    // Loop to ensure candidate number doesn't match any existing allotment number
    while (true) {
        $candidateNo = 'AITF/' . $monthName . '-' . $yy . '/' . $candidate;
        
        $sqlCheck = "SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :no";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindParam(':no', $candidateNo);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->fetch();
        
        if (!$resCheck || (int)$resCheck['count'] === 0) {
            break; // Unique!
        }
        $candidate++;
    }

    return $candidate;
}

function formatAllotmentNo($year, $month, $sequence) {
    $monthName = date('M', mktime(0, 0, 0, (int)$month, 1));
    return 'AITF/' . $monthName . '-' . substr((string)$year, -2) . '/' . (int)$sequence;
}

function acquireAllotmentLock($db, $year, $month) {
    $lockName = 'aitf_allotment_' . (int)$year . '_' . str_pad((int)$month, 2, '0', STR_PAD_LEFT);
    $stmt = $db->prepare("SELECT GET_LOCK(:lock_name, 10) AS locked");
    $stmt->bindParam(':lock_name', $lockName);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || (int)$row['locked'] !== 1) {
        throw new Exception('Unable to reserve allotment number. Please try again.');
    }

    return $lockName;
}

function releaseAllotmentLock($db, $lockName) {
    if (!$lockName) {
        return;
    }

    $stmt = $db->prepare("SELECT RELEASE_LOCK(:lock_name)");
    $stmt->bindParam(':lock_name', $lockName);
    $stmt->execute();
}

if (isset($_POST["certificateid"]) && !empty($_POST["certificateid"])) {
    $allotmentLockName = null;
    try {
        // Check if all required POST variables are set
        if (!isset($_POST["newRegistrationDate"]) || !isset($_POST["allotmentNo"]) || !isset($_POST["csId"]) || !isset($_POST["newCount"])) {
            header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
            exit;
        }
        
        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
        $orderdate = explode('-', $date);
        $day = $orderdate[0];
        $month = $orderdate[1];
        $year = $orderdate[2];
        $allotmentLockName = acquireAllotmentLock($db, $year, $month);
        
        $isRcAgreement = (bool)preg_match('/\bRc-\d+/i', $_POST['allotmentNo']);
        if (!$isRcAgreement && isset($_POST["service"]) && !empty($_POST["service"])) {
            $serviceTypeCheck = "SELECT service_type FROM service WHERE service_id = :sid";
            $stmtCheck = $db->prepare($serviceTypeCheck);
            $stmtCheck->bindParam(':sid', $_POST["service"]);
            $stmtCheck->execute();
            $svcRow = $stmtCheck->fetch();
            if ($svcRow && ($svcRow['service_type'] == 'RC' || $svcRow['service_type'] == 'CR')) {
                $isRcAgreement = true;
            }
        }

        // Validation to prevent duplicate allotment numbers in Case A (Edit)
        if (!$isRcAgreement) {
            $checkSql = "SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :altno AND id != :id";
            $stmtCheck = $db->prepare($checkSql);
            $stmtCheck->bindParam(':altno', $_POST['allotmentNo']);
            $stmtCheck->bindParam(':id', $_POST["certificateid"]);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->fetch();
            if ($resCheck && (int)$resCheck['count'] > 0) {
                // It is a duplicate! Recalculate a unique one
                $allocatedCount = getNextAllotmentSequence($db, $_POST["csId"], $year, $month);
                $_POST['allotmentNo'] = formatAllotmentNo($year, $month, $allocatedCount);
                $_POST['newCount'] = $allocatedCount;
            }
        }

        $sql = "UPDATE `certification_aggrement` SET `aggrement_date`= :date ,`aggrement_no`= :no  WHERE `id` = :id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST["certificateid"]);
        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
        $statement->bindParam(":no", $_POST['allotmentNo']);
        $statement->execute();

        // Update unique_id in customer_info table
        $updateUniqueId = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
        $statement = $db->prepare($updateUniqueId);
        $statement->bindParam(':allotment_agreement', $_POST['allotmentNo']);
        $statement->bindParam(':id', $_POST['csId']);
        $statement->execute();

        // ONLY update allotment_count for base client allotments (NEVER for RC agreements)
        if (!$isRcAgreement) {
            $cleanCount = null;
            if (preg_match('/\/(\d+)$/', $_POST['allotmentNo'], $seqMatch)) {
                $cleanCount = (int)$seqMatch[1];
            } else if (isset($_POST["newCount"]) && is_numeric($_POST["newCount"])) {
                $cleanCount = (int)$_POST["newCount"];
            }

            if ($cleanCount !== null) {
                $updateCount = "UPDATE allotment_count SET year = :year, month = :month, certification_date = :date, client_count = :count WHERE client_id = :customer";
                $statement = $db->prepare($updateCount);
                $statement->bindParam(":year", $year);
                $statement->bindParam(":month", $month);
                $statement->bindParam(":count", $cleanCount);
                $statement->bindParam(":customer", $_POST['csId']);
                $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
                $statement->execute();
            }
        }

        releaseAllotmentLock($db, $allotmentLockName);
        $allotmentLockName = null;
        header("Location: customerAccount.php?customerid=" . $_POST['csId']);
    } catch (Exception $ex) {
        releaseAllotmentLock($db, $allotmentLockName);
        error_log('Allotment update failed: ' . $ex->getMessage());
        header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
    }
} else {
    $allotmentLockName = null;
    try {
        // Check if all required POST variables are set
        if (!isset($_POST["service"]) || !isset($_POST["csId"]) || !isset($_POST["newRegistrationDate"]) || !isset($_POST["allotmentNo"])) {
            header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
            exit;
        }

        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
        $orderdate = explode('-', $date);
        $day = $orderdate[0];
        $month = $orderdate[1];
        $year = $orderdate[2];
        $allotmentLockName = acquireAllotmentLock($db, $year, $month);
        
        // Check if this service is an RC or CR service
        $serviceTypeCheck = "SELECT service_type FROM service WHERE service_id = :sid";
        $stmtCheck = $db->prepare($serviceTypeCheck);
        $stmtCheck->bindParam(':sid', $_POST["service"]);
        $stmtCheck->execute();
        $serviceTypeResult = $stmtCheck->fetch();
        $isRCService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC');
        $isCRService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'CR');
        
        // Check if customer has previous CA/SA services (for CR)
        $hasPreviousServices = false;
        if ($isCRService) {
            $previousServiceSql = "SELECT COUNT(*) as service_count FROM service WHERE customer_id = :cid AND service_type IN ('CA', 'SA-I', 'SA-II') AND service_id < :current_sid";
            $prevStmt = $db->prepare($previousServiceSql);
            $prevStmt->bindParam(':cid', $_POST["csId"]);
            $prevStmt->bindParam(':current_sid', $_POST["service"]);
            $prevStmt->execute();
            $prevResult = $prevStmt->fetch();
            $hasPreviousServices = ($prevResult && $prevResult['service_count'] > 0);
        }

        $client = "SELECT count(id) as id FROM allotment_count where client_id = :id";
        $statement = $db->prepare($client);
        $statement->bindParam(":id", $_POST["csId"]);
        $statement->execute();
        $row = $statement->fetch();

        $isRcAgreement = ($isRCService || ($isCRService && $hasPreviousServices) || preg_match('/\bRc-\d+/i', $_POST['allotmentNo']));
        $shouldAllocateBaseAllotment = (!$isRcAgreement);
        
        // Always store the allotment as provided (including Rc-X suffix when generated for RC/CR)
        $allotmentToStore = $_POST["allotmentNo"];
        $allocatedCount = null;

        if (!$row["id"] && $shouldAllocateBaseAllotment) {
            $allocatedCount = getNextAllotmentSequence($db, $_POST["csId"], $year, $month);
            $allotmentToStore = formatAllotmentNo($year, $month, $allocatedCount);
        } else if ($shouldAllocateBaseAllotment) {
            // Validate to prevent duplicate allotment numbers for any other base allotments
            $checkSql = "SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :altno";
            $stmtCheck = $db->prepare($checkSql);
            $stmtCheck->bindParam(':altno', $allotmentToStore);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->fetch();
            if ($resCheck && (int)$resCheck['count'] > 0) {
                $allocatedCount = getNextAllotmentSequence($db, $_POST["csId"], $year, $month);
                $allotmentToStore = formatAllotmentNo($year, $month, $allocatedCount);
            }
        } else {
            // RC or CR after CA/SA: Never overwrite with a monthly sequence!
            // If duplicate exists, increment the RC suffix (e.g. Rc-1 -> Rc-2)
            $checkSql = "SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :altno";
            $stmtCheck = $db->prepare($checkSql);
            $stmtCheck->bindParam(':altno', $allotmentToStore);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->fetch();
            if ($resCheck && (int)$resCheck['count'] > 0) {
                if (preg_match('/^(.*)\s+Rc-(\d+)$/i', $allotmentToStore, $rcMatches)) {
                    $base = $rcMatches[1];
                    $nextRc = (int)$rcMatches[2] + 1;
                    while (true) {
                        $candidateRc = $base . ' Rc-' . $nextRc;
                        $stmtDup = $db->prepare("SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :cno");
                        $stmtDup->execute([':cno' => $candidateRc]);
                        $dupRes = $stmtDup->fetch();
                        if (!$dupRes || (int)$dupRes['count'] === 0) {
                            $allotmentToStore = $candidateRc;
                            break;
                        }
                        $nextRc++;
                    }
                }
            }
        }

        $sqlcertagrment = "INSERT INTO `certification_aggrement`(`service`, `customer_id`, `aggrement_date`, `aggrement_no`) VALUES (:svc, :customer, :date, :altno)";
        $statement = $db->prepare($sqlcertagrment);
        $statement->bindParam(":svc", $_POST["service"]);
        $statement->bindParam(":customer", $_POST["csId"]);
        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
        $statement->bindParam(":altno", $allotmentToStore);
        $statement->execute();
        
        // Only update customer_info.unique_id for first-time services (not RC or CR after CA/SA)
        if (!$row["id"] && $shouldAllocateBaseAllotment) {
            $updateNextService = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':allotment_agreement', $allotmentToStore);
            $statement->bindParam(':id', $_POST['csId']);
            $statement->execute();

            $insertCount = "INSERT INTO allotment_count (year, month, client_count, client_id, certification_date) VALUES (:year, :month, :count, :customer, :date)";
            $statement = $db->prepare($insertCount);
            $statement->bindParam(":year", $year);
            $statement->bindParam(":month", $month);
            $statement->bindParam(":count", $allocatedCount);
            $statement->bindParam(":customer", $_POST['csId']);
            $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
            $statement->execute();
        }
        releaseAllotmentLock($db, $allotmentLockName);
        $allotmentLockName = null;
        header("Location: customerAccount.php?customerid=" . $_POST['csId']);
    } catch (Exception $ex) {
        releaseAllotmentLock($db, $allotmentLockName);
        error_log('Allotment insert failed: ' . $ex->getMessage());
        header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
    }
}
//if (isset($_POST['csId']) && !empty($_POST['csId']) && isset($_POST["generate"]) && !empty($_POST["generate"]) && $_POST["generate"] == "create")  {
//
//    $regDate = $_POST["newRegistrationDate"];
//    $alotno = $_POST['allotmentNo'];
//
//    $updateNextService = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
//    $statement = $db->prepare($updateNextService);
//    $statement->bindParam(':allotment_agreement', $_POST['allotmentNo']);
//    $statement->bindParam(':id', $_POST['csId']);
//    $statement->execute();
//
//    $count = "SELECT count(id) as id FROM allotment_count where client_id = :id";
//    $statement = $db->prepare($count);
//    $statement->bindParam(":id", $_POST['csId']);
//    $statement->execute();
//    $counter = $statement->fetch();
//    if (!$counter["id"] > 0) {
//        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
//        $orderdate = explode('-', $date);
//        $day = $orderdate[0];
//        $month = $orderdate[1];
//        $year = $orderdate[2];
//
//        $insertCount = "INSERT INTO allotment_count (year, month, client_count, client_id, certification_date) VALUES (:year, :month, :count, :customer, :date)";
//        $statement = $db->prepare($insertCount);
//        $statement->bindParam(":year", $year);
//        $statement->bindParam(":month", $month);
//        $statement->bindParam(":count", $_POST["newCount"]);
//        $statement->bindParam(":customer", $_POST['csId']);
//        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
//        $statement->execute();
//    } else {
//        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
//        $orderdate = explode('-', $date);
//        $day = $orderdate[0];
//        $month = $orderdate[1];
//        $year = $orderdate[2];
//
//        $updateCount = "UPDATE allotment_count SET year = :year, month = :month, certification_date = :date, client_count = :count WHERE client_id = :customer";
//        $statement = $db->prepare($updateCount);
//        $statement->bindParam(":year", $year);
//        $statement->bindParam(":month", $month);
//        $statement->bindParam(":count", $_POST["newCount"]);
//        $statement->bindParam(":customer", $_POST['csId']);
//        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
//        $statement->execute();
//    }
//} 
