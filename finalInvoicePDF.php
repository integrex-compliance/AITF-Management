<?php

//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

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
// Ensure no stray output breaks PDF headers
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
$gsts = "";
$firmSign = "";
$signaturePath = '';

$customerId = '';
if (!empty($_POST['invoiceconsultingFirmName'])) {
    $customerId = $_POST['invoiceconsultingFirmName'];
} elseif (!empty($_POST['invoiceCustomerId'])) {
    $customerId = $_POST['invoiceCustomerId'];
} elseif (!empty($_GET['invoiceCustomerId'])) {
    $customerId = $_GET['invoiceCustomerId'];
} elseif (!empty($_REQUEST['customer_id'])) {
    $customerId = $_REQUEST['customer_id'];
}

$customerInfo = array();
if (!empty($customerId)) {
    $customer = "SELECT * FROM customer_info WHERE id = :id";
    $statement = $db->prepare($customer);
    $statement->bindParam(':id', $customerId);
    $statement->execute();
    $customerInfo = $statement->fetch();
}

$consultingFirm = "SELECT * FROM consulting_firm ";
$statement = $db->prepare($consultingFirm);
$statement->execute();
$firmInfo = $statement->fetch();
$firmSign = $firmInfo["signature"];

// Resolve signature image path if the file exists
if (!empty($firmSign)) {
    $candidate = $firmSign;
    if (!file_exists($candidate)) {
        $candidate = __DIR__ . '/' . ltrim($firmSign, '/\\');
    }
    if (file_exists($candidate)) {
        $signaturePath = str_replace('\\', '/', $candidate);
    }
}



if (isset($_POST['invoiceId']) && !empty($_POST['invoiceId'])) {
    $invoiceid = $_POST['invoiceId'];
    $netPriceVal = moneyRound($_POST['finalNetPrice']);
    if (isset($_POST['finalInvoiceService'], $_POST['finalInvoiceServiceAmt']) && is_array($_POST['finalInvoiceService']) && is_array($_POST['finalInvoiceServiceAmt'])) {
        $serviceSumVal = 0.0;
        $c = min(count($_POST['finalInvoiceService']), count($_POST['finalInvoiceServiceAmt']));
        $qtyArr = isset($_POST['finalInvoiceServiceQty']) && is_array($_POST['finalInvoiceServiceQty']) ? $_POST['finalInvoiceServiceQty'] : [];
        $rateArr = isset($_POST['finalInvoiceServiceRate']) && is_array($_POST['finalInvoiceServiceRate']) ? $_POST['finalInvoiceServiceRate'] : [];
        $useQtyRate = !empty($_POST['includeFinalQtyRate']) && count($qtyArr) > 0 && count($rateArr) > 0;
        for ($i = 0; $i < $c; $i++) {
            if ((string) $_POST['finalInvoiceService'][$i] !== '') {
                if ($useQtyRate) {
                    $serviceSumVal += round(moneyRound($qtyArr[$i] ?? 0) * moneyRound($rateArr[$i] ?? 0), 2);
                } else {
                    $serviceSumVal += moneyRound($_POST['finalInvoiceServiceAmt'][$i]);
                }
            }
        }
        if ($serviceSumVal > 0 && abs($netPriceVal - $serviceSumVal) > 0.009) {
            $netPriceVal = round($serviceSumVal, 2);
        }
    }
    if ($_POST["finalTaxtype"] == "IGST") {
        $sgst = "NA";
        $cgst = "NA";
        $igstVal = round($netPriceVal * 0.18, 2);
        $igst = moneyDb($igstVal);
        $total_amt = round($netPriceVal + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    } else if ($_POST["finalTaxtype"] == "CGST & SGST") {
        $igst = 'NA';
        $cgstVal = round($netPriceVal * 0.09, 2);
        $sgstVal = round($netPriceVal * 0.09, 2);
        $cgst = moneyDb($cgstVal);
        $sgst = moneyDb($sgstVal);
        $total_amt = round($netPriceVal + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/- </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/- </td></tr>';
    }

    $netPriceDb = moneyDb($netPriceVal);
    $totalAmtDb = moneyDb($total_amt);
    $invoiceUpdate = "UPDATE `invoice_details` SET `customer_id`= :customer,`invoice_date`= :date,`state`= :state,`state_code`= :statecode,`net_price`= :netprice,"
            . "`igst`= :igst,`cgst`= :cgst,`sgst`= :sgst,`tax_type`= :taxtype,`final_amount`= :totalAmt WHERE `invoice_id` = :id";
    $statement = $db->prepare($invoiceUpdate);
    $statement->bindParam(':id', $invoiceid);
    $statement->bindParam(':customer', $_POST["invoiceconsultingFirmName"]);
    $statement->bindParam(':date', date("Y-m-d", strtotime($_POST['invoiceDt'])));
    $statement->bindParam(':state', $_POST['invoiceState']);
    $statement->bindParam(':statecode', $_POST['invoiceStateCode']);
    $statement->bindParam(':netprice', $netPriceDb);
    $statement->bindParam(':igst', $igst);
    $statement->bindParam(':cgst', $cgst);
    $statement->bindParam(':sgst', $sgst);
    $statement->bindParam(':taxtype', $_POST["finalTaxtype"]);
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

    if (isset($_POST['finalInvoiceService']) && isset($_POST['finalInvoiceServiceAmt'])) {
        $serviceArray = $_POST['finalInvoiceService'];
        $serviceAmtArray = $_POST['finalInvoiceServiceAmt'];
        $serviceQtyArray = isset($_POST['finalInvoiceServiceQty']) && is_array($_POST['finalInvoiceServiceQty']) ? $_POST['finalInvoiceServiceQty'] : [];
        $serviceRateArray = isset($_POST['finalInvoiceServiceRate']) && is_array($_POST['finalInvoiceServiceRate']) ? $_POST['finalInvoiceServiceRate'] : [];
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
                if (!empty($_POST['includeFinalQtyRate']) && isset($serviceQtyArray[$count], $serviceRateArray[$count])) {
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
    }

    $invDate = $_POST['invoiceDt'];
    $netPrice = $netPriceVal;
    $state = $_POST["invoiceState"];
    $stateCode = $_POST["invoiceStateCode"];

    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $_POST['invoiceId']);
    $statement->execute();
    $innvoice = $statement->fetch();

    $invoiceDetail = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($invoiceDetail);
    $statement->bindParam(':id', $_POST['invoiceId']);
    $statement->execute();
    $invoiceSvc = $statement->fetchAll();

    $invno = $innvoice['final_invoice_no'];
    $invDate = $innvoice['invoice_date'];
    $netPrice = moneyRound($innvoice['net_price']);
    $sgst = $innvoice['sgst'] === 'NA' ? 'NA' : moneyRound($innvoice['sgst']);
    $cgst = $innvoice["cgst"] === 'NA' ? 'NA' : moneyRound($innvoice["cgst"]);
    $igst = $innvoice['igst'] === 'NA' ? 'NA' : moneyRound($innvoice['igst']);
    $total_amt = moneyRound($innvoice['final_amount']);
    $state = $innvoice['state'];
    $stateCode = $innvoice['state_code'];
    $finalprefix = $innvoice['final_prefix'];
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
} elseif (!empty($customerId) && !empty($_POST['invoiceDt'])) {
    $netPriceVal = moneyRound($_POST['finalNetPrice']);
    if ($_POST["finalTaxtype"] == "IGST") {
        $sgst = "NA";
        $cgst = "NA";
        $igstVal = round($netPriceVal * 0.18, 2);
        $igst = moneyDb($igstVal);
        $total_amt = round($netPriceVal + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    } else if ($_POST["finalTaxtype"] == "CGST & SGST") {
        $igst = 'NA';
        $cgstVal = round($netPriceVal * 0.09, 2);
        $sgstVal = round($netPriceVal * 0.09, 2);
        $cgst = moneyDb($cgstVal);
        $sgst = moneyDb($sgstVal);
        $total_amt = round($netPriceVal + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/- </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/- </td></tr>';
    }

    $invoiceDate = date("Y-m-d", strtotime($_POST['invoiceDt']));
    $finalprefix = 'CD/' . strtoupper(date('Y/M', strtotime($invoiceDate)));

    $monthlyInvoiceNoQuery = "SELECT COALESCE(MAX(CAST(final_invoice_no AS UNSIGNED)), 0) AS max_invoice_no
            FROM invoice_details
            WHERE invoice_type = 'final'
            AND final_prefix = :finalprefix
            AND YEAR(invoice_date) = :invoice_year
            AND MONTH(invoice_date) = :invoice_month";
    $statement = $db->prepare($monthlyInvoiceNoQuery);
    $invoiceYear = date('Y', strtotime($invoiceDate));
    $invoiceMonth = date('m', strtotime($invoiceDate));
    $statement->bindParam(':finalprefix', $finalprefix);
    $statement->bindParam(':invoice_year', $invoiceYear);
    $statement->bindParam(':invoice_month', $invoiceMonth);
    $statement->execute();
    $monthlyInvoiceInfo = $statement->fetch(PDO::FETCH_ASSOC);

    $finalNo = (int) $monthlyInvoiceInfo['max_invoice_no'] + 1;
    $netPriceDb = moneyDb($netPriceVal);
    $totalAmtDb = moneyDb($total_amt);
    $invoiceInsert = "INSERT INTO invoice_details (customer_id, profarma_prefix, invoice_no, final_prefix, final_invoice_no, invoice_date, state, state_code, net_price, igst, cgst, sgst, tax_type, final_amount, invoice_type)
            VALUES (:customer, :profarma_prefix, :invoice_no, :final_prefix, :final_invoice_no, :date, :state, :statecode, :netprice, :igst, :cgst, :sgst, :taxtype, :totalAmt, 'final')";
    $statement = $db->prepare($invoiceInsert);
    $emptyProformaPrefix = '';
    $emptyProformaNo = '0';
    $statement->bindParam(':customer', $customerId);
    $statement->bindParam(':profarma_prefix', $emptyProformaPrefix);
    $statement->bindParam(':invoice_no', $emptyProformaNo);
    $statement->bindParam(':final_prefix', $finalprefix);
    $statement->bindParam(':final_invoice_no', $finalNo);
    $statement->bindParam(':date', $invoiceDate);
    $statement->bindParam(':state', $_POST['invoiceState']);
    $statement->bindParam(':statecode', $_POST['invoiceStateCode']);
    $statement->bindParam(':netprice', $netPriceDb);
    $statement->bindParam(':igst', $igst);
    $statement->bindParam(':cgst', $cgst);
    $statement->bindParam(':sgst', $sgst);
    $statement->bindParam(':taxtype', $_POST["finalTaxtype"]);
    $statement->bindParam(':totalAmt', $totalAmtDb);
    $statement->execute();

    $sql = "SELECT max(LAST_INSERT_ID(invoice_id)) as id From invoice_details";
    $statement = $db->prepare($sql);
    $statement->execute();
    $row = $statement->fetch();
    $invoiceid = $row['id'];

    if (isset($_POST['finalInvoiceService']) && isset($_POST['finalInvoiceServiceAmt'])) {
        $serviceArray = $_POST['finalInvoiceService'];
        $serviceAmtArray = $_POST['finalInvoiceServiceAmt'];
        $serviceQtyArray = isset($_POST['finalInvoiceServiceQty']) && is_array($_POST['finalInvoiceServiceQty']) ? $_POST['finalInvoiceServiceQty'] : [];
        $serviceRateArray = isset($_POST['finalInvoiceServiceRate']) && is_array($_POST['finalInvoiceServiceRate']) ? $_POST['finalInvoiceServiceRate'] : [];
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
                if (!empty($_POST['includeFinalQtyRate']) && isset($serviceQtyArray[$count], $serviceRateArray[$count])) {
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
    }

    $nextFinalNo = $finalNo + 1;
    $updatefinvoiceno = "UPDATE consulting_firm SET final_prefix = :prefix, final_invoice_no = :no WHERE firm_id = '1'";
    $statement = $db->prepare($updatefinvoiceno);
    $statement->bindParam(':prefix', $finalprefix);
    $statement->bindParam(":no", $nextFinalNo);
    $statement->execute();

    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $invoiceid);
    $statement->execute();
    $innvoice = $statement->fetch();

    $invoiceDetail = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($invoiceDetail);
    $statement->bindParam(':id', $invoiceid);
    $statement->execute();
    $invoiceSvc = $statement->fetchAll();

    $invno = $innvoice['final_invoice_no'];
    $invDate = $innvoice['invoice_date'];
    $netPrice = moneyRound($innvoice['net_price']);
    $sgst = $innvoice['sgst'] === 'NA' ? 'NA' : moneyRound($innvoice['sgst']);
    $cgst = $innvoice["cgst"] === 'NA' ? 'NA' : moneyRound($innvoice["cgst"]);
    $igst = $innvoice['igst'] === 'NA' ? 'NA' : moneyRound($innvoice['igst']);
    $total_amt = moneyRound($innvoice['final_amount']);
    $state = $innvoice['state'];
    $stateCode = $innvoice['state_code'];
}

$targetInvoiceId = !empty($_POST['invoiceid']) ? $_POST['invoiceid'] : (!empty($_GET['invoiceid']) ? $_GET['invoiceid'] : (!empty($_REQUEST['invoiceid']) ? $_REQUEST['invoiceid'] : ''));

if (!empty($targetInvoiceId)) {

    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $targetInvoiceId);
    $statement->execute();
    $innvoice = $statement->fetch();

    $invoiceDetail = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($invoiceDetail);
    $statement->bindParam(':id', $targetInvoiceId);
    $statement->execute();
    $invoiceSvc = $statement->fetchAll();

    if (empty($customerInfo) && !empty($innvoice['customer_id'])) {
        $customerId = $innvoice['customer_id'];
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $customerId);
        $statement->execute();
        $customerInfo = $statement->fetch();
    }

    $invno = $innvoice['final_invoice_no'];
    $invDate = $innvoice['invoice_date'];
    $netPrice = moneyRound($innvoice['net_price']);
    $sgst = $innvoice['sgst'] === 'NA' ? 'NA' : moneyRound($innvoice['sgst']);
    $cgst = $innvoice["cgst"] === 'NA' ? 'NA' : moneyRound($innvoice["cgst"]);
    $igst = $innvoice['igst'] === 'NA' ? 'NA' : moneyRound($innvoice['igst']);
    $total_amt = moneyRound($innvoice['final_amount']);
    $state = $innvoice['state'];
    $stateCode = $innvoice['state_code'];
    $finalprefix = $innvoice['final_prefix'];

    if ($innvoice["tax_type"] == "CGST & SGST") {
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
        $cgstVal = round($netPrice * 0.09, 2);
        $sgstVal = round($netPrice * 0.09, 2);
        $total_amt = round($netPrice + $cgstVal + $sgstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . moneyFormat2($cgstVal) . '/- </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . moneyFormat2($sgstVal) . '/- </td></tr>';
    } else if ($innvoice["tax_type"] == "IGST") {
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
        $igstVal = round($netPrice * 0.18, 2);
        $total_amt = round($netPrice + $igstVal, 2);
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . moneyFormat2($igstVal) . '/- </td></tr>';
    }
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 12);
        $this->Ln(7);
        $this->SetXY(60, 15);
        $this->SetTextColor(128, 184, 224);
        $this->Cell(0, 15, '(NABCB ACCREDITED CERTIFICATION REGISTRAR FOR QMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 12);
        $this->Ln(7);
        $this->SetXY(60, 20);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 15, '(TAX INVOICE)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//
//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
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
$pdf->SetTitle('Final Invoice');
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

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
//echo $firmName;
// create some HTML content
//$html = '<table><tr><td width="10%" style="text-align:right"><img src="" alt="logo" height="50" width="50"></td><td width="90%" style="text-align:center; color:blue; font-size:1.6em; vertical-align: middle;"><br><span style="font-size:0.6em"></span> </td></tr>
//        <tr><td></td></tr></table>
//        <h2  style="text-align:center"><i>AITF MANAGEMENT CERTIFICATION LIMITED</i></h2>
//        <h2  style="text-align:center">(NABCB Accredited Certification Registrar for QMS)</h2>
//        <h2  style="text-align:center">PROFORMA INVOICE</h2>';
// output the HTML content
//$pdf->writeHTML($html, true, false, true, false, '');
// create some HTML content
$subtable = '<table border="2" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$includeQtyRate = !empty($_POST['includeFinalQtyRate']);
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
if (isset($_POST['finalInvoiceService']) && isset($_POST['finalInvoiceServiceAmt'])) {
    $serviceArray = $_POST['finalInvoiceService'];
    $serviceAmtArray = $_POST['finalInvoiceServiceAmt'];
    $serviceQtyArray = isset($_POST['finalInvoiceServiceQty']) && is_array($_POST['finalInvoiceServiceQty']) ? $_POST['finalInvoiceServiceQty'] : [];
    $serviceRateArray = isset($_POST['finalInvoiceServiceRate']) && is_array($_POST['finalInvoiceServiceRate']) ? $_POST['finalInvoiceServiceRate'] : [];
    if (!$includeQtyRate && count($serviceQtyArray) > 0 && count($serviceRateArray) > 0) {
        $includeQtyRate = true;
    }
    $count = 0;
    foreach ($serviceArray as $service) {
        if ($includeQtyRate) {
            $qtyVal = $serviceQtyArray[$count] ?? '';
            $rateVal = $serviceRateArray[$count] ?? '';
            $amtVal = $serviceAmtArray[$count] ?? '';
            if ($qtyVal !== '' && $rateVal !== '') {
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
            $serviceRow = $serviceRow . '<tr style="border-top: none"><td>' . ($count + 1) . '</td><td>' . $svc['service_desc'] . '</td><td>' . moneyFormat($qtyVal) . '</td><td>' . moneyFormat($rateVal) . '</td><td>' . moneyFormat2($svc['service_amount']) . '/-</td></tr>';
        } else {
            $serviceRow = $serviceRow . '<tr style="border-top: none"><td>' . ($count + 1) . '</td><td>' . $svc['service_desc'] . '</td><td>' . moneyFormat2($svc['service_amount']) . '/-</td></tr>';
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
    <td height="100"><b>Billed To - <br>NAME: ' . $customerInfo['customer_name']. '</b><br><b>ADDRESS: </b>' . $customerInfo['address'] . ' 
      <br> <br><b>GSTIN No.: </b>' . $customerInfo['gstin_no'] . '
        <br><b>STATE: </b>' . $state . '&nbsp; &nbsp; &nbsp; <b>STATE CODE: </b>' . $stateCode . '
    </td>
    <td ><br><b> INVOICE NO:' . $finalprefix . '-' . $invno . '</b><br><br><br><b>DATE:</b> ' . date("d.m.Y", strtotime($invDate)) . ' </td>
    </tr>
    <tr><td colspan="3"><b>SAC code : </b> 998311</td></tr>
    </table>
    <table border="1" cellspacing="1" cellpadding="4">
    ' . $serviceHeader . ' 
    ' . $serviceRow . '
    <tr><td colspan="' . $netColspan . '" style="text-align:right">Taxable Basic Amount</td><td> ' . moneyFormat($netPrice) . '/- </td></tr>
    ' . $gstsForTable . '
    <tr><td colspan="' . $finalColspan . '" style="text-align:right">TOTAL INVOICE VALUE WITH TAX</td><td><b>' . moneyFormat($total_amt) . '/-</b></td></tr>
    <tr><td colspan="' . $wordsColspan . '"><b>Total Amount in words: ' . convertToWord($total_amt) . '</b></td></tr>
    <tr><td colspan="' . $wordsColspan . '"><b>
    NOTE-</b>
    <ul style= "list-style-type:none; ">
    <li>* Please issue cheque in favor of AITF Management Certification Limited. Cash Payment is not acceptable</li>
    <li>* <b>GSTIN No.: 27AAJCA6651K1ZB,</b></li>
    <li>* PAN Number : ' . $firmInfo['pan_no'] . '</li>
    <li>* Bank details: ' . $firmInfo['firm_bank'] . ' Address - ' . $firmInfo['bank_address'] . '</li>
    <li>* Current Account No.- ' . $firmInfo['bank_ac_no'] . ',IFSC Code - ' . $firmInfo['bank_ifsc_code'] . ', MICR No.- ' . $firmInfo['bank_micr_no'] . '</li>
    <li>* For late payment interest will be charged at a rate of 10 % per month.</li>
    <li>* Signature not required if invoice is computer generated and send by AITF registered A/c mail ID i.e. "aitfacct@gmail.com".</li>
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
    ' . ($signaturePath !== '' ? '<img src="' . $signaturePath . '" alt="logo" height="50" width="90"><br/>' : '') . '
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
$pdf->Output('Final Invoice.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

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
