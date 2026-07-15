<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
require_once('tcpdf_include.php');
//echo $_POST['control-format-service'];
//echo $_POST['control-format-customer'];

if (isset($_POST['control-format-id']) && !empty($_POST['control-format-id'])) {
    try {
        $cfid = $_POST['control-format-id'];
        $service = $_POST['control-format-service'];
        $customer = $_POST['control-format-customer'];
        if (isset($_POST['controlFormat1CertDate']) && !empty($_POST['controlFormat1CertDate'])) {
            $controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
        } else {
            $controldate = NULL;
        }
        if (isset($_POST['reviewDateControl1']) && !empty($_POST['reviewDateControl1'])) {
            $review_date = date("Y-m-d", strtotime($_POST['reviewDateControl1']));
        } else {
            $review_date = NULL;
        }
       
        //$controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
        $question = explode('~', $_POST['risk-question-hidden']);
        $comment = explode('~', $_POST['comment-hidden']);
        $risk = explode('~', $_POST['risk-hidden']);
        $riskDegree = explode('~', $_POST['risk-degree-hidden']);
        //print_r($riskDegree);
        $controls = explode('~', $_POST['controls-hidden']);
        $srno = explode('~', $_POST['srno-hidden']);
        $title = explode('~', $_POST['title-hidden']);
        if($_POST['clientreferance']=="Other")
                $certtype = $_POST['Othertext'];
        else  $certtype = $_POST['clientreferance'];

        // Handle certificate numbers - can be array or single value
        $certificateNo = '';
        if (isset($_POST['controlFormat1RefNo'])) {
            if (is_array($_POST['controlFormat1RefNo'])) {
                $certificateNo = implode(', ', array_filter($_POST['controlFormat1RefNo']));
            } else {
                $certificateNo = $_POST['controlFormat1RefNo'];
            }
        }
        
        $cfidedit = "UPDATE control_formati_id SET control_client = :client, control_ref_no = :refno, cert_type= :certty, control_address = :address, control_date = :date WHERE  control_id = :id ";
        $statement = $db->prepare($cfidedit);
        $statement->bindParam(':client', $_POST['controlFormat1ClientName']);
        $statement->bindParam(':refno', $certificateNo);
        $statement->bindParam(':certty', $certtype);
        $statement->bindParam(':address', $_POST['controlFormat1Address']);
        $statement->bindParam(':date', $controldate);
        $statement->bindParam(':id', $cfid);
        $statement->execute();
        
        $dltcontrolFormat = "DELETE FROM control_formati WHERE control_id = :id";
        $statement = $db->prepare($dltcontrolFormat);
        $statement->bindParam(':id', $cfid);
        $statement->execute();
        $riskDegreeVal = "";
        for ($i = 0; $i < count($question); ++$i) {
            if ($question[$i] != "") {
                if ($risk[$i] == "Yes") {
                    $riskDegreeVal = $riskDegree[$i];
                } else {
                    $riskDegreeVal = "";
                }
                $controlFormat = "INSERT INTO control_formati(control_id, caservice, control_sr, control_title, control_question, comments, risks, risk_degree, controls) VALUES (:id,:service,:srno,:title,:question,:comment,:risk,:riskDegree,:controls)";
                $statement = $db->prepare($controlFormat);
                $statement->bindParam(':id', $cfid);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':srno', $srno[$i]);
                $statement->bindParam(':title', $title[$i]);
                $statement->bindParam(':question', $question[$i]);
                $statement->bindParam(':comment', $comment[$i]);
                $statement->bindParam(':risk', $risk[$i]);
                $statement->bindParam(':riskDegree', $riskDegreeVal);
                $statement->bindParam(':controls', $controls[$i]);
                $statement->execute();
            }
        }
        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {    
    if (isset($_POST['control-format-service']) && !empty($_POST['control-format-service']) && isset($_POST['control-format-customer']) && !empty($_POST['control-format-customer'])) {
        try {
            
            if (isset($_POST['addControlFormat1'])) {
                $service = $_POST['control-format-service'];
                $customer = $_POST['control-format-customer'];
                if (isset($_POST['controlFormat1CertDate']) && !empty($_POST['controlFormat1CertDate'])) {
                    $controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
                } else {
                    $controldate = NULL;
                }
                if (isset($_POST['reviewDateControl1']) && !empty($_POST['reviewDateControl1'])) {
                    $review_date = date("Y-m-d", strtotime($_POST['reviewDateControl1']));
                } else {
                    $review_date = NULL;
                }
                //$controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
                $question = explode('~', $_POST['risk-question-hidden']);
                $comment = explode('~', $_POST['comment-hidden']);
                $risk = explode('~', $_POST['risk-hidden']);
                $riskDegree = explode('~', $_POST['risk-degree-hidden']);
                $controls = explode('~', $_POST['controls-hidden']);
                $srno = explode('~', $_POST['srno-hidden']);
                $title = explode('~', $_POST['title-hidden']);
                if($_POST['clientreferance']=== "Other")
                {
                    $certtype = $_POST['Othertext'];
                }
                else  $certtype = $_POST['clientreferance'];
                
                // Handle certificate numbers - can be array or single value
                $certificateNo = '';
                if (isset($_POST['controlFormat1RefNo'])) {
                    if (is_array($_POST['controlFormat1RefNo'])) {
                        $certificateNo = implode(', ', array_filter($_POST['controlFormat1RefNo']));
                    } else {
                        $certificateNo = $_POST['controlFormat1RefNo'];
                    }
                }
                
                $format1id = "INSERT INTO control_formati_id (customer_id, caservice_id, control_client, control_ref_no, cert_type, control_address, control_date) "
                        . "VALUES (:customer,:service,:client,:refno,:certty,:address,:date)";
                $statement = $db->prepare($format1id);
                $statement->bindParam(':customer', $customer);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':client', $_POST['controlFormat1ClientName']);
                $statement->bindParam(':refno', $certificateNo);
                $statement->bindParam(':certty', $certtype);
                $statement->bindParam(':address', $_POST['controlFormat1Address']);
                $statement->bindParam(':date', $controldate);
                $statement->execute();

                $sql = "SELECT max(LAST_INSERT_ID(control_id)) as id From control_formati_id";
                $statement = $db->prepare($sql);
                $statement->execute();
                $row = $statement->fetch();
                $controlid = $row['id'];
                $riskDegreeVal = "";
                for ($i = 0; $i < count($question); ++$i) {
                    if ($question[$i] != "") {
                        if ($risk[$i] == "Yes") {
                            $riskDegreeVal = $riskDegree[$i];
                        } else {
                            $riskDegreeVal = "";
                        }
                        $controlFormat = "INSERT INTO control_formati(control_id, caservice, control_sr, control_title, control_question, comments, risks, risk_degree, controls) VALUES (:id,:service,:srno,:title,:question,:comment,:risk,:riskDegree,:controls)";
                        $statement = $db->prepare($controlFormat);
                        $statement->bindParam(':id', $controlid);
                        $statement->bindParam(':service', $service);
                        $statement->bindParam(':srno', $srno[$i]);
                        $statement->bindParam(':title', $title[$i]);
                        $statement->bindParam(':question', $question[$i]);
                        $statement->bindParam(':comment', $comment[$i]);
                        $statement->bindParam(':risk', $risk[$i]);
                        $statement->bindParam(':riskDegree', $riskDegreeVal);
                        $statement->bindParam(':controls', $controls[$i]);
                        $statement->execute();
                    }
                }
                header("Location: customerAccount.php?customerid=" . $customer);
            }
            if (isset($_POST['addControlFormat1Pdf'])) {
                $service = $_POST['control-format-service'];
                $customer = $_POST['control-format-customer'];
                if (isset($_POST['controlFormat1CertDate']) && !empty($_POST['controlFormat1CertDate'])) {
                    $controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
                } else {
                    $controldate = NULL;
                }
                //$controldate = date("Y-m-d", strtotime($_POST['controlFormat1CertDate']));
                $question = explode('~', $_POST['risk-question-hidden']);
                $comment = explode('~', $_POST['comment-hidden']);
                $risk = explode('~', $_POST['risk-hidden']);
                $riskDegree = explode('~', $_POST['risk-degree-hidden']);
                $controls = explode('~', $_POST['controls-hidden']);
                $srno = explode('~', $_POST['srno-hidden']);
                $title = explode('~', $_POST['title-hidden']);
                  if($_POST['clientreferance']=="other")
                $certtype = $_POST['Othertext'];
                else  $certtype = $_POST['clientreferance'];
                
                // Handle certificate numbers - can be array or single value
                $certificateNo = '';
                if (isset($_POST['controlFormat1RefNo'])) {
                    if (is_array($_POST['controlFormat1RefNo'])) {
                        $certificateNo = implode(', ', array_filter($_POST['controlFormat1RefNo']));
                    } else {
                        $certificateNo = $_POST['controlFormat1RefNo'];
                    }
                }
                
                $format1id = "INSERT INTO control_formati_id (customer_id, caservice_id, control_client, control_ref_no, cert_type, control_address, control_date) "
                        . " VALUES (:customer,:service,:client,:refno,:certty,:address,:date)";
                $statement = $db->prepare($format1id);
                $statement->bindParam(':customer', $customer);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':client', $_POST['controlFormat1ClientName']);
                $statement->bindParam(':refno', $certificateNo);
                $statement->bindParam(':certty', $certtype);
                $statement->bindParam(':address', $_POST['controlFormat1Address']);
                $statement->bindParam(':date', $controldate);
                $statement->execute();

                $sql = "SELECT max(LAST_INSERT_ID(control_id)) as id From control_formati_id";
                $statement = $db->prepare($sql);
                $statement->execute();
                $row = $statement->fetch();
                $controlid = $row['id'];

                for ($i = 0; $i < count($question); ++$i) {
                    if ($question[$i] != "") {
                        $controlFormat = "INSERT INTO control_formati(control_id, caservice, control_sr, control_title, control_question, comments, risks, risk_degree, controls) VALUES (:id,:service,:srno,:title,:question,:comment,:risk,:riskDegree,:controls)";
                        $statement = $db->prepare($controlFormat);
                        $statement->bindParam(':id', $controlid);
                        $statement->bindParam(':service', $service);
                        $statement->bindParam(':srno', $srno[$i]);
                        $statement->bindParam(':title', $title[$i]);
                        $statement->bindParam(':question', $question[$i]);
                        $statement->bindParam(':comment', $comment[$i]);
                        $statement->bindParam(':risk', $risk[$i]);
                        $statement->bindParam(':riskDegree', $riskDegree[$i]);
                        $statement->bindParam(':controls', $controls[$i]);
                        $statement->execute();
                    }
                }

                $control_client = "";
                $control_ref_no = "";
                $control_address = "";
                $control_date = "";
                $allrow1 = "";
                $allrow2 = "";
                $allrow3 = "";
                $allrow4 = "";

                $controlformati = "SELECT * FROM control_formati_id WHERE control_id = :id";
                $statement = $db->prepare($controlformati);
                $statement->bindParam(':id', $controlid);
                $statement->execute();
                $controlformatiDetails = $statement->fetch();

                $control_client = $controlformatiDetails['control_client'];
                $control_ref_no = $controlformatiDetails['control_ref_no'];
                $control_address = $controlformatiDetails['control_address'];
                $control_date = date("d-m-Y", strtotime($controlformatiDetails['control_date']));

                $formatiData = "SELECT * FROM  control_formati WHERE control_id = :id";
                $statement = $db->prepare($formatiData);
                $statement->bindParam(':id', $controlid);
                $statement->execute();
                $formatiDetails = $statement->fetchAll();

                foreach ($formatiDetails as $value) {
                    if ($value['control_title'] == "CONSULTANCY") {
                        $allrow1 = $allrow1 . '
<tr>

<td width="7%" align="center"><br><br>' . $value['control_sr'] . '</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="15.75%" >
' . $value['comments'] . '

</td> 
<td width="15.75%" >

' . $value['risks'] . '
</td> 
<td width="15.75%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="15.75%" >
' . $value['controls'] . '

</td> 
</tr>';
                    } elseif ($value['control_title'] == "TRAINING, INTERNAL AUDITS & OTHER SERVICES") {

                        $allrow2 = $allrow2 . '
<tr>

<td width="7%" align="center"><br><br>' . $value['control_sr'] . '</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="15.75%" >
' . $value['comments'] . '

</td> 
<td width="15.75%" >

' . $value['risks'] . '
</td> 
<td width="15.75%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="15.75%" >
' . $value['controls'] . '

</td> 
</tr>';
                    } elseif ($value['control_title'] == "AUDITOR SELECTION/ALLOCATION") {

                        $allrow3 = $allrow3 . '
<tr>

<td width="7%" align="center"><br><br>' . $value['control_sr'] . '</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="15.75%" >
' . $value['comments'] . '

</td> 
<td width="15.75%" >

' . $value['risks'] . '
</td> 
<td width="15.75%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="15.75%" >
' . $value['controls'] . '

</td> 
</tr>
';
                    } elseif ($value['control_title'] == "CERTIFICATION DECISION") {

                        $allrow4 = $allrow4 . '
<tr>

<td width="7%" align="center"><br><br>' . $value['control_sr'] . '</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="15.75%" >
' . $value['comments'] . '

</td> 
<td width="15.75%" >

' . $value['risks'] . '
</td> 
<td width="15.75%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="15.75%" >
' . $value['controls'] . '

</td> 
</tr>
';
                    }
                }
            }

// Extend the TCPDF class to create custom Header and Footer
            class MYPDF extends TCPDF {

                protected $processId = 0;
                protected $header = '';
                protected $footer = '';
                static $errorMsg = '';

                // Page footer
                public function Footer() {
                    // Position at 15 mm from bottom
                    $this->SetY(-15);
                    // Set font
                    $this->SetFont('helvetica', 'I', 10);
                    $this->Cell(0, 0, '', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                    // Page number
                    $this->Cell(0, 10, 'Page- ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

                    //page border
                    $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
                    $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

                    $this->Line(5, 5, $this->getPageWidth() - 5, 5);

                    $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
                    $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
                    $this->Line(5, 5, 5, $this->getPageHeight() - 5);
                }

            }

// create new PDF document
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('AITF');
            $pdf->SetTitle('CONTROLFORMAT-1');
            $pdf->SetSubject('AITF');
            $pdf->SetKeywords('AITF, PDF, example, test, guide');

// remove default header/footer
            $pdf->setPrintHeader(false);

            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


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


//html content Start Here
            $html = ' <table border="1"  cellpadding="4" cellspacing="0" width="100%" >
    
<tr bgcolor="#dcd8c4">
   <td align="center"><font size="9" style="text-align:justify;">In compliance with the requirements of ISO 17021, AITF is performing a review of its certification activities to address the issue of conflict of Interest (COI) and analyze any existing or potential risk to impartiality of its activities.  
"Control Points” are established and analysis is carried out for each client while initial/ Recertification assessment and whenever need arrives.

       
       <br>
       <br>If any risk is identified, and, if its degree is “high,” then steps shall be taken to make it low or none. If any risk is likely to occur frequently, it shall be marked “high”.   
If there are chances of occurrence of risk, it shall be marked “low”, 
else, it shall be marked “none”.


        </font>
        </td> 
</tr>

       
</table>

<table border="1"  cellpadding="4" cellspacing="0" width="100%" >

<tr>
<td > Name of client : ' . $control_client . '</td> 
<td >Certificate No. ' . $control_ref_no . '</td> 
</tr>

<tr>
<td > Address/Location: ' . $control_address . '</td> 
<td>Date of review: ' . $control_date . '</td> 
</tr>
</table>

<table border="1"  >
<tr>

<td width="7%" align="center"><br><br>Sr.No.</td> 
<td width="30%" align="center" ><br><br>Question (Risks)</td> 

<td width="63%" >


<table border="1" cellpadding="4" cellspacing="0">

<tr>

<td colspan="4" align="center"><b>CONTROL POINT-1</b> 
<br><b>Certification Decision Committee (CDC)</b>
</td>

</tr>

<tr>

<td rowspan="1" align="center">Comments</td>
<td rowspan="1" align="center">Risk,if any</td>
<td rowspan="1" align="center">Degree of Risk</td>
<td rowspan="1" align="center" >Controls</td>

</tr>

</table>

</td> 

</tr>

</table>

<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>CONSULTANCY</b>
</td>
</tr>
</table>


<table  border="1" cellpadding="4" cellspacing="0" >

' . $allrow1 . '
</table>

<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>TRAINING,INTERNAL AUDITS & OTHER SERVICES</b>
</td>
</tr>
</table>

<table  border="1" cellpadding="4" cellspacing="0" >

' . $allrow2 . '
</table>
';



// output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
            $pdf->lastPage();



// add a page
            $pdf->AddPage();
//html content Start Here
            $html = '<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>AUDITOR SELECTION/ALLOCATION</b>
</td>
</tr>
</table>

<table  border="1" cellpadding="4" cellspacing="0" >

' . $allrow3 . '
</table>

<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>CERTIFICATION DECISION</b>
</td>
</tr>
</table>  

<table  border="1" cellpadding="4" cellspacing="0" >

' . $allrow4 . '
    
 <tr>   
 <td width="100%">
<pre><b>REMARKS:</b></pre>
<pre><b>Signature & Date</b></pre>
</td>
</tr>

</table>



';

// output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
            $pdf->Output('CONTROLFORMAT-1.pdf', 'I');
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
