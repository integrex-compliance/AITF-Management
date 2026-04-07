<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php
   
    $pageIdentifier = "Invoices";
    include 'header.php';
    include 'dbConnect.php';

    $proformaInvoice = "SELECT id.invoice_id, customer_id, profarma_prefix, invoice_no, invoice_date, state, state_code, net_price, igst, cgst, sgst, tax_type, final_amount, invoice_type, pro_inv_sent_count, ci.customer_name, id, gstin_no FROM invoice_details id left join customer_info ci on id.customer_id = ci.id where id.invoice_type = 'proforma' AND id.is_deleted = 'No' order by invoice_date desc, invoice_no DESC";
//    $proformaInvoice = "SELECT * FROM invoice_details WHERE invoice_type = 'proforma'";
    $statement = $db->prepare($proformaInvoice);
    $statement->execute();
    $proformaDetails = $statement->fetchAll();

    $finalInvoice = "SELECT id.invoice_id, customer_id, final_prefix, final_invoice_no, invoice_date, state, state_code, net_price, igst, cgst, sgst, tax_type, final_amount, invoice_type, final_inv_sent_count, ci.customer_name, id, gstin_no FROM invoice_details id left join customer_info ci on id.customer_id = ci.id where id.invoice_type = 'final' AND id.is_deleted = 'No' order by invoice_date desc, final_invoice_no DESC";
//    $finalInvoice = "SELECT * FROM invoice_details WHERE invoice_type = 'final'";
    $statement = $db->prepare($finalInvoice);
    $statement->execute();
    $finalDetails = $statement->fetchAll();

    $firmDetails = "SELECT * FROM consulting_firm";
    $statement = $db->prepare($firmDetails);
    $statement->execute();
    $firmInfo = $statement->fetch();

    // Batch-load invoice services to avoid per-row queries
    $proformaSvcMap = array();
    if (!empty($proformaDetails)) {
        $proformaIds = array_column($proformaDetails, 'invoice_id');
        $placeholders = implode(',', array_fill(0, count($proformaIds), '?'));
        $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
        $stmtSvc = $db->prepare($svcSql);
        $stmtSvc->execute($proformaIds);
        foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $proformaSvcMap[$row['invoice_ids']][] = $row['service_desc'];
        }
    }

    $finalSvcMap = array();
    if (!empty($finalDetails)) {
        $finalIds = array_column($finalDetails, 'invoice_id');
        $placeholders = implode(',', array_fill(0, count($finalIds), '?'));
        $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
        $stmtSvc = $db->prepare($svcSql);
        $stmtSvc->execute($finalIds);
        foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $finalSvcMap[$row['invoice_ids']][] = $row['service_desc'];
        }
    }

    //query
    ?>
    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }

        #loading-div-background{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        #loading-div{
            width: 300px;
            height: 150px;
            background-color: #fff;
            color:#1468b3;
            border: 5px solid #1468b3;
            text-align: center;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
        }

        .invoice-section-title {
            display: inline-block;
            margin: 0 0 15px;
            padding: 8px 16px;
            font-size: 22px;
            font-weight: 700;
            color: #0d4d8b;
            background: #eaf3ff;
            border-left: 4px solid #0d4d8b;
            border-radius: 4px;
        }

        .invoice-section-divider {
            margin: 25px 0 0;
            padding-top: 25px;
            border-top: 2px solid #d6e4f0;
        }

        .invoice-filter-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin: 10px 0 20px;
            padding: 15px;
            background: #f8fbff;
            border: 1px solid #d6e4f0;
            border-radius: 6px;
        }

        .invoice-filter-item {
            flex: 1 1 220px;
        }

        .invoice-filter-item label {
            display: block;
            margin-bottom: 6px;
            color: #0d4d8b;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .invoice-filter-item input {
            width: 100%;
            height: 38px;
            padding: 8px 12px;
            border: 1px solid #bfd2e6;
            border-radius: 4px;
            box-shadow: none;
        }

        .invoice-filter-item input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 2px rgba(13, 77, 139, 0.08);
        }

        #table_proforma_invoice_filter,
        #table_final_invoice_filter {
            display: none;
        }

        .invoice-mail-confirm {
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f8fbff;
            border: 1px solid #d6e4f0;
            border-radius: 6px;
            color: #0d4d8b;
        }

        .invoice-mail-confirm strong {
            color: #083a69;
        }
    </style>

    <div id="page-content-wrapper">

        <div id="loading-div-background">
            <div id="loading-div" class="modern-spinner">
                <div class="spinner-container">
                    <div class="spinner-circle"></div>
                    <div class="spinner-text">Loading...</div>
                </div>
            </div>
        </div>
        <style>
            #loading-div-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            
            .modern-spinner {
                text-align: center;
            }
            
            .spinner-container {
                display: inline-block;
                padding: 30px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .spinner-circle {
                width: 60px;
                height: 60px;
                margin: 0 auto 20px;
                border: 5px solid #f0f0f0;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                animation: spin 1.5s linear infinite;
            }
            
            .spinner-text {
                color: #2c3e50;
                font-size: 16px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                animation: pulse 1.5s infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            @keyframes pulse {
                0% { opacity: 0.6; }
                50% { opacity: 1; }
                100% { opacity: 0.6; }
            }
        </style>

        <div class="modal fade" id="invoice-msg-sent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Send Proforma Invoice</h4>
                    </div>
                    <form class="customeraddform" id="customeraddform" action="invoicemailsend.php" method="post">
                        <div class="modal-body">
                            <div class="invoice-mail-confirm">
                                Do you want to send this invoice to the client via email?
                                <br>
                                <strong>Note:</strong> A copy of the email and document will also be shared at <strong>certification@aitf.co.in</strong>.
                            </div>
                        </div>
                        <input type="hidden" class="invoiceFirmId" name="invoiceFirmId" value="">
                        <input type="hidden" class="invoiceCustomerId" name="invoiceCustomerId" value="">
                        <input type="hidden" class="invoiceid" name="invoiceid" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-xs btn-danger cancelInvSent">Cancel</button>
                            <button type="submit" class="btn btn-xs btn-success">OK</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="final-invoice-msg-sent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Send Final Invoice</h4>
                    </div>
                    <form class="customeraddform" id="customeraddform" action="mailAttach.php" method="post">
                        <div class="modal-body">
                            <div class="invoice-mail-confirm">
                                Do you want to send this invoice to the client via email?
                                <br>
                                <strong>Note:</strong> A copy of the email and document will also be shared at <strong>certification@aitf.co.in</strong>.
                            </div>
                        </div>
                        <input type="hidden" class="finalInvoiceFirmId" name="invoiceFirmId" value="">
                        <input type="hidden" class="finalInvoiceCustomerId" name="invoiceCustomerId" value="">
                        <input type="hidden" class="finalInvoiceid" name="invoiceid" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-xs btn-danger cancelFinalInvSent">Cancel</button>
                            <button type="submit" class="btn btn-xs btn-success">OK</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">                
                <div class="col-md-12">
                    <?php if (isset($_GET["mailsend"]) && $_GET["mailsend"] == "1") { ?>
                        <div class="alert alert-success">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            Mail has been sent successfully. A copy of the email and document has also been shared at certification@aitf.co.in.
                        </div>
                    <?php } elseif (isset($_GET["mailsend"]) && $_GET["mailsend"] == "0") { ?>
                        <div class="alert alert-danger">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?php echo !empty($_GET["mailerror"]) ? htmlspecialchars(urldecode($_GET["mailerror"]), ENT_QUOTES, 'UTF-8') : 'Invoice mail send failed'; ?>
                        </div>
                    <?php } ?>
                    <div class="container" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="invoice-section-title">Proforma Invoice</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row">
                                    <div class="col-sm-12 main_content" style="">
                                        <div class="invoice-filter-panel">
                                            <div class="invoice-filter-item">
                                                <label for="proformaClientSearch">Client Name</label>
                                                <input type="text" id="proformaClientSearch" placeholder="Search by client name">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable" id="table_proforma_invoice">
                                            <thead>
                                                <tr>
                                                    <th class=""  >
                                                        <small><a>Sr.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>GSTIN No</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Invoice No.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Particulars</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Action</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($proformaDetails as $proforma) {
                                                    $perticuler1 = "";
                                                    if (isset($proformaSvcMap[$proforma['invoice_id']])) {
                                                        $perticuler1 = implode(', ', $proformaSvcMap[$proforma['invoice_id']]);
                                                    }
                                                    $count = $count + 1;
                                                    ?>
                                                    <tr id='addr0' >
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $count ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['customer_name'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['gstin_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['profarma_prefix'] . '-' . $proforma['invoice_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo simpleDateConversion($proforma['invoice_date']) ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['final_amount'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $perticuler1; ?></small>
                                                        </td>
                                                        <?php
                                                        if ($proforma['pro_inv_sent_count'] == '0') {
                                                            $evnsent = "fa-envelope-o";
                                                            $msg = "Send Proforma Invoice";
                                                        } else {
                                                            $evnsent = "fa-envelope";
                                                            $msg = "Re-send Proforma Invoice";
                                                        }
                                                        ?>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <a class="finalInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" data-invoice="<?php echo $proforma['invoice_no'] ?>" title="Make Final Invoice" style="padding: 1px 5px; "><i class="fa fa-plus-circle" style="padding:3px"></i></a>
                                                            <a class="editInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Edit Proforma Invoice" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                            <a class="viewInvoice" data-firm="<?php echo $proforma['firm_id'] ?>" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Proforma Invoice PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <a class="mailProformaInvoice" data-firm="<?php echo $proforma['firm_id'] ?>" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="<?php echo $msg; ?>" style="padding: 1px 5px; "><i class="fa <?php echo $evnsent; ?>" style="padding:3px"></i></a>
                                                            <a class="deleteInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Delete Invoice" style="padding: 1px 5px; "><i class="fa fa-trash" style="padding:3px"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content inset invoice-section-divider">
            <div class="row">
                <div class="col-md-12">
                    <div class="container" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="invoice-section-title">Final Invoice</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row">
                                    <div class="col-sm-12 main_content" style="">
                                        <div class="invoice-filter-panel">
                                            <div class="invoice-filter-item">
                                                <label for="filterFromDate">From Date</label>
                                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="From Date" value="">
                                            </div>
                                            <div class="invoice-filter-item">
                                                <label for="filterToDate">To Date</label>
                                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="To Date" value="">
                                            </div>
                                            <div class="invoice-filter-item">
                                                <label for="finalClientSearch">Client Name</label>
                                                <input type="text" id="finalClientSearch" placeholder="Search by client name">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable" id="table_final_invoice">
                                            <thead>
                                                <tr>
                                                    <th class="">
                                                        <small><a>Sr.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>GSTIN No</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Invoice No.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Particulars</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Action</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($finalDetails as $final) {
                                                    $perticuler2 = "";
                                                    if (isset($finalSvcMap[$final['invoice_id']])) {
                                                        $perticuler2 = implode(', ', $finalSvcMap[$final['invoice_id']]);
                                                    }
                                                    $count = $count + 1;
                                                    ?>
                                                    <tr id='addr0' >
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $count ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["customer_name"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["gstin_no"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final['final_prefix'] . '-' . $final['final_invoice_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><span><?php echo simpleDateConversion($final['invoice_date']) ?>~</span><?php echo simpleDateConversion($final['invoice_date']) ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["final_amount"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $perticuler2; ?></small>
                                                        </td>
                                                        <?php
                                                        if ($final['final_inv_sent_count'] == '0') {
                                                            $finalevnsent = "fa-envelope-o";
                                                            $finalmsg = "Send Final Invoice";
                                                        } else {
                                                            $finalevnsent = "fa-envelope";
                                                            $finalmsg = "Re-send Final Invoice";
                                                        }
                                                        ?>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <a class="editFinalInvoice" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Edit Final Invoice" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                            <a class="viewFinalInvoice" data-firm="<?php echo $final['firm_id'] ?>" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Final Invoice PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <?php if(Utitlity::checkForMainAdmin()){ ?>
                                                            <a class="deleteInvoice" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Delete Final Invoice" style="padding: 1px 5px; "><i class="fa fa-trash" style="padding:3px"></i></a>
                                                            <?php } ?>
                                                            <a class="mailInvoice" data-firm="<?php echo $final['firm_id'] ?>" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="<?php echo $finalmsg; ?>" style="padding: 1px 5px; "><i class="fa <?php echo $finalevnsent; ?>" style="padding:3px"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="delete-details1" style="display: none">
            <div id="loading-div" class="ui-corner-all">
                <span style="font-size:30pt;"><i style="padding:5px;" class="fa fa-warning text-warning"></i></span>
                <div> 
                    <span style="font-size:11pt;padding:10px" class="text-primary meeting-cancel-confirm-message" >Are you sure that you want to delete details?</span>
                </div>
                <div style="padding:3px;">
                    <button title="Click here to delete"  class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                    <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .btn-circle {
        width: 30px;
        height: 30px;
        text-align: center;
        padding: 6px 0;
        font-size: 12px;
        line-height: 1.428571429;
        border-radius: 15px;
    }
    #loading-div-background{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }

    #loading-div{
        width: 300px;
        height: 150px;
        background-color: #fff;
        color:#1468b3;
        border: 5px solid #1468b3;
        text-align: center;
        color: #202020;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -150px;
        margin-top: -100px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        opacity: 1.0;
        behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
    }

    #loading-div-user-info{
        width: 350px;
        background-color: #fff;
        color:#1468b3;
        border: 1px solid gray;
        color: #202020;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -150px;
        margin-top: -100px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        opacity: 1.0;
        behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
    }

    #loading-div-background-resend{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }

    #loading-div-background-meetedit{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }
</style>

<script type="text/javascript">
    function debounce(callback, delay) {
        var debounceTimer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    var proformaTable = $('#table_proforma_invoice').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        "autoWidth": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "5%",
                "targets": 0
            },
            {
                "width": "10%",
                "targets": 1
            },
            {
                "width": "10%",
                "targets": 2
            },
            {
                "width": "10%",
                "targets": 3
            },
            {
                "width": "10%",
                "targets": 4
            },
            {
                "width": "10%",
                "targets": 5
            },
            {
                "width": "25%",
                "targets": 6
            },
        ]
                //        "paging":         false
    });

    var finalTable = $('#table_final_invoice').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        "autoWidth": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "5%",
                "targets": 0
            },
            {
                "width": "10%",
                "targets": 1
            },
            {
                "width": "10%",
                "targets": 2
            },
            {
                "width": "10%",
                "targets": 3
            },
            {
                "width": "10%",
                "targets": 4
            },
            {
                "width": "10%",
                "targets": 5
            },
            {
                "width": "25%",
                "targets": 6
            },
        ]
    });

    $('.filterFromDate, .filterToDate').change(function () {
        var fromDate = $(".filterFromDate").val();
        var toDate = $(".filterToDate").val();
        if (fromDate != "" && toDate != "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date, From Date se chhoti nahi ho sakti");
                $(".filterToDate").val("");
            }
        }
        finalTable.draw();
    });

    function ShowProgressAnimation() {
//        alert("called");
        $("#loading-div-background").show();
//        $("#loading-div-background-resend").show();
    }
    function HideProgressAnimation() {
//        alert("called");
        $("#loading-div-background").hide();
//        $("#loading-div-background-resend").show();
    }

    function compareMinDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit[2] < rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] < rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] < rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    function compareMaxDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        //year comparison
        if (filterDateSplit[2] > rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] > rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] > rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    $.fn.DataTable.ext.search.push(
            function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'table_final_invoice') {
                    return true;
                }
//                var serviceType = $(".service-type").val();
                var fromDate = $(".filterFromDate").val();
                var toDate = $(".filterToDate").val();
//                var isServiceType = false;
                var isfromDate = false;
                var isToDate = false;
                //            alert(serviceType);
                if (fromDate == "" && toDate == "") {
                    return true;
                }
//                if (serviceType != "") {
//                    var servieTypeStr = data[6] || ""; // use data for the age column
////                    alert(servieTypeStr);
//                    if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
//                        //                alert("test");
//                        isServiceType = true;
//                    }
//                } else {
//                    isServiceType = true;
//                }
                var dateValue = data[4] || "";
                var dateSplit = dateValue.split("~");
                //            alert(dateSplit[1]);
                //            alert(fromDate);
                if (fromDate != "") {
//                      alert(dateValue);
                    isfromDate = compareMinDates(fromDate, dateSplit[1]);
                } else {
                    isfromDate = true;
                }
                if (toDate != "") {
                    isToDate = compareMaxDates(toDate, dateSplit[1]);
                } else {
                    isToDate = true;
                }
                finalResult = (isfromDate && isToDate);
                return finalResult;
            }
    );

    $(document).ready(function () {

        var debouncedProformaSearch = debounce(function () {
            proformaTable.column(1).search($('#proformaClientSearch').val()).draw();
        }, 200);

        var debouncedFinalSearch = debounce(function () {
            finalTable.column(1).search($('#finalClientSearch').val()).draw();
        }, 200);

        $('#proformaClientSearch').on('input', debouncedProformaSearch);
        $('#finalClientSearch').on('input', debouncedFinalSearch);

        $('#filterFromDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $("#filterFromDate").keydown(function (e) {
            //            e.preventDefault();
        });

        $('#filterToDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $("#filterToDate").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.viewInvoice').click(function () {
            var customerID = $(this).attr('data-customer');
            var firmId = $(this).attr('data-firm');
            var invoiceid = $(this).attr('data-id');
            // open popup
            var url = 'invoicePdf.php'
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="invoiceconsultingFirmName" value="' + customerID + '" />' +
                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.viewFinalInvoice').click(function () {
            var customerID = $(this).attr('data-customer');
            var firmId = $(this).attr('data-firm');
            var invoiceid = $(this).attr('data-id');
            // open popup
            var url = 'finalInvoicePDF.php'
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="invoiceCustomerId" value="' + customerID + '" />' +
                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $(".mailProformaInvoice").click(function () {
//            var firmanme = $(this).attr('data-firmname');
//            alert($(this).attr('data-firmname'));
            $(".invoiceFirmId").val($(this).attr('data-firm'));
            $(".invoiceCustomerId").val($(this).attr('data-customer'));
            $(".invoiceid").val($(this).attr('data-id'));

            $("#invoice-msg-sent").modal("show");
//            ShowProgressAnimation();
//            var customerID = $(this).attr('data-customer');
//            var firmId = $(this).attr('data-firm');
//            var invoiceid = $(this).attr('data-id');
//            // open popup
//            var url = 'invoicemailsend.php'
//            var form = $('<form action="' + url + '" method="post" >' +
//                    '<input type="hidden" name="invoiceCustomerId" value="' + customerID + '" />' +
//                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
//                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
//                    '</form>');
//            $('body').append(form);
//            form.submit();
        });

        $(".cancelInvSent").click(function () {
            $(".invoiceFirmId").val("");
            $(".invoiceCustomerId").val("");
            $(".invoiceid").val("");

            $("#invoice-msg-sent").modal("hide");
        });

        $(".mailInvoice").click(function () {

            $(".finalInvoiceFirmId").val($(this).attr('data-firm'));
            $(".finalInvoiceCustomerId").val($(this).attr('data-customer'));
            $(".finalInvoiceid").val($(this).attr('data-id'));

            $("#final-invoice-msg-sent").modal("show");

        });

        $(".cancelFinalInvSent").click(function () {
            $(".finalInvoiceFirmId").val("");
            $(".finalInvoiceCustomerId").val("");
            $(".finalInvoiceid").val("");

            $("#final-invoice-msg-sent").modal("hide");
        });

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
//            alert(customerID);
            // open popup
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
        });

        // Enable DataTables with paging to reduce render cost; guard against reinit
        if ($.fn.DataTable) {
            if (!$.fn.DataTable.isDataTable('#table_proforma_invoice')) {
                $("#table_proforma_invoice").DataTable({
                    pageLength: 50,
                    deferRender: true,
                    lengthChange: false
                });
            }
            if (!$.fn.DataTable.isDataTable('#table_final_invoice')) {
                $("#table_final_invoice").DataTable({
                    pageLength: 50,
                    deferRender: true,
                    lengthChange: false
                });
            }
        }

        $('.finalInvoice').click(function () {
            var invoice = $(this).attr('data-id');
            var customer = $(this).attr('data-customer');
            var invoiceNo = $(this).attr('data-invoice');
            var url = 'finalInvoice.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="invoice" value="' + invoice + '" />' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
                    '<input type="hidden" name="invoiceNo" value="' + invoiceNo + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.editFinalInvoice').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    invoice: $(this).attr('data-id'),
                },
                success: function (data)
                {

                    $("input[value='" + data[0]['tax_type'] + "']").prop('checked', true);
                    $('.invoiceconsultingFirmName').val(data[2]['id']);
                    $('.invoiceAddress').val(data[2]['address']);
                    $('.invoiceDt').val(moment(data[0]['invoice_date']).format('DD.MM.YYYY'));
                    $(".invoiceState").val(data[0]['state']);
                    $(".invoiceStateCode").val(data[0]['state_code']);
                    $('.finalNetPrice').val(data[0]['net_price']);
                    $('.serviceTax').val(data[0]['svc_tax_code']);
//                    alert(data[0]['swachh_bharat_cess']);
                    if (data[0]['tax_type'] == "CGST & SGST") {
                        $('.finalcgst').val(data[0]['cgst']);
                        $('.finalsgst').val(data[0]['sgst']);
                        $('.finaligst').val("");
                    } else if (data[0]['tax_type'] == "IGST") {
                        $('.finaligst').val(data[0]['igst']);
                        $('.finalcgst').val("");
                        $('.finalsgst').val("");
                    }
//                    $(".finaltaxtype").val(data[0]['tax_type']);

                    $('.invoiceCustomerId').val(data[0]['customer_id']);
                    $('.invoiceFirmId').val(data[0]['firm_id']);
                    $('.invoiceId').val(data[0]['invoice_id']);
                    $('.finalserviceAmtTd').empty();
                    var includeFinalQtyRate = false;
                    for (var k in data[1]) {
                        if ((data[1][k]['quantity'] !== null && data[1][k]['quantity'] !== undefined && data[1][k]['quantity'] !== '') ||
                                (data[1][k]['rate'] !== null && data[1][k]['rate'] !== undefined && data[1][k]['rate'] !== '')) {
                            includeFinalQtyRate = true;
                            break;
                        }
                    }
                    $('.includeFinalQtyRate').prop('checked', includeFinalQtyRate);
                    var option1 = "";
                    var svcs = '<?php echo $svclist ?>';
                    var services = svcs.split(',');
                    var serviceLen = services.length;
                    var max = (Math.max(serviceLen));
                    for (var key = 0; key < max; key++) {
//                        alert("in");
                        var svcname = key <= serviceLen && services[key] != null && services[key] != "" ? services[key] : "";
                        option1 += '<option value="' + svcname + '">' + svcname + '</option>';
                    }

                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['service_desc'] + '"selected>' + data[1][key]['service_desc'] + '</option>';
                        if (includeFinalQtyRate) {
                            var qtyVal = data[1][key]['quantity'] !== undefined && data[1][key]['quantity'] !== null ? data[1][key]['quantity'] : '';
                            var rateVal = data[1][key]['rate'] !== undefined && data[1][key]['rate'] !== null ? data[1][key]['rate'] : '';
                            $('.finalserviceAmtTd').append('<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-5 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceQty[]" class="form-control form-group finalInvoiceServiceQty" placeholder="Qty" value="' + qtyVal + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceRate[]" class="form-control form-group finalInvoiceServiceRate" placeholder="Rate" value="' + rateVal + '"></div>\n\
                                        <div class = "col-sm-2 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '" readonly> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        } else {
                            $('.finalserviceAmtTd').append('<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-8 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '"> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        }
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#finalinvoiceModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editInvoice').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    invoice: $(this).attr('data-id'),
                },
                success: function (data)
                {
//                    alert(data);
                    $('.invoiceconsultingFirmName').val(data[2]['id']);
                    $('#invoiceAddress').val(data[2]['address']);
                    $('.invoiceDt').val(moment(data[0]['invoice_date']).format('DD.MM.YYYY'));
                    $(".invoiceState").val(data[0]['state']);
                    $(".invoiceStateCode").val(data[0]['state_code']);
                    $('.netPrice').val(data[0]['net_price']);
                    $('.serviceTax').val(data[0]['svc_tax_code']);
//                    alert(data[0]['swachh_bharat_cess']);
                    if (data[0]['tax_type'] == "CGST & SGST") {
                        $('.cgst').val(data[0]['cgst']);
                        $('.sgst').val(data[0]['sgst']);
                        $('.igst').val("");
                    } else if (data[0]['tax_type'] == "IGST") {
                        $('.igst').val(data[0]['igst']);
                        $('.cgst').val("");
                        $('.sgst').val("");
                    }
//                    $(".taxtype").val(data[0]['tax_type']);
                    $("input[value='" + data[0]['tax_type'] + "']").prop('checked', true);
                    $('.invoiceCustomerId').val(data[0]['customer_id']);
                    $('.invoiceFirmId').val(data[0]['firm_id']);
                    $('.invoiceId').val(data[0]['invoice_id']);
                    $('.serviceAmtTd').empty();
                    var includeQtyRate = false;
                    for (var k in data[1]) {
                        if ((data[1][k]['quantity'] !== null && data[1][k]['quantity'] !== undefined && data[1][k]['quantity'] !== '') ||
                                (data[1][k]['rate'] !== null && data[1][k]['rate'] !== undefined && data[1][k]['rate'] !== '')) {
                            includeQtyRate = true;
                            break;
                        }
                    }
                    $('.includeQtyRate').prop('checked', includeQtyRate);
                    var option1 = "";
                    var svcs = '<?php echo $svclist ?>';
                    var services = svcs.split(',');
                    var serviceLen = services.length;
                    var max = (Math.max(serviceLen));
                    for (var key = 0; key < max; key++) {
//                        alert("in");
                        var svcname = key <= serviceLen && services[key] != null && services[key] != "" ? services[key] : "";
                        option1 += '<option value="' + svcname + '">' + svcname + '</option>';
                    }

                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['service_desc'] + '"selected>' + data[1][key]['service_desc'] + '</option>';
                        if (includeQtyRate) {
                            var qtyVal = data[1][key]['quantity'] !== undefined && data[1][key]['quantity'] !== null ? data[1][key]['quantity'] : '';
                            var rateVal = data[1][key]['rate'] !== undefined && data[1][key]['rate'] !== null ? data[1][key]['rate'] : '';
                            $('.serviceAmtTd').append('<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-5 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceQty[]" class="form-control form-group invoiceServiceQty" placeholder="Qty" value="' + qtyVal + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceRate[]" class="form-control form-group invoiceServiceRate" placeholder="Rate" value="' + rateVal + '"></div>\n\
                                        <div class = "col-sm-2 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style = "padding-top:10px;" >\n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "invoiceServiceAmt[]" class ="form-control form-group invoiceServiceAmt" id = "invoiceService" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '" readonly> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        } else {
                            $('.serviceAmtTd').append('<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-8 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "invoiceServiceAmt[]" class ="form-control form-group invoiceServiceAmt" id = "invoiceService" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '"> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        }
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#invoiceModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });


        $("body").on("click", ".deleteInvoice", function () {
            var id = $(this).attr('data-id');

            $('#delete-details1').show();
            $('.delete-confirm-ok').click(function () {

                var url = 'deleteFinalInvoice.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="finalId" value="' + id + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            });
            $('.delete-cancel').click(function () {
                $('#delete-details1').hide();
            });
        });



    });
</script>
