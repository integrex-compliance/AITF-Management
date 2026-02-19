<?php

/**
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
// Buffer any stray output to avoid breaking TCPDF output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

$__invoiceServicesQtyRateReady = invoiceServicesHasQtyRateColumns($db);

$invoiceid = "";
$total_amt = "";
$innvoice = "";
$invoiceSvc = "";
$invDate = "";
$netPrice = "";
$svcTax = "";
$swachBharat = "";
$krushiKalyan = "";
$state = "";
$stateCode = "";
$sgst = "";
$igst = "";
$gsts = "";
$firmSign = "";
$signaturePath = ''; 

$customerId = '';
if (!empty($_POST['invoiceconsultingFirmName'])) {
    $customerId = $_POST['invoiceconsultingFirmName'];
} elseif (!empty($_POST['invoiceCustomerId'])) {
    $customerId = $_POST['invoiceCustomerId'];
}



$customer = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customer);
$statement->bindParam(':id', $customerId);
$statement->execute();
$customerInfo = $statement->fetch();

$consultingFirm = "SELECT * FROM consulting_firm ";
$statement = $db->prepare($consultingFirm);
$statement->execute();
$firmInfo = $statement->fetch();
$firmSign = $firmInfo["signature"];

// Update to use PNG signature if JPG doesn't exist
if (!empty($firmSign) && !file_exists(__DIR__ . '/' . $firmSign)) {
    $pngVersion = str_replace('.jpg', '.png', $firmSign);
    if (file_exists(__DIR__ . '/' . $pngVersion)) {
        $firmSign = $pngVersion;
        // Update database to use PNG version
        $updateSign = $db->prepare("UPDATE consulting_firm SET signature = ? WHERE firm_id = '1'");
        $updateSign->execute([$firmSign]);
    }
}
if (isset($_POST['invoiceId']) && !empty($_POST['invoiceId'])) {
    $invoiceid = $_POST['invoiceId'];
    $netPriceVal = moneyRound($_POST['netPrice']);
    if (isset($_POST['invoiceService'], $_POST['invoiceServiceAmt']) && is_array($_POST['invoiceService']) && is_array($_POST['invoiceServiceAmt'])) {
        $serviceSumVal = 0.0;
        $c = min(count($_POST['invoiceService']), count($_POST['invoiceServiceAmt']));
        $qtyArr = isset($_POST['invoiceServiceQty']) && is_array($_POST['invoiceServiceQty']) ? $_POST['invoiceServiceQty'] : [];
        $rateArr = isset($_POST['invoiceServiceRate']) && is_array($_POST['invoiceServiceRate']) ? $_POST['invoiceServiceRate'] : [];
        $useQtyRate = !empty($_POST['includeQtyRate']) && count($qtyArr) > 0 && count($rateArr) > 0;
        for ($i = 0; $i < $c; $i++) {
            if ((string) $_POST['invoiceService'][$i] !== '') {
                if ($useQtyRate) {
                    $serviceSumVal += round(moneyRound($qtyArr[$i] ?? 0) * moneyRound($rateArr[$i] ?? 0), 2);
                } else {
                    $serviceSumVal += moneyRound($_POST['invoiceServiceAmt'][$i]);
                }
            } 
        } 


        
        if ($serviceSumVal > 0 && abs($netPriceVal - $serviceSumVal) > 0.009) {
            $netPriceVal = round($serviceSumVal, 2);
        }
    }
    if ($_POST["taxtype"] == "IGST") {
        $sgst = "NA";
        $cgst = "NA";
        $igstVal = round($netPriceVal * 0.18, 2);
        $igst = moneyDb($igstVal);
        $total_amt = round($netPriceVal + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    } else if ($_POST["taxtype"] == "CGST & SGST") {
        $igst = 'NA';  
        $cgstVal = round($netPriceVal * 0.09, 2);
        $sgstVal = round($netPriceVal * 0.09, 2);
        $cgst = moneyDb($cgstVal);
        $sgst = moneyDb($sgstVal);
        $total_amt = round($netPriceVal + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/-</td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/-</td></tr>';
    }

    $netPriceDb = moneyDb($netPriceVal);
    $totalAmtDb = moneyDb($total_amt);
    $invoiceUpdate = "UPDATE `invoice_details` SET `customer_id`= :customer,`invoice_date`= :date,`state`= :state,`state_code`= :statecode,`net_price`= :netprice,"
            . "`igst`= :igst,`cgst`= :cgst,`sgst`= :sgst,`tax_type`= :taxtype,`final_amount`= :totalAmt WHERE `invoice_id` = :id";
    $statement = $db->prepare($invoiceUpdate);
    $statement->bindParam(':id', $invoiceid);
    $statement->bindParam(':customer', $customerId);
    $statement->bindParam(':date', date("Y-m-d", strtotime($_POST['invoiceDt'])));
    $statement->bindParam(':state', $_POST['invoiceState']);
    $statement->bindParam(':statecode', $_POST['invoiceStateCode']);
    $statement->bindParam(':netprice', $netPriceDb);
    $statement->bindParam(':igst', $igst);
    $statement->bindParam(':cgst', $cgst);
    $statement->bindParam(':sgst', $sgst);
    $statement->bindParam(':taxtype', $_POST["taxtype"]);
    $statement->bindParam(':totalAmt', $totalAmtDb);
    $statement->execute();

    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $invoiceid);
    $statement->execute();
    $innvoice = $statement->fetch();
    $invno = $innvoice['invoice_no'];
    $proformaprefix = $innvoice['profarma_prefix'];

    $dltService = "DELETE FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($dltService);
    $statement->bindParam(':id', $invoiceid);
    $statement->execute();

    if (isset($_POST['invoiceService']) && isset($_POST['invoiceServiceAmt'])) {
        $serviceArray = $_POST['invoiceService'];
        $serviceAmtArray = $_POST['invoiceServiceAmt'];
        $serviceQtyArray = isset($_POST['invoiceServiceQty']) && is_array($_POST['invoiceServiceQty']) ? $_POST['invoiceServiceQty'] : [];
        $serviceRateArray = isset($_POST['invoiceServiceRate']) && is_array($_POST['invoiceServiceRate']) ? $_POST['invoiceServiceRate'] : [];
        $hasQtyRateCols = invoiceServicesHasQtyRateColumns($db);
        foreach ($serviceArray as $idx => $serviceDesc) {
            if ((string) $serviceDesc !== "") {
                if ($hasQtyRateCols) {
                    $invoiceServices = "INSERT INTO invoice_services (invoice_ids, service_desc, service_amount, quantity, rate) VALUES (:id,:service,:amount,:qty,:rate)";
                } else {
                    $invoiceServices = "INSERT INTO invoice_services (invoice_ids, service_desc, service_amount) VALUES (:id,:service,:amount)";
                }
                $statement = $db->prepare($invoiceServices);
                $statement->bindParam(':id', $invoiceid);
                $statement->bindParam(':service', $serviceDesc);
                $serviceAmtVal = $serviceAmtArray[$idx] ?? 0;
                if (!empty($_POST['includeQtyRate']) && isset($serviceQtyArray[$idx], $serviceRateArray[$idx])) {
                    $serviceAmtVal = round(moneyRound($serviceQtyArray[$idx]) * moneyRound($serviceRateArray[$idx]), 2);
                }
                $serviceAmtDb = moneyDb($serviceAmtVal);
                $statement->bindParam(':amount', $serviceAmtDb);
                if ($hasQtyRateCols) {
                    $qtyDb = isset($serviceQtyArray[$idx]) ? moneyDb($serviceQtyArray[$idx]) : null;
                    $rateDb = isset($serviceRateArray[$idx]) ? moneyDb($serviceRateArray[$idx]) : null;
                    $statement->bindParam(':qty', $qtyDb);
                    $statement->bindParam(':rate', $rateDb);
                }
                $statement->execute();
            }
        }
    }

    $invDate = $_POST['invoiceDt'];
    $netPrice = $netPriceVal;
    $state = $_POST["invoiceState"];
    $stateCode = $_POST["invoiceStateCode"];
} else if (isset($_POST['invoiceid']) && !empty($_POST['invoiceid'])) {
    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $_POST['invoiceid']);
    $statement->execute();
    $innvoice = $statement->fetch();

    $invoiceDetail = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($invoiceDetail);
    $statement->bindParam(':id', $_POST['invoiceid']);
    $statement->execute();
    $invoiceSvc = $statement->fetchAll();

    $proformaprefix = $innvoice['profarma_prefix'];
    $invno = $innvoice['invoice_no'];
    $invDate = $innvoice['invoice_date'];
    $netPrice = moneyRound($innvoice['net_price']);
    $sgst = $innvoice['sgst'] === 'NA' ? 'NA' : moneyRound($innvoice['sgst']);
    $cgst = $innvoice["cgst"] === 'NA' ? 'NA' : moneyRound($innvoice["cgst"]);
    $igst = $innvoice['igst'] === 'NA' ? 'NA' : moneyRound($innvoice['igst']);
    $total_amt = moneyRound($innvoice['final_amount']);
    $state = $innvoice['state'];
    $stateCode = $innvoice['state_code'];
    if (is_array($invoiceSvc)) {
        $serviceSumVal = 0.0;
        foreach ($invoiceSvc as $svc) {
            if (isset($svc['service_amount'])) {
                $serviceSumVal += moneyRound($svc['service_amount']);
            }
        }
        if ($serviceSumVal > 0 && abs($netPrice - $serviceSumVal) > 0.009) {
            $netPrice = round($serviceSumVal, 2);
        }
    }
    if ($innvoice["tax_type"] == "CGST & SGST") {
        $cgstVal = round($netPrice * 0.09, 2);
        $sgstVal = round($netPrice * 0.09, 2);
        $total_amt = round($netPrice + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/- </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/- </td></tr>';
    } else if ($innvoice["tax_type"] == "IGST") {
        $igstVal = round($netPrice * 0.18, 2);
        $total_amt = round($netPrice + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    }
} else if (isset($_POST['netPrice']) && !empty($_POST['netPrice'])) {
    $netPriceVal = moneyRound($_POST['netPrice']);
    if (isset($_POST['invoiceService'], $_POST['invoiceServiceAmt']) && is_array($_POST['invoiceService']) && is_array($_POST['invoiceServiceAmt'])) {
        $serviceSumVal = 0.0;
        $c = min(count($_POST['invoiceService']), count($_POST['invoiceServiceAmt']));
        $qtyArr = isset($_POST['invoiceServiceQty']) && is_array($_POST['invoiceServiceQty']) ? $_POST['invoiceServiceQty'] : [];
        $rateArr = isset($_POST['invoiceServiceRate']) && is_array($_POST['invoiceServiceRate']) ? $_POST['invoiceServiceRate'] : [];
        $useQtyRate = !empty($_POST['includeQtyRate']) && count($qtyArr) > 0 && count($rateArr) > 0;
        for ($i = 0; $i < $c; $i++) {
            if ((string) $_POST['invoiceService'][$i] !== '') {
                if ($useQtyRate) {
                    $serviceSumVal += round(moneyRound($qtyArr[$i] ?? 0) * moneyRound($rateArr[$i] ?? 0), 2);
                } else {
                    $serviceSumVal += moneyRound($_POST['invoiceServiceAmt'][$i]);
                }
            }
        }
        if ($serviceSumVal > 0 && abs($netPriceVal - $serviceSumVal) > 0.009) {
            $netPriceVal = round($serviceSumVal, 2);
        }
    }
    if ($_POST["taxtype"] == "IGST") {
        $sgst = "NA";
        $cgst = "NA";
        $igstVal = round($netPriceVal * 0.18, 2);
        $igst = moneyDb($igstVal);
        $total_amt = round($netPriceVal + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    } else if ($_POST["taxtype"] == "CGST & SGST") {
        $igst = 'NA';
        $cgstVal = round($netPriceVal * 0.09, 2);
        $sgstVal = round($netPriceVal * 0.09, 2);
        $cgst = moneyDb($cgstVal);
        $sgst = moneyDb($sgstVal);
        $total_amt = round($netPriceVal + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/- </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/- </td></tr>';
    }

    $cmonth_invoice = "SELECT count(invoice_id) as id FROM `invoice_details` WHERE (invoice_date BETWEEN '" . date('Y-m-01') . "' AND '" . date('Y-m-t') . "') AND invoice_type = 'proforma'";
    $statement = $db->prepare($cmonth_invoice);
    $statement->execute();
    $monthinvc = $statement->fetch();
//echo $cmonth_invoice.' -> '.date('Y-m-01').' -> '.' -> '.date('Y-m-t').' -> '.$monthinvc["id"]; die;
    if ($monthinvc["id"] == '0') {
        $proformaPrefix = strtoupper(date("Y/M"));
        $proformaPrefix = 'CD/' . $proformaPrefix;
        $proformainv = '1';
        $updateInvoice = "UPDATE consulting_firm SET profarma_prefix = :prefix,firm_invoice_no = :no WHERE firm_id = '1' ";
        $statement = $db->prepare($updateInvoice);
        $statement->bindParam(':prefix', $proformaPrefix);
        $statement->bindParam(':no', $proformainv);
        $statement->execute();
    }

    $consultingFirm = "SELECT * FROM consulting_firm ";
    $statement = $db->prepare($consultingFirm);
    $statement->execute();
    $firmInfo = $statement->fetch();
    $invoiceDate = date("Y-m-d", strtotime($_POST['invoiceDt']));
    //$total_amt = $_POST['netPrice'] + $_POST['serviceTax'] + $_POST['swachBharat'];
    $invoice = "INSERT INTO invoice_details (customer_id, profarma_prefix, invoice_no, invoice_date, state, state_code, net_price,igst,"
            . " cgst,  sgst, tax_type, final_amount) VALUES (:customer, :profarma_prefix, :invoiceNo, :date, :state, :statecode,"
            . ":netprice, :igst, :cgst, :sgst, :taxtype, :totalAmt);";
    $statement = $db->prepare($invoice);
    $statement->bindParam(':customer', $customerId);
    $statement->bindParam(':profarma_prefix', $firmInfo['profarma_prefix']);
    $statement->bindParam(':invoiceNo', $firmInfo['firm_invoice_no']);
    $statement->bindParam(':date', $invoiceDate);
    $statement->bindParam(':state', $_POST['invoiceState']);
    $statement->bindParam(':statecode', $_POST['invoiceStateCode']);
    $netPriceDb = moneyDb($netPriceVal);
    $totalAmtDb = moneyDb($total_amt);
    $statement->bindParam(':netprice', $netPriceDb);
    $statement->bindParam(':igst', $igst);
    $statement->bindParam(':cgst', $cgst); 
    $statement->bindParam(':sgst', $sgst);
    $statement->bindParam(':taxtype', $_POST["taxtype"]);
    $statement->bindParam(':totalAmt', $totalAmtDb);
    $statement->execute();

    $invno = $firmInfo['firm_invoice_no'];
    $proformaprefix = $firmInfo['profarma_prefix'];

    $sql = "SELECT max(LAST_INSERT_ID(invoice_id)) as id From invoice_details";
    $statement = $db->prepare($sql);
    $statement->execute();
    $row = $statement->fetch();
    $invoiceid = $row['id'];

    $serviceArray = $_POST['invoiceService'];
    $serviceAmtArray = $_POST['invoiceServiceAmt'];
    $serviceQtyArray = isset($_POST['invoiceServiceQty']) && is_array($_POST['invoiceServiceQty']) ? $_POST['invoiceServiceQty'] : [];
    $serviceRateArray = isset($_POST['invoiceServiceRate']) && is_array($_POST['invoiceServiceRate']) ? $_POST['invoiceServiceRate'] : [];
    $hasQtyRateCols = invoiceServicesHasQtyRateColumns($db);
    $count = 0;
    foreach ($serviceArray as $service) {
        if ($serviceArray[$count] != "") {
            if ($hasQtyRateCols) {
                $invoiceServices = "INSERT INTO invoice_services (invoice_ids, service_desc, service_amount, quantity, rate) VALUES (:id,:service,:amount,:qty,:rate)";
            } else {
                $invoiceServices = "INSERT INTO invoice_services (invoice_ids, service_desc, service_amount) VALUES (:id,:service,:amount)";
            }
            $statement = $db->prepare($invoiceServices);
            $statement->bindParam(':id', $invoiceid);
            $statement->bindParam(':service', $serviceArray[$count]);
            $serviceAmtVal = $serviceAmtArray[$count];
            if (!empty($_POST['includeQtyRate']) && isset($serviceQtyArray[$count], $serviceRateArray[$count])) {
                $serviceAmtVal = round(moneyRound($serviceQtyArray[$count]) * moneyRound($serviceRateArray[$count]), 2);
            }
            $serviceAmtDb = moneyDb($serviceAmtVal);
            $statement->bindParam(':amount', $serviceAmtDb);
            if ($hasQtyRateCols) {
                $qtyDb = isset($serviceQtyArray[$count]) ? moneyDb($serviceQtyArray[$count]) : null;
                $rateDb = isset($serviceRateArray[$count]) ? moneyDb($serviceRateArray[$count]) : null;
                $statement->bindParam(':qty', $qtyDb);
                $statement->bindParam(':rate', $rateDb);
            }
            $statement->execute();
            $count++;
        }
    }

    $invoice = $firmInfo['firm_invoice_no'];
    $newInvoice = $invoice + 1;
    $updateInvoice = "UPDATE consulting_firm SET firm_invoice_no = :no WHERE firm_id = '1' ";
    $statement = $db->prepare($updateInvoice);
    $statement->bindParam(':no', $newInvoice);
    $statement->execute();
    $invDate = $_POST['invoiceDt'];
    $netPrice = $netPriceVal;
    $state = $_POST["invoiceState"];
    $stateCode = $_POST["invoiceStateCode"];
}

// Resolve signature image path if the file exists
if (!empty($firmSign)) {
    $candidate = $firmSign;
    if (!file_exists($candidate)) {
        // Try relative path from document root
        $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        $candidate = $documentRoot . '/' . ltrim($firmSign, '/\\');
    }
    if (!file_exists($candidate)) {
        // Try relative path from current directory
        $candidate = __DIR__ . '/' . $firmSign;
    }
    if (!file_exists($candidate)) {
        // Try without leading slash from current directory
        $candidate = __DIR__ . '/' . ltrim($firmSign, '/\\');
    }
    if (!file_exists($candidate)) {
        // Try to extract filename and look in signatures directory
        $filename = basename($firmSign);
        $candidate = __DIR__ . '/uploads/signatures/' . $filename;
    }
    if (!file_exists($candidate)) {
        // Try PNG version if original was JPG
        if (pathinfo($firmSign, PATHINFO_EXTENSION) == 'jpg') {
            $pngFile = pathinfo($firmSign, PATHINFO_FILENAME) . '.png';
            $candidate = __DIR__ . '/uploads/invoiceSign/' . $pngFile;
        }
    }
    if (!file_exists($candidate)) {
        // Try any available signature file from signatures directory as fallback
        $signaturesDir = __DIR__ . '/uploads/signatures/';
        if (is_dir($signaturesDir)) {
            $files = glob($signaturesDir . '*.{jpg,png}', GLOB_BRACE);
            if (!empty($files)) {
                $candidate = $files[0]; // Use first available signature
            }
        }
    }
    if (file_exists($candidate)) {
        // Convert to proper URL format for TCPDF
        $signaturePath = str_replace('\\', '/', $candidate);
    }
    // Debug: Log signature path resolution (remove in production)
    error_log("Signature Debug - Original: $firmSign, Final Path: " . ($signaturePath ?? 'NOT FOUND'));
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        // Logo
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 12);
        $this->Ln(7);
        $this->SetXY(60, 15);
        $this->SetTextColor(128, 184, 224);
        $this->Cell(0, 30, '(NABCB ACCREDITED CERTIFICATION REGISTER FOR QMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 14);
        $this->Ln(7);
        $this->SetXY(60, 20);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 30, '(PROFORMA)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        // Set font
        $this->SetFont('times', 'I', 12);
        $this->SetXY(0, -8);
        // Page number
        // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', ' F 6.01', 0, 0, false, "L", true);
//        $this->SetFont('helvetica', 'B', 12);
//        $this->Ln(7);
//        $this->Cell(0, 15, 'CERTIFICATION SERVICE AGREEMENT (FEES STRUCTURE)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Proforma Invoice');
$pdf->SetSubject('AITF');
$pdf->SetKeywords('AITF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 25, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 9);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
// ---------------------------------------------------------
// set font
$pdf->SetFont('times', '', 12);

/// add a page
$pdf->AddPage();

$subtable = '<table border="2" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$includeQtyRate = !empty($_POST['includeQtyRate']);
if (!$includeQtyRate && is_array($invoiceSvc)) {
    foreach ($invoiceSvc as $svc) {
        if ((isset($svc['quantity']) && $svc['quantity'] !== null && $svc['quantity'] !== '') ||
                (isset($svc['rate']) && $svc['rate'] !== null && $svc['rate'] !== '')) {
            $includeQtyRate = true;
            break;
        }
    }
}

$serviceRow = '';
if (isset($_POST['invoiceService']) && isset($_POST['invoiceServiceAmt'])) {
    $serviceArray = $_POST['invoiceService'];
    $serviceAmtArray = $_POST['invoiceServiceAmt'];
    $serviceQtyArray = isset($_POST['invoiceServiceQty']) && is_array($_POST['invoiceServiceQty']) ? $_POST['invoiceServiceQty'] : [];
    $serviceRateArray = isset($_POST['invoiceServiceRate']) && is_array($_POST['invoiceServiceRate']) ? $_POST['invoiceServiceRate'] : [];
    $count = 0;
    foreach ($serviceArray as $service) {
        if ($includeQtyRate) {
            $qtyVal = isset($serviceQtyArray[$count]) ? $serviceQtyArray[$count] : '';
            $rateVal = isset($serviceRateArray[$count]) ? $serviceRateArray[$count] : '';
            $amtVal = $serviceAmtArray[$count];
            if (!empty($_POST['includeQtyRate']) && $qtyVal !== '' && $rateVal !== '') {
                $amtVal = round(moneyRound($qtyVal) * moneyRound($rateVal), 2);
            }
            $serviceRow = $serviceRow . '<tr><td>' . ($count + 1) . '</td><td>' . $serviceArray[$count] . '</td><td>' . moneyFormat($qtyVal) . '</td><td>' . moneyFormat($rateVal) . '</td><td>' . moneyFormat2($amtVal) . '/-</td></tr>';
        } else {
            $serviceRow = $serviceRow . '<tr><td>' . ($count + 1) . '</td><td>' . $serviceArray[$count] . '</td><td>' . moneyFormat2($serviceAmtArray[$count]) . '/-</td></tr>';
        }
        $count = $count + 1;
    }
} else {
    $count = 0;
//    print_r($invoiceSvc);
    foreach ($invoiceSvc as $svc) {
        if ($includeQtyRate) {
            $qtyVal = isset($svc['quantity']) ? $svc['quantity'] : '';
            $rateVal = isset($svc['rate']) ? $svc['rate'] : '';
            $serviceRow = $serviceRow . '<tr><td>' . ($count + 1) . '</td><td>' . $svc['service_desc'] . '</td><td>' . moneyFormat($qtyVal) . '</td><td>' . moneyFormat($rateVal) . '</td><td>' . moneyFormat2($svc['service_amount']) . '/-</td></tr>';
        } else {
            $serviceRow = $serviceRow . '<tr><td>' . ($count + 1) . '</td><td>' . $svc['service_desc'] . '</td><td>' . moneyFormat2($svc['service_amount']) . '/-</td></tr>';
        }
        $count = $count + 1;
    }
}

//echo $serviceRow;

$gstsForTable = $includeQtyRate ? str_replace('colspan="2"', 'colspan="4"', $gsts) : $gsts;
$netColspan = $includeQtyRate ? 4 : 2;
$finalColspan = $includeQtyRate ? 4 : 2;
$wordsColspan = $includeQtyRate ? 5 : 3;
$serviceHeader = $includeQtyRate ?
        '<tr><th width="5%">Sr.</th><th width="45%">Description</th><th width="10%">Qty</th><th width="15%">Rate</th><th width="25%">Amount in Rs. </th></tr>' :
        '<tr><th width="5%">Sr.</th><th width="60%">Description</th><th width="35%">Amount in Rs. </th></tr>';

$html = '
<table border="1" cellspacing="1" cellpadding="4">	
    <tr >
    <td height="100"><b>TO:</b><br><b>NAME - ' . $customerInfo['customer_name'] . '</b><br>ADDRESS - ' . $customerInfo['address'] . '<br>
        
        <br><b>GSTIN No.: </b>' . $customerInfo['gstin_no'] . '
        
        <br><b>STATE: </b>' . $state . '&nbsp; &nbsp; &nbsp; <b>STATE CODE: </b>' . $stateCode . '
         </td>		
    <td > <br/><b>DATE:</b> ' . date("d.m.Y", strtotime($invDate)) . ' <br/><br/><b>Note:</b>This is Proforma, don’t take any tax credit on basis of this, we send final invoice only after payment realization.</td>
    </tr>
    <tr><td colspan="3"><b>SAC code : </b> 998311</td></tr>
    </table>
    <table border="1" cellspacing="1" cellpadding="4">
    ' . $serviceHeader . ' 
    ' . $serviceRow . '
    <tr><td colspan="' . $netColspan . '" style="text-align:right">Net Price</td><td> ' . moneyFormat($netPrice) . '/-</td></tr>
    ' . $gstsForTable . '
    <tr><td colspan="' . $finalColspan . '" style="text-align:right"><b>Final Amount</b></td><td> ' . moneyFormat($total_amt) . '/- </td></tr>
    <tr><td colspan="' . $wordsColspan . '">Amount In Word : ' . convertToWord($total_amt) . '</td></tr>
    <tr><td colspan="' . $wordsColspan . '"><b>
    NOTE-</b>
    <ul style= "list-style-type:none; ">
    <li>* Please issue cheque in favor of AITF Management Certification Limited. Cash Payment is not acceptable.</li>
    <li>* <b>GSTIN No.: 27AAJCA6651K1ZB,</b></li>
    <li>* PAN Number :AAJCA6651K</li>
    <li>* Bank details: ' . $firmInfo['firm_bank'] . ' Address - ' . $firmInfo['bank_address'] . '</li>
    <li>* Current Account No.- ' . $firmInfo['bank_ac_no'] . ',IFSC Code - ' . $firmInfo['bank_ifsc_code'] . ', MICR No.- ' . $firmInfo['bank_micr_no'] . '</li>
    <li>* For late payment interest will be charged at a rate of 10 % per month.</li>
    <li>* Signature not required if is computer generated and send by AITF registered A/c mail ID i.e. aitfacct@gmail.com .</li>
    </ul>
    </td></tr>
    </table>
    <table border="1" cellspacing="1" cellpadding="4"> 
    <tr><td style="text-align:left;border-top:0.1px solid white"><b>Registered office Address -</b><br/>
    ' . $firmInfo['firm_address'] . '<br/>
    (PH) ' . $firmInfo['firm_contact'] . '<br/>
    E mail : ' . $firmInfo['firm_email'] . ' <br/> 
    Web – www.aitf.co.in <br/> 
    </td>
    <td style="text-align:right;border-bottom:0.1px solid white"><b>AITF MANAGEMENT CERTIFICATION LIMITED.</b><br/>
    ' . (!empty($signaturePath) && file_exists($signaturePath) ? '<img src="' . $signaturePath . '" alt="signature" height="50" width="90"><br/>' : '') . '
    Administrator
    </td>
    </tr>
    </table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('Proforma Invoice.pdf', 'I');



// here new code added by Gaurav Oli

function moneyRound($value) {
    $v = is_string($value) ? str_replace(',', '', $value) : $value;
    return round((float) $v, 2);
}

function moneyDb($value) {
    if ($value === 'NA' || $value === '' || $value === null) {
        return $value;
    }
    return number_format(moneyRound($value), 2, '.', '');
}

function moneyFormat($value) {
    if ($value === 'NA' || $value === '' || $value === null) {
        return (string) $value;
    }
    $v = moneyRound($value);
    if (abs($v - round($v)) < 0.00001) {
        return number_format($v, 0, '.', '');
    }
    return number_format($v, 2, '.', '');
}

function moneyFormat2($value) {
    if ($value === 'NA' || $value === '' || $value === null) {
        return (string) $value;
    }
    return number_format(moneyRound($value), 2, '.', '');
}

function invoiceServicesHasQtyRateColumns($db) {
    static $has = null;
    if ($has !== null) {
        return $has;
    }
    try {
        $stmtQty = $db->query("SHOW COLUMNS FROM invoice_services LIKE 'quantity'");
        $hasQty = $stmtQty && $stmtQty->fetch();
        $stmtRate = $db->query("SHOW COLUMNS FROM invoice_services LIKE 'rate'");
        $hasRate = $stmtRate && $stmtRate->fetch();
        if (!$hasQty) {
            try {
                $db->exec("ALTER TABLE invoice_services ADD COLUMN quantity DECIMAL(10,2) NULL");
            } catch (Exception $e) {
            }
        }
        if (!$hasRate) {
            try {
                $db->exec("ALTER TABLE invoice_services ADD COLUMN rate DECIMAL(10,2) NULL");
            } catch (Exception $e) {
            }
        }
        $stmtQty2 = $db->query("SHOW COLUMNS FROM invoice_services LIKE 'quantity'");
        $hasQty2 = $stmtQty2 && $stmtQty2->fetch();
        $stmtRate2 = $db->query("SHOW COLUMNS FROM invoice_services LIKE 'rate'");
        $hasRate2 = $stmtRate2 && $stmtRate2->fetch();
        $has = ($hasQty2 && $hasRate2) ? true : false;
    } catch (Exception $e) {
        $has = false;
    }
    return $has;
}

function convertcash($num, $currency) {
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits 
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping. 
        $explrestunits = "";
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            $explrestunits .= (int) $expunit[$i] . ","; // creates each of the 2's group and adds a comma to the end 
        }

        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return $currency . $thecash . ".00"; // writes the final format where $currency is the currency symbol. 
}

function convertToWord($number) {
    $number = moneyRound($number);
    $no = (int) floor($number);
    $point = (int) round(($number - $no) * 100);
    if ($point === 100) {
        $no = $no + 1;
        $point = 0;
    }
    $hundred = null;
    $digits_1 = strlen((string) $no);
    $i = 0;
    $str = array();
    $words = array('0' => '', '1' => 'One', '2' => 'Two',
        '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
        '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
        '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
        '13' => 'Thirteen', '14' => 'Fourteen',
        '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
        '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
        '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
        '60' => 'Sixty', '70' => 'Seventy',
        '80' => 'Eighty', '90' => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    $n = $no;
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($n % $divider);
        $n = floor($n / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
        } else
            $str[] = null;
    }
    $str = array_reverse($str);
    $result = trim(implode('', $str));
    if ($result === '') {
        $result = 'Zero';
    }
    $pointsWords = '';
    if ($point) {
        $pointsWords = ($point < 21) ? $words[$point] : ($words[floor($point / 10) * 10] . " " . $words[$point % 10]);
        $pointsWords = trim($pointsWords);
    }
    return $result . " Rupees" . ($pointsWords !== '' ? (" and " . $pointsWords . " Paise") : "") . " Only";
}
