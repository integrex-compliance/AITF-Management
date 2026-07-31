<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
include_once './include/certification_cycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

function getNextAllotmentSequence($db, $customerId, $year, $month)
{
    $month = str_pad((int) $month, 2, '0', STR_PAD_LEFT);
    $year = (string) (int) $year;
    $monthName = date('M', mktime(0, 0, 0, (int) $month, 1));
    $yy = substr($year, -2);
    $monthPrefix = 'AITF/' . $monthName . '-' . $yy . '/%';

    // 1. Get max client_count from allotment_count as a number.
    $maxCountFromAllotment = 0;
    $sql1 = "SELECT MAX(CAST(client_count AS UNSIGNED)) as max_count FROM allotment_count WHERE year = :year AND CAST(month AS UNSIGNED) = :month";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindParam(':year', $year);
    $monthIntForCount = (int) $month;
    $stmt1->bindParam(':month', $monthIntForCount, PDO::PARAM_INT);
    $stmt1->execute();
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    if ($row1 && $row1['max_count'] !== null) {
        $maxCountFromAllotment = (int) $row1['max_count'];
    }

    // 2. Get max sequence from certification_aggrement for the same Agreement month/year.
    $maxCountFromAgreement = 0;
    $sql2 = "SELECT aggrement_no FROM certification_aggrement WHERE YEAR(aggrement_date) = :year AND MONTH(aggrement_date) = :month AND aggrement_no LIKE :prefix";
    $stmt2 = $db->prepare($sql2);
    $monthInt = (int) $month;
    $stmt2->bindParam(':year', $year);
    $stmt2->bindParam(':month', $monthInt, PDO::PARAM_INT);
    $stmt2->bindParam(':prefix', $monthPrefix);
    $stmt2->execute();
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $row2) {
        $agNo = $row2['aggrement_no'];
        if ($agNo && preg_match('/^AITF\/' . preg_quote($monthName, '/') . '-' . preg_quote($yy, '/') . '\/(\d+)(?:\s+Rc-\d+)?$/i', $agNo, $matches)) {
            $seq = (int) $matches[1];
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

        if (!$resCheck || (int) $resCheck['count'] === 0) {
            break; // Unique!
        }
        $candidate++;
    }

    return $candidate;
}

if (isset($_POST['customerEmail']) && !empty($_POST['customerEmail'])) {
    try {
        $checkCustomer = "SELECT count(*) as exist from customer_info where email_id = '" . $_POST['customerEmail'] . "'";
        $statement = $db->prepare($checkCustomer);
        $statement->execute();
        $customerCount = $statement->fetch();
        if ($customerCount['exist'] > 0) {
            echo 1;
        } else {
            echo 0;
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET['customerName']) && !empty($_GET['customerName']) && isset($_GET["customerphone"]) && !empty($_GET["customerphone"])) {
    try {
        $chkCustomerName = "SELECT count(*) as exist from customer_info where customer_name = '" . $_GET['customerName'] . "' and contact = '" . $_GET["customerphone"] . "'";
        $statement = $db->prepare($chkCustomerName);
        $statement->execute();
        $customerNameCount = $statement->fetch();
        if ($customerNameCount['exist'] > 0) {
            echo 1;
        } else {
            echo 0;
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["contactNo"]) && !empty($_GET["contactNo"]) && isset($_GET["customername"]) && !empty($_GET["customername"])) {
    try {
        $chkCustomerContact = "SELECT count(*) as exist from customer_info where contact = '" . $_GET['contactNo'] . "' and customer_name = '" . $_GET["customername"] . "'";
        $statement = $db->prepare($chkCustomerContact);
        $statement->execute();
        $customerContactCount = $statement->fetch();
        if ($customerContactCount['exist'] > 0) {
            echo 1;
        } else {
            echo 0;
        }
    } catch (Exception $ex) {

    }
} else if (isset($_POST['taxcation']) && !empty($_POST['taxcation'])) {
    try {
        $checkTaxcation = "SELECT count(*) as exist from taxation where type_of_tax  = '" . $_POST['taxcation'] . "'";
        $statement = $db->prepare($checkTaxcation);
        $statement->execute();
        $userCount = $statement->fetch();
        if ($userCount['exist'] > 0) {
            echo 1;
        } else {
            echo 0;
        }
    } catch (Exception $ex) {

    }
} else if (isset($_POST['auditor']) && !empty($_POST['auditor'])) {
    try {
        $checkAuditor = "SELECT count(*) as exist from auditors where auditor_name  = '" . $_POST['auditor'] . "'";
        $statement = $db->prepare($checkAuditor);
        $statement->execute();
        $userCount = $statement->fetch();
        if ($userCount['exist'] > 0) {
            echo 1;
        } else {
            echo 0;
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["nextDate"]) && !empty($_GET["nextDate"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["nextDate"]));
        $orderdate = explode('-', $date);
        $month = $orderdate[1];
        $day = $orderdate[2];
        $year = $orderdate[0];

        $clientCount = "SELECT client_count, certification_date FROM allotment_count WHERE year= :year and CAST(month AS UNSIGNED) = :month ORDER BY CAST(client_count AS UNSIGNED) DESC LIMIT 1";
        $statement = $db->prepare($clientCount);
        $statement->bindParam(":year", $year);
        $monthInt = (int) $month;
        $statement->bindParam(":month", $monthInt, PDO::PARAM_INT);
        $statement->execute();
        $count = $statement->fetch();
        //echo "new date -> ".$date. " Prev date -> ". $count["certification_date"];
        if ($count["certification_date"] > $date) {
            echo "1";
        } else {
            $date = date("Y-m-d", strtotime($_GET["nextDate"]));
            //         $custid="select customer_id from service where service_id = :id";
//         $statement = $db->prepare($custid);
//            $statement->bindParam(":id", $_GET["serviceid"]);
//            $statement->execute();
//            $custid = $statement->fetch();

            $latestService = "SELECT service_id, service_type FROM service WHERE customer_id = :id ";
            $statement = $db->prepare($latestService);
            $statement->bindParam(":id", $_GET["custid"]);
            $statement->execute();
            $latestSer = $statement->fetchall();

            $max = 0;
            foreach ($latestSer as $ls) {
                if ($ls['service_type'] == 'CR') {
                    if ($ls['service_id'] > $max)
                        $max = $ls['service_id'];
                }
            }


            $auditReviewDate = "SELECT review_date FROM audit_application where uni_svc_id = :id";
            $statement = $db->prepare($auditReviewDate);
            $statement->bindParam(":id", $max);
            $statement->execute();
            $dateVal = $statement->fetch();

            //	echo "new date-> ".$date." prev date -> ". $dateVal["review_date"];
            if ($dateVal["review_date"] > $date) {
                echo "2";
            }
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["checkServiceType"]) && $_GET["checkServiceType"] == "true" && isset($_GET["serviceid"])) {
    // Check if a service is an RC/CR/CA type
    // Note: serviceid could be cr_id, ca_id, rc_id, etc.
    try {
        error_log("🔍 checkServiceType called for service_id: " . $_GET["serviceid"]);

        // Try to find the service type by checking all service type tables
        $serviceType = null;

        // Check service_type_cr table
        $crSql = "SELECT s.service_type FROM service_type_cr cr 
                  INNER JOIN service s ON cr.service = s.service_id 
                  WHERE cr.cr_id = :sid";
        $statement = $db->prepare($crSql);
        $statement->bindParam(':sid', $_GET["serviceid"]);
        $statement->execute();
        $result = $statement->fetch();

        if ($result && isset($result['service_type'])) {
            $serviceType = $result['service_type'];
            error_log(" Found in CR table, service_type: " . $serviceType);
        } else {
            // Check service_type_ca table
            $caSql = "SELECT s.service_type FROM service_type_ca ca 
                      INNER JOIN service s ON ca.service = s.service_id 
                      WHERE ca.ca_id = :sid";
            $statement = $db->prepare($caSql);
            $statement->bindParam(':sid', $_GET["serviceid"]);
            $statement->execute();
            $result = $statement->fetch();

            if ($result && isset($result['service_type'])) {
                $serviceType = $result['service_type'];
                error_log(" Found in CA table, service_type: " . $serviceType);
            } else {
                // Check service_type_rc table
                $rcSql = "SELECT s.service_type FROM service_type_rc rc 
                          INNER JOIN service s ON rc.service = s.service_id 
                          WHERE rc.rc_id = :sid";
                $statement = $db->prepare($rcSql);
                $statement->bindParam(':sid', $_GET["serviceid"]);
                $statement->execute();
                $result = $statement->fetch();

                if ($result && isset($result['service_type'])) {
                    $serviceType = $result['service_type'];
                    error_log(" Found in RC table, service_type: " . $serviceType);
                }
            }
        }

        if ($serviceType) {
            echo json_encode(array('service_type' => $serviceType));
        } else {
            error_log(" Service not found in any service_type table with ID: " . $_GET["serviceid"]);
            echo json_encode(array('service_type' => null, 'error' => 'Service not found', 'service_id_checked' => $_GET["serviceid"]));
        }
    } catch (Exception $ex) {
        error_log(" Exception in checkServiceType: " . $ex->getMessage());
        echo json_encode(array('service_type' => null, 'error' => $ex->getMessage()));
    }
} else if (isset($_GET["checkPreviousServices"]) && $_GET["checkPreviousServices"] == "true" && isset($_GET["customerId"]) && isset($_GET["serviceid"])) {
    // Check if customer has previous CA/SA services
    // Note: serviceid could be cr_id, ca_id, rc_id, etc., need to get actual service_id
    try {
        error_log(" checkPreviousServices called for customer: " . $_GET["customerId"] . ", serviceid: " . $_GET["serviceid"]);

        // First, get the actual service_id from the service_type table
        $actualServiceId = null;

        // Check CR table
        $crSql = "SELECT service FROM service_type_cr WHERE cr_id = :sid";
        $statement = $db->prepare($crSql);
        $statement->bindParam(':sid', $_GET["serviceid"]);
        $statement->execute();
        $result = $statement->fetch();

        if ($result && isset($result['service'])) {
            $actualServiceId = $result['service'];
        } else {
            // Check CA table
            $caSql = "SELECT service FROM service_type_ca WHERE ca_id = :sid";
            $statement = $db->prepare($caSql);
            $statement->bindParam(':sid', $_GET["serviceid"]);
            $statement->execute();
            $result = $statement->fetch();

            if ($result && isset($result['service'])) {
                $actualServiceId = $result['service'];
            } else {
                // Check RC table
                $rcSql = "SELECT service FROM service_type_rc WHERE rc_id = :sid";
                $statement = $db->prepare($rcSql);
                $statement->bindParam(':sid', $_GET["serviceid"]);
                $statement->execute();
                $result = $statement->fetch();

                if ($result && isset($result['service'])) {
                    $actualServiceId = $result['service'];
                }
            }
        }

        error_log(" Actual service_id found: " . ($actualServiceId ? $actualServiceId : 'NONE'));

        if ($actualServiceId) {
            // Now check for previous CA/SA services
            $previousServiceSql = "SELECT COUNT(*) as service_count FROM service WHERE customer_id = :cid AND service_type IN ('CA', 'SA-I', 'SA-II') AND service_id < :current_sid";
            $statement = $db->prepare($previousServiceSql);
            $statement->bindParam(':cid', $_GET["customerId"]);
            $statement->bindParam(':current_sid', $actualServiceId);
            $statement->execute();
            $result = $statement->fetch();

            $hasPrevious = ($result && $result['service_count'] > 0);
            error_log(" Has previous CA/SA services: " . ($hasPrevious ? 'YES' : 'NO') . " (count: " . ($result['service_count'] ?? 0) . ")");

            echo json_encode(array('has_previous' => $hasPrevious));
        } else {
            error_log(" Could not find actual service_id for id: " . $_GET["serviceid"]);
            echo json_encode(array('has_previous' => false, 'error' => 'Service not found'));
        }
    } catch (Exception $ex) {
        error_log(" Exception in checkPreviousServices: " . $ex->getMessage());
        echo json_encode(array('has_previous' => false, 'error' => $ex->getMessage()));
    }
} else if (isset($_GET["getBaseAllotment"]) && $_GET["getBaseAllotment"] == "true" && isset($_GET["customerId"]) && isset($_GET["serviceid"])) {
    // Get the base allotment number for RC service and calculate RC count
    try {
        // Get the original base allotment number (first non-RC service for this customer)
        $baseAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' ORDER BY id ASC LIMIT 1";
        $statement = $db->prepare($baseAllotmentSql);
        $statement->bindParam(':cid', $_GET["customerId"]);
        $statement->execute();
        $baseResult = $statement->fetch();

        if ($baseResult && !empty($baseResult['aggrement_no'])) {
            $baseAllotment = $baseResult['aggrement_no'];

            // ROBUST RC COUNT: Find the highest existing RC suffix in saved certification agreements
            $existingRcSql = "SELECT aggrement_no FROM certification_aggrement 
                             WHERE customer_id = :cid 
                             AND aggrement_no LIKE :pattern 
                             ORDER BY id DESC";
            $statement = $db->prepare($existingRcSql);
            $statement->bindParam(':cid', $_GET["customerId"]);
            $pattern = '% Rc-%';
            $statement->bindParam(':pattern', $pattern);
            $statement->execute();
            $existingRcAgreements = $statement->fetchAll();

            // Extract the highest RC number from existing agreements
            $maxRcNumber = 0;
            foreach ($existingRcAgreements as $agreement) {
                if (preg_match('/\sRc-(\d+)$/i', $agreement['aggrement_no'], $matches)) {
                    $rcNum = intval($matches[1]);
                    if ($rcNum > $maxRcNumber) {
                        $maxRcNumber = $rcNum;
                    }
                }
            }

            // Next RC will be highest existing + 1, or 1 if no RC agreements exist yet
            $rcCount = $maxRcNumber + 1;

            echo json_encode(array(
                'base_allotment' => $baseAllotment,
                'rc_count' => $rcCount
            ));
        } else {
            echo json_encode(array('error' => 'No base allotment found'));
        }
    } catch (Exception $ex) {
        echo json_encode(array('error' => $ex->getMessage()));
    }
} else if (isset($_GET["customerId"]) && !empty($_GET["customerId"])) {
    try {
        $client = "SELECT count(id) as id FROM allotment_count where client_id = :id";
        $statement = $db->prepare($client);
        $statement->bindParam(":id", $_GET["customerId"]);
        $statement->execute();
        $row = $statement->fetch();

        if ($row["id"]) {
            $sql = "SELECT count(id) as c_id FROM `certification_aggrement` WHERE `customer_id` = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_GET["customerId"]);
            $statement->execute();
            $agrementcount = $statement->fetch();

            $sql1 = "SELECT unique_id FROM `customer_info` where id = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $_GET["customerId"]);
            $statement->execute();
            $uniqueid = $statement->fetch();

            $clientDate = "SELECT certification_date FROM allotment_count WHERE client_id = :id ORDER BY client_count DESC LIMIT 1";
            $statement = $db->prepare($clientDate);
            $statement->bindParam(":id", $_GET["customerId"]);
            $statement->execute();
            $clientDate = $statement->fetch();

            echo json_encode(array($clientDate, $agrementcount, $uniqueid));
        } else {
            echo json_encode(array("no deta"));
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["certId"]) && !empty($_GET["certId"]) && isset($_GET["svceid"]) && !empty($_GET["svceid"])) {
    try {
        $sql = "SELECT * FROM `certification_aggrement` where id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["certId"]);
        $statement->execute();
        $certification = $statement->fetch();
        echo json_encode($certification);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["reviewDate"]) && !empty($_GET["reviewDate"]) && isset($_GET["customerid"]) && !empty($_GET["customerid"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["reviewDate"]));
        $clientDate = "SELECT certification_date FROM allotment_count WHERE client_id = :customer ORDER BY client_count DESC LIMIT 1";
        $statement = $db->prepare($clientDate);
        $statement->bindParam(":customer", $_GET["customerid"]);
        $statement->execute();
        $row = $statement->fetch();
        if ($row["certification_date"] >= $date) {
            echo "1";
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["assessmentDate"]) && !empty($_GET["assessmentDate"]) && isset($_GET["svcid"]) && !empty($_GET["svcid"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["assessmentDate"]));
        $cRevDate = "SELECT review_date FROM contract_id WHERE ca_service_id = :svcId";
        $statement = $db->prepare($cRevDate);
        $statement->bindParam(":svcId", $_GET["svcid"]);
        $statement->execute();
        $cDate = $statement->fetch();
        if ($cDate["review_date"] > $date) {
            echo "1";
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["intimationDate"]) && !empty($_GET["intimationDate"]) && isset($_GET["svc_id"]) && !empty($_GET["svc_id"])) {
    try {
        //		echo $_GET["svc_id"].$_GET["intimationDate"];
        $date = date("Y-m-d", strtotime($_GET["intimationDate"]));

        // Check service type
        $serviceTypeQuery = "SELECT service_type FROM service WHERE service_id = :svc";
        $stmt = $db->prepare($serviceTypeQuery);
        $stmt->bindParam(":svc", $_GET["svc_id"]);
        $stmt->execute();
        $serviceData = $stmt->fetch();

        // For SA-I (SA-01) and SA-II (SA-02), check against information updation review date
        if (isset($serviceData["service_type"]) && ($serviceData["service_type"] == 'SA-I' || $serviceData["service_type"] == 'SA-II')) {
            // For SA-I (SA-01) and SA-II (SA-02) types, check information updation review date
            $infoReviewQuery = "SELECT review_date FROM information_updation_review WHERE service_id = :svc ORDER BY review_date DESC LIMIT 1";
            $statement = $db->prepare($infoReviewQuery);
            $statement->bindParam(":svc", $_GET["svc_id"]);
            $statement->execute();
            $reviewDate = $statement->fetch();

            if ($reviewDate && $reviewDate["review_date"] > $date) {
                echo "1";
            }
        } else {
            // For CA, RC (Recertification), and other types, check assessment planning date
            $assessmentDate = "SELECT date FROM assessment_planning where service_id = :svc";
            $statement = $db->prepare($assessmentDate);
            $statement->bindParam(":svc", $_GET["svc_id"]);
            $statement->execute();
            $aDate = $statement->fetch();
            //		print_r($aDate);
            if ($aDate["date"] > $date) {
                echo "1";
            }
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["reviewDate"]) && !empty($_GET["reviewDate"]) && isset($_GET["svcId"]) && !empty($_GET["svcId"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["reviewDate"]));
        $auditDate = "SELECT schedule_audit_date FROM audit_schedule_id WHERE serviceca_id = :service";
        $statement = $db->prepare($auditDate);
        $statement->bindParam(":service", $_GET["svcId"]);
        $statement->execute();
        $auditDate = $statement->fetch();
        $aspectDate = explode(', ', $auditDate["schedule_audit_date"]);
        foreach ($aspectDate as $dates) {
            $dateval = date("Y-m-d", strtotime($dates));
        }
        //        echo $dateval;
        if ($dateval > $date) {
            echo "1";
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["intimationDateii"]) && !empty($_GET["intimationDateii"]) && isset($_GET["notisvcId"]) && !empty($_GET["notisvcId"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["intimationDateii"]));
        $revDate = "SELECT auditDate FROM stage_i_audit_id WHERE ca_service_id = :svc";
        $statement = $db->prepare($revDate);
        $statement->bindParam(":svc", $_GET["notisvcId"]);
        $statement->execute();
        $rowDate = $statement->fetch();
        $dates = explode(", ", $rowDate["auditDate"]);
        $lastDate = $dates[count($dates) - 1];
        $lastDate = DateTime::createFromFormat('d.m.Y', $lastDate);

        // Format the date as YYYY-MM-DD
        $lastDate = $lastDate->format('Y-m-d');
        if ($lastDate > $date) {
            echo "1";
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["reviewDateAnnex"]) && !empty($_GET["reviewDateAnnex"]) && isset($_GET["service_Id"]) && !empty($_GET["service_Id"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["reviewDateAnnex"]));
        $revDate = "SELECT audit_dates FROM stage_twoaudit_id WHERE service_id = :svc ORDER BY audit_stageii_id DESC LIMIT 1";
        $statement = $db->prepare($revDate);
        $statement->bindParam(":svc", $_GET["service_Id"]);
        $statement->execute();
        $rowDate = $statement->fetch();
        if ($rowDate && !empty($rowDate["audit_dates"])) {
            $aspectDate = array_values(array_filter(array_map('trim', explode(',', $rowDate["audit_dates"]))));
            $dateval = null;
            foreach ($aspectDate as $singleDate) {
                $normalizedDate = date("Y-m-d", strtotime($singleDate));
                if ($normalizedDate !== false && ($dateval === null || $normalizedDate < $dateval)) {
                    $dateval = $normalizedDate;
                }
            }
            if ($dateval !== null && $dateval > $date) {
                echo "1";
            }
        } else {
            $revDate = "SELECT audit_dates FROM stage_ii_audit_id where ca_service_id = :svc ORDER BY stage_ii_id DESC LIMIT 1";
            $statement = $db->prepare($revDate);
            $statement->bindParam(":svc", $_GET["service_Id"]);
            $statement->execute();
            $rowDate = $statement->fetch();

            $aspectDate = $rowDate && !empty($rowDate["audit_dates"]) ? array_values(array_filter(array_map('trim', explode(',', $rowDate["audit_dates"])))) : array();
            $dateval = null;
            foreach ($aspectDate as $singleDate) {
                $normalizedDate = date("Y-m-d", strtotime($singleDate));
                if ($normalizedDate !== false && ($dateval === null || $normalizedDate < $dateval)) {
                    $dateval = $normalizedDate;
                }
            }
            if ($dateval !== null && $dateval > $date) {
                echo "1";
            }
        }
    } catch (Exception $e) {

    }
} else if (isset($_GET["periodicalaudit"]) && !empty($_GET["periodicalaudit"]) && isset($_GET["clientId"]) && !empty($_GET["clientId"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["periodicalaudit"]));
        $anexReviewDate = "SELECT certification_stage_date FROM customer_info WHERE id = :user ";
        $statement = $db->prepare($anexReviewDate);
        $statement->bindParam(":user", $_GET["clientId"]);
        $statement->execute();
        $anexDate = $statement->fetch();

        $sql = "SELECT * FROM cdc_report_review WHERE ca_service_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['serviceID']);
        $statement->execute();
        $ca_cdcreport = $statement->fetch();

        if ($ca_cdcreport["audit_type"] == "CA") {
            $dt = new DateTime(date("Y-m-d", strtotime($anexDate["certification_stage_date"])));
            $dt->modify('+1 year');
            $dt->modify('-1 day');
            $renewalDate = $dt->format('Y-m-d');
            if ($renewalDate != $date) {
                echo "1";
            }
        } elseif ($ca_cdcreport["audit_type"] == "SA-I") {
            $dt = new DateTime(date("Y-m-d", strtotime($anexDate["certification_stage_date"])));
            $dt->modify('+2 year');
            $dt->modify('-1 day');
            $renewalDate = $dt->format('Y-m-d');
            if ($renewalDate != $date) {
                echo "2";
            }
        } elseif ($ca_cdcreport["audit_type"] == "SA-II") {
            $dt = new DateTime(date("Y-m-d", strtotime($anexDate["certification_stage_date"])));
            $dt->modify('+3 year');
            $dt->modify('-1 day');
            $renewalDate = $dt->format('Y-m-d');
            if ($renewalDate != $date) {
                echo "3";
            }
        } elseif ($ca_cdcreport["audit_type"] == "RC") {
            $dt = new DateTime(date("Y-m-d", strtotime($anexDate["certification_stage_date"])));
            $dt->modify('+4 year');
            $dt->modify('-1 day');
            $renewalDate = $dt->format('Y-m-d');
            if ($renewalDate != $date) {
                echo "4";
            }
        } else {
            echo "5";
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["revDatecdc"]) && !empty($_GET["revDatecdc"]) && isset($_GET["serviceID"]) && !empty($_GET["serviceID"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["revDatecdc"]));
        $anexReviewDate = "SELECT review_date FROM stage_ii_audit_id WHERE ca_service_id = :service ";
        $statement = $db->prepare($anexReviewDate);
        $statement->bindParam(":service", $_GET["serviceID"]);
        $statement->execute();
        $anexDate = $statement->fetch();
        if ($anexDate["review_date"] > $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["certifyRegistrationDate"]) && !empty($_GET["certifyRegistrationDate"]) && isset($_GET["certifyServiceId"]) && !empty($_GET["certifyServiceId"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["certifyRegistrationDate"]));
        $regDate = "SELECT Date FROM cdc_report_review WHERE ca_service_id = :svc ";
        $statement = $db->prepare($regDate);
        $statement->bindParam(":svc", $_GET["certifyServiceId"]);
        $statement->execute();
        $cdcRegDate = $statement->fetch();
        if (!$cdcRegDate) {
            echo "1";
            return;
        }
        $expectedDate = $cdcRegDate["Date"];
        $certificateDates = aitfBuildCertificatePrintDates($db, null, $_GET["certifyServiceId"], null, '', $cdcRegDate ? $cdcRegDate : array());
        if (!empty($certificateDates) && !empty($certificateDates['registration_date'])) {
            $expectedDate = $certificateDates['registration_date'];
        }
        if ($expectedDate != $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["certifyValidTill"]) && !empty($_GET["certifyValidTill"]) && isset($_GET["certifyService_Id"]) && !empty($_GET["certifyService_Id"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["certifyValidTill"]));
        $expDate = "SELECT * FROM cdc_report_review WHERE ca_service_id = :svc ";
        $statement = $db->prepare($expDate);
        $statement->bindParam(":svc", $_GET["certifyService_Id"]);
        $statement->execute();
        $expDateRoe = $statement->fetch();
        if (!$expDateRoe) {
            echo "1";
            return;
        }
        $expectedDate = $expDateRoe["expiredate"];
        $certificateDates = aitfBuildCertificatePrintDates($db, null, $_GET["certifyService_Id"], null, isset($expDateRoe['audit_type']) ? $expDateRoe['audit_type'] : '', $expDateRoe ? $expDateRoe : array());
        if (!empty($certificateDates) && !empty($certificateDates['valid_until'])) {
            $expectedDate = $certificateDates['valid_until'];
        }
        if ($expectedDate != $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["certifyBeforeDate"]) && !empty($_GET["certifyBeforeDate"]) && isset($_GET["certifyService_ID"]) && !empty($_GET["certifyService_ID"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["certifyBeforeDate"]));
        $expDate = "SELECT * FROM cdc_report_review WHERE ca_service_id = :svc ";
        $statement = $db->prepare($expDate);
        $statement->bindParam(":svc", $_GET["certifyService_ID"]);
        $statement->execute();
        $expDateRoe = $statement->fetch();
        if (!$expDateRoe) {
            echo "1";
            return;
        }
        $expectedDate = $expDateRoe["periodicalaudit"];
        $certificateDates = aitfBuildCertificatePrintDates($db, null, $_GET["certifyService_ID"], null, isset($expDateRoe['audit_type']) ? $expDateRoe['audit_type'] : '', $expDateRoe ? $expDateRoe : array());
        if (!empty($certificateDates) && !empty($certificateDates['before_date'])) {
            $expectedDate = $certificateDates['before_date'];
        }
        if ($expectedDate != $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["survayAuditDates"]) && !empty($_GET["survayAuditDates"]) && isset($_GET["survayService_ID"]) && !empty($_GET["survayService_ID"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["survayAuditDates"]));
        $certPrintDate = "SELECT registration_date FROM certificate_print WHERE service_id = :svc";
        $statement = $db->prepare($certPrintDate);
        $statement->bindParam(":svc", $_GET['survayService_ID']);
        $statement->execute();
        $certDate = $statement->fetch();
        if ($certDate["registration_date"] > $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["controlFormat1CertDate"]) && !empty($_GET["controlFormat1CertDate"]) && isset($_GET["cf1Service_ID"]) && !empty($_GET["cf1Service_ID"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["controlFormat1CertDate"]));
        $certPrintDate = "SELECT registration_date FROM certificate_print WHERE service_id = :svc";
        $statement = $db->prepare($certPrintDate);
        $statement->bindParam(":svc", $_GET['cf1Service_ID']);
        $statement->execute();
        $certDate = $statement->fetch();
        if ($certDate["registration_date"] > $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["controlFormat2CertDate"]) && !empty($_GET["controlFormat2CertDate"]) && isset($_GET["cf2Service_ID"]) && !empty($_GET["cf2Service_ID"])) {
    try {
        $date = date("Y-m-d", strtotime($_GET["controlFormat2CertDate"]));
        $certPrintDate = "SELECT registration_date FROM certificate_print WHERE service_id = :svc";
        $statement = $db->prepare($certPrintDate);
        $statement->bindParam(":svc", $_GET['cf2Service_ID']);
        $statement->execute();
        $certDate = $statement->fetch();
        if ($certDate["registration_date"] > $date) {
            echo "1";
        }
    } catch (Exception $ex) {

    }
} else if (isset($_GET["rev_Datecdc"]) && !empty($_GET["rev_Datecdc"]) && isset($_GET["servic_ID"]) && !empty($_GET["servic_ID"]) && isset($_GET["uni_svc"]) && !empty($_GET["uni_svc"]) && isset($_GET["customer"]) && !empty($_GET["customer"])) {
    $secondMaxSvc = "SELECT MAX(service_id) as id FROM `service` WHERE service_type = 'SA-II' AND customer_id = :cust";
    $statement = $db->prepare($secondMaxSvc);
    $statement->bindParam(":cust", $_GET['customer']);
    $statement->execute();
    $maxid = $statement->fetch();

    $expdate = "SELECT due_date FROM `ca_service_dates` WHERE `service_id` = :id AND action = 'Expire Letter'";
    $statement = $db->prepare($expdate);
    $statement->bindParam(":id", $maxid['id']);
    $statement->execute();
    $expdate = $statement->fetch();

    date("Y-m-d", strtotime($_GET["rev_Datecdc"]));
    if (date("Y-m-d", strtotime($_GET["rev_Datecdc"])) > $expdate["due_date"]) {
        echo "1";
    } else {
        echo "0";
    }
} else if (isset($_GET["generateAllotment"]) && $_GET["generateAllotment"] == "true") {
    // Generate allotment number automatically for audit schedule
    try {
        $currentDate = date('Y-m-d');
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get the highest counter for current month/year using helper
        $newCounter = getNextAllotmentSequence($db, null, $currentYear, $currentMonth);

        // Format the allotment number: AITF/Mon-YY/Counter
        $monthName = date('M', mktime(0, 0, 0, intval($currentMonth), 1));
        $year = date('y'); // 2-digit year

        $allotmentNumber = 'AITF/' . $monthName . '-' . $year . '/' . $newCounter;

        echo json_encode(array(
            'success' => true,
            'allotmentNumber' => $allotmentNumber,
            'counter' => $newCounter
        ));
    } catch (Exception $ex) {
        echo json_encode(array(
            'success' => false,
            'error' => $ex->getMessage()
        ));
    }
}
