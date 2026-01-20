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
//database details fetch and display
$contract_chk_point = "";
$req_stated = "";
$req_notstated = "";
$req_stat_notacpt = "";
$req_notstat_notacpt = "";
$remark = "";

$yes11 = "";
$yes12 = "";
$yes13 = "";
$yes14 = "";
$yes15 = "";

$yes21 = "";
$yes22 = "";
$yes23 = "";
$yes24 = "";
$yes25 = "";

$yes31 = "";
$yes32 = "";
$yes33 = "";
$yes34 = "";
$yes35 = "";

$yes41 = "";
$yes42 = "";
$yes43 = "";
$yes44 = "";
$yes45 = "";

$yes51 = "";
$yes52 = "";
$yes53 = "";
$yes54 = "";
$yes55 = "";

$yes61 = "";
$yes62 = "";
$yes63 = "";
$yes64 = "";
$yes65 = "";

$yes71 = "";
$yes72 = "";
$yes73 = "";
$yes74 = "";
$yes75 = "";

$yes81 = "";
$yes82 = "";
$yes83 = "";
$yes84 = "";
$yes85 = "";

$yes91 = "";
$yes92 = "";
$yes93 = "";
$yes94 = "";
$yes95 = "";

$yes101 = "";
$yes102 = "";
$yes103 = "";
$yes104 = "";
$yes105 = "";


$yes201 = "";
$yes202 = "";
$yes203 = "";
$yes204 = "";
$yes205 = "";

$yes301 = "";
$yes302 = "";
$yes303 = "";
$yes304 = "";
$yes305 = "";

$yes401 = "";
$yes402 = "";
$yes403 = "";
$yes404 = "";
$yes405 = "";

$yes501 = "";
$yes502 = "";
$yes503 = "";
$yes504 = "";
$yes505 = "";

$yes601 = "";
$yes602 = "";
$yes603 = "";
$yes604 = "";
$yes605 = "";

$yes701 = "";
$yes702 = "";
$yes703 = "";
$yes704 = "";
$yes705 = "";


$yes801 = "";
$yes802 = "";
$yes803 = "";
$yes804 = "";
$yes805 = "";

$yes901 = "";
$yes902 = "";
$yes903 = "";
$yes904 = "";
$yes905 = "";

$yes111 = "";
$yes112 = "";
$yes113 = "";
$yes114 = "";
$yes115 = "";

$yes211 = "";
$yes212 = "";
$yes213 = "";
$yes214 = "";
$yes215 = "";

$yes311 = "";
$yes312 = "";
$yes313 = "";
$yes314 = "";
$yes315 = "";

$yes411 = "";
$yes412 = "";
$yes413 = "";
$yes414 = "";
$yes415 = "";
$dateOfReview = "";

$customer = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customer);
$statement->bindParam(':id', $_POST['customer']);
$statement->execute();
$customerInfo = $statement->fetch();

$id = "";

if (isset($_POST['contractid'])) {

    $contractId = "SELECT * FROM contract_id WHERE contractid = :id";
    $statement = $db->prepare($contractId);
    $statement->bindParam(':id', $_POST['contractid']);
    $statement->execute();
    $contractInfo = $statement->fetch();

    if (isset($contractInfo['review_date']) && !empty($contractInfo['review_date'])) {
        $dateOfReview = date("d-m-Y", strtotime($contractInfo['review_date']));
    } else {
        $dateOfReview = '';
    }
    $contract = "SELECT * FROM contract_review WHERE ca_service_contract = :id";
    $statement = $db->prepare($contract);
    $statement->bindParam(':id', $_POST['contractid']);
    $statement->execute();
    $contractDetails = $statement->fetchAll();

    foreach ($contractDetails as $value) {

        if ($value['contract_chk_point'] == "Process of certification activity") {
            if ($value['req_stated'] == "true") {
                $yes11 = "√";
            } else if ($value['req_notstated'] == "true") {
                $yes12 = "√";
            } else if ($value['req_stat_notacpt'] == "true") {
                $yes13 = "√";
            } else if ($value['req_notstat_notacpt'] == "true") {
                $yes14 = "√";
            }
//            else if ($value['remark'] == "true") {
//                $yes15 = "√";
//            }
            $yes15 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Scope for certification & standard") {
            if ($value['req_stated'] == "true") {
                $yes21 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes22 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes23 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes24 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes25 = "√";
//            }
            $yes25 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Service agreement for management system assessment (The Services, Warranty, Timing & Progress of the service, Postponement of Services, Duration & termination, Confidentiality, Intellectual Property rights, Payment, Indemnities and limitation of liabilities, force majeure, assignment, Dispute resolution and  Governing Law, Publication)") {

            if ($value['req_stated'] == "true") {
                $yes31 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes32 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes33 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes34 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes35 = "√";
//            }
            $yes35 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Complains & Appeals") {

            if ($value['req_stated'] == "true") {
                $yes41 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes42 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes43 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes44 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes45 = "√";
//            }
            $yes45 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Suspension, Withdrawal, Cancellation of certificate") {

            if ($value['req_stated'] == "true") {
                $yes51 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes52 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes53 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes54 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes55 = "√";
//            }
            $yes55 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Miscellaneous") {

            if ($value['req_stated'] == "true") {
                $yes61 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes62 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes63 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes64 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes65 = "√";
//            }
            $yes65 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Scope of service") {

            if ($value['req_stated'] == "true") {
                $yes71 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes72 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes73 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes74 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes75 = "√";
//            }
            $yes75 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Nonconformity") {

            if ($value['req_stated'] == "true") {
                $yes81 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes82 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes83 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes84 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes85 = "√";
//            }
            $yes85 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Corrective actions & surveillance audit") {

            if ($value['req_stated'] == "true") {
                $yes91 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes92 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes93 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes94 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes95 = "√";
//            }
            $yes95 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Certificate of conformity & Retention") {

            if ($value['req_stated'] == "true") {
                $yes101 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes102 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes103 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes104 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes105 = "√";
//            }
            $yes105 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Renewal of the certificate") {

            if ($value['req_stated'] == "true") {
                $yes201 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes202 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes203 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes204 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes205 = "√";
//            }
            $yes205 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Audit team") {

            if ($value['req_stated'] == "true") {
                $yes301 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes302 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes303 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes304 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes305 = "√";
//            }
            $yes305 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Control of mark") {

            if ($value['req_stated'] == "true") {
                $yes401 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes402 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes403 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes404 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes405 = "√";
//            }
            $yes405 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Stake holder committee (SHC) (Impartiality committee)") {

            if ($value['req_stated'] == "true") {
                $yes501 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes502 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes503 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes504 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes505 = "√";
//            }
            $yes505 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Confidentiality and independence") {

            if ($value['req_stated'] == "true") {
                $yes601 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes602 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes603 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes604 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes605 = "√";
//            }
            $yes605 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Initial Certification") {

            if ($value['req_stated'] == "true") {
                $yes701 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes702 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes703 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes704 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes705 = "√";
//            }
            $yes705 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Maintenance of Certification") {

            if ($value['req_stated'] == "true") {
                $yes801 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes802 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes803 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes804 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes805 = "√";
//            }
            $yes805 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Special Surveillance") {

            if ($value['req_stated'] == "true") {
                $yes901 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes902 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes903 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes904 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes905 = "√";
//            }
            $yes905 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Recertification") {

            if ($value['req_stated'] == "true") {
                $yes111 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes112 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes113 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes114 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes115 = "√";
//            }
            $yes115 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Follow – up Visits") {

            if ($value['req_stated'] == "true") {
                $yes211 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes212 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes213 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes214 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes215 = "√";
//            }
            $yes215 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Travel and Living Expenses") {

            if ($value['req_stated'] == "true") {
                $yes311 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes312 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes313 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes314 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes315 = "√";
//            }
            $yes315 = $value['remark'];
        }

        if ($value['contract_chk_point'] == "Service tax") {

            if ($value['req_stated'] == "true") {
                $yes411 = "√";
            } elseif ($value['req_notstated'] == "true") {
                $yes412 = "√";
            } elseif ($value['req_stat_notacpt'] == "true") {
                $yes413 = "√";
            } elseif ($value['req_notstat_notacpt'] == "true") {
                $yes414 = "√";
            }
//            elseif ($value['remark'] == "true") {
//                $yes415 = "√";
//            }
            $yes415 = $value['remark'];
        }
    }
}

// Extend the TCPDF class to create custom Header and Footer

class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';
    protected $count = '1';

    public function setInfo($info) {
        $this->count = $count;
    }

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(50, 18);
        $this->SetFont('helvetica', 'B', 12);
        // $this->Ln(7);
        //SubTitle
        $this->Cell(0, 25, 'CONTRACT REVIEW', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        //page Bordar

        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {

        $html = '<table border="1"  cellpadding="4" cellspacing="0">';
        $html .= '<tr><td colspan="1">Form No. F 7.04</td>';
        $html .= '<td colspan="2">Rev.:00, Date: 01.10.2013</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 01.10.2013</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of 1]</td></tr>';
        $html .= '</table>';
        // Position at 15 mm from bottom
        $this->SetXY(17,-10);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 7, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Contract Review');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 22, 5);
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
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();


// create some HTML content
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="8">
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td width="30%">        
                        <font size="8"><i> Name of the customer:</i></font>
                    </td>
                    <td width="70%">
                        <font size="8"><i>' . $customerInfo['customer_name'] . '</i></font>
                    </td>       
                </tr>
                <tr>        
                    <td width="30%">       
                        <font size="8"><i> Address:</i></font>           
                    </td>
                    <td width="70%"> 
                        <font size="8"><i>' . $customerInfo['address'] . '</i></font>
                    </td>
                </tr>
                <tr>        
                    <td width="30%">        
                        <font size="8"><i> Contract Agreement No:</i></font>     
                    </td>
                    <td colspan="70%">
                        <font size="8"><i>' . $customerInfo['unique_id'] . '</i></font>
                    </td>        
                </tr>    
            </table>
        </font></p>';



// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// create some HTML content
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="8">
            <table border="1"   >
                <tr>

<td width="5%" align="center"><br><br><font size="8">Sr.</font></td> 
<td width="28%" align="center" ><br><br><font size="8">Contract review check point</font>
</td> 

<td width="53%" >


<table border="1" cellpadding="4" cellspacing="0">

<tr>

<td colspan="4" align="center"><font size="8">Review comments ( √ Mark at appropriate )</font></td>

</tr>

<tr>

<td rowspan="1" align="center"><p style="text-align: justify;text-justify: inter-word;"><font size="8">Requirement stated & accepted
</font></p>
</td>
<td rowspan="1" align="center"><p style="text-align: justify;text-justify: inter-word;"><font size="8">Requirement not stated to be clarified
</font></p>
</td>
<td rowspan="1" align="center"><p style="text-align: justify;text-justify: inter-word;"><font size="8">Requirement stated and not acceptable to be resolved
</font></p>
</td>
<td rowspan="1" align="center" ><p style="text-align: justify;text-justify: inter-word;"><font size="8">Not stated /Not acceptable
</font></p>
</td>

</tr>

</table>

</td> 

<td width="14%" align="center">
<p style="text-align: justify;text-justify: inter-word;"><font size="8"><br><br>&nbsp;Remark and
<br>&nbsp;follow up
<br>&nbsp;actions,if any. </font></p>
</td>
</tr>
</table>
<table border="1"  cellpadding="2" cellspacing="0" align="center">

<tr  bgcolor="#DCDCDC">
     <td align="left"width="100%" ><font size="8"><i>General</i></font></td>
</tr>
<tr>
<td width="5%" align="center"><font size="9">1.</font></td> 
<td width="28%" align="center" ><p style="text-align: justify;text-justify: inter-word;"><font size="8">Process of certification activity
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes11 . '</font>

</td>

<td width="13.4%" >
<font size="8">' . $yes12 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes13 . '</font>
</td> 

<td width="13.4%" >

<font size="8">' . $yes14 . '</font>
</td> 

<td width="13.4%" >

<font size="8">' . $yes15 . '</font>
</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">2.</font></td> 
<td width="28%" align="center" ><p style="text-align: justify;text-justify: inter-word;"><font size="8">Scope for certification & standard

</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes21 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes22 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes23 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes24 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes25 . '</font>

</td> 
</tr>

<tr  bgcolor="#DCDCDC">
     <td align="left"width="100%"><font size="8"><i>Service</i></font></td>
</tr>

<tr>

<td width="5%" align="center"><font size="8">1.</font></td> 
<td width="28%" align="center" ><p style="text-align:left;text-justify:inter-word;"><font size="8">Service agreement for management system assessment
<br>(The Services,Warranty,Timing& Progress of the service,Postponement of Services,Duration & termination,Confidentiality,Intellectual Property rights,Payment,Indemnities and limitation of liabilities,force majeure,assignment,Dispute resolution and Governing Law,Publication)
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes31 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes32 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes33 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes34 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes35 . '</font>

</td> 
</tr>

<tr  bgcolor="#DCDCDC">
     <td align="left" width="100%"><font size="8"><i>Processes</i></font></td>
</tr>

<tr>

<td width="5%" align="center"><font size="8">1.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Complaints & Appeals

</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes41 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes42 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes43 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes44 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes45 . '</font>


</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">2.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Suspension,Withdrawal,Cancellation
<br>of certificate
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes51 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes52 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes53 . '</font>
</td> 
<td width="13.4%" >
<font size="8">' . $yes54 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes55 . '</font>
</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">3.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Miscellaneous

</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes61 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes62 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes63 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes64 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes65 . '</font>
</td> 
</tr>

<tr  bgcolor="#DCDCDC">
     <td align="left" width="100%"><font size="8"><i>Basic service details</i></font></td>
</tr>

<tr>

<td width="5%" align="center"><font size="8">1.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Scope of service

</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes71 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes72 . '</font>
</td> 
<td width="13.4%" >
<font size="8">' . $yes73 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes74 . '</font>
</td> 

<td width="13.4%" >
<font size="8">' . $yes75 . '</font>

</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">2.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Nonconformity

</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes81 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes82 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes83 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes84 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes85 . '</font>


</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">3.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Corrective actions & surveillance audit</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes91 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes92 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes93 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes94 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes95 . '</font>

</td> 
</tr>
<tr>

<td width="5%" align="center"><font size="8">4.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Certificate of conformity & Retention


</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes101 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes102 . '</font>
</td> 
<td width="13.4%" >

<font size="8">' . $yes103 . '</font>
</td> 
<td width="13.4%" >
<font size="8">' . $yes104 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes105 . '</font>
</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">5.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Renewal of the certificate
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes201 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes202 . '</font>
</td> 
<td width="13.4%" >

<font size="8">' . $yes203 . '</font>
</td> 
<td width="13.4%" >

<font size="8">' . $yes204 . '</font>
</td> 

<td width="13.4%" >
<font size="8">' . $yes205 . '</font>

</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">6.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Audit team
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes301 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes302 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes303 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes304 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes305 . '</font>

</td> 
</tr>
<tr>

<td width="5%" align="center"><font size="8">7.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Control of mark
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes401 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes402 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes403 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes404 . '</font>


</td> 

<td width="13.4%" >

<font size="8">' . $yes405 . '</font>

</td> 

</tr>

<tr align="center">
<td width="5%" align="center"><font size="8">8.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Stake-holder committee(SHC)(Impartiality committee)
</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes501 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes502 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes503 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes504 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes505 . '</font>


</td> 
</tr>

<tr>
<td width="5%" align="center"><font size="8">9.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Confidentiality and independence
</font></p>
</td> 
<td width="13.4%" >

<font size="8">' . $yes601 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes602 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes603 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes604 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes605 . '</font>


</td> 
</tr>

<tr  bgcolor="#DCDCDC">
     <td align="left" width="100%"><font size="8"><i>Commercial</i></font></td>
</tr>

<tr>

<td width="5%" align="center"><font size="8">1.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Initial Certification</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes701 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes702 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes703 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes704 . '</font>
</td> 

<td width="13.4%" >
<font size="8">' . $yes705 . '</font>
</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">2.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Maintenance of Certification</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes801 . '</font>
</td> 
<td width="13.4%" >
<font size="8">' . $yes802 . '</font>
</td> 
<td width="13.4%" >

<font size="8">' . $yes803 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes804 . '</font>

</td> 

<td width="13.4%" >
<font size="8">' . $yes805 . '</font>


</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">3.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Special Surveillance</font></p>
</td> 
<td width="13.4%" >

<font size="8">' . $yes901 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes902 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes903 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes904 . '</font>


</td> 

<td width="13.4%" >

<font size="8">' . $yes905 . '</font>

</td> 
</tr>
<tr>

<td width="5%" align="center"><font size="8">4.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Recertification</font></p>
</td> 
<td width="13.4%" >

<font size="8">' . $yes111 . '</font>


</td> 
<td width="13.4%" >

<font size="8">' . $yes112 . '</font>
    
</td> 
<td width="13.4%" >
<font size="8">' . $yes113 . '</font>

</td> 
<td width="13.4%" >
<font size="8">' . $yes114 . '</font>

</td> 

<td width="13.4%" >

<font size="8">' . $yes115 . '</font>
</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">5.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Follow – up Visits</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes211 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes212 . '</font>
    
</td> 
<td width="13.4%" >
<font size="8">' . $yes213 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes214 . '</font>
</td> 

<td width="13.4%" >
<font size="8">' . $yes215 . '</font>

</td> 
</tr>

<tr>

<td width="5%" align="center"><font size="8">6.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Travel and Living Expenses</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes311 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes312 . '</font>

</td> 
<td width="13.4%" >

<font size="8">' . $yes313 . '</font>
    
</td> 
<td width="13.4%" >

<font size="8">' . $yes314 . '</font>
    
</td> 

<td width="13.4%" >

<font size="8">' . $yes315 . '</font>

</td> 
</tr>
<tr>

<td width="5%" align="center"><font size="8">7.</font></td> 
<td width="28%" align="center" ><p style="text-align:justify;text-justify:inter-word;"><font size="8">Service tax</font></p>
</td> 
<td width="13.4%" >
<font size="8">' . $yes411 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes412 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes413 . '</font>


</td> 
<td width="13.4%" >
<font size="8">' . $yes414 . '</font>


</td> 

<td width="13.4%" >
<font size="8">' . $yes415 . '</font>


</td> 

</tr>




</table>
</font></p>
    


    
<br><span><font size="8"><i>Conclusion: ' . $contractInfo['conclusion'] . '</i></font></span><br>

<br><span><font size="8"><i>Name of Reviewer: '  . $contractInfo['reviewer'] . ' </i></font></span><br>

<br><span><font size="8"><i>Date of review: ' . $dateOfReview . '</i></font></span>';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


//Close and output PDF document
$pdf->Output('ContractReview.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+