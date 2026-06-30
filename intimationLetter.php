32...<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Due letter";
    include 'header.php';
    include 'dbConnect.php';
    include_once './include/certification_cycle_helper.php';

    $excludeWithdrawn = " AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')";
    $latestCertificationService = aitfLatestCertificationServiceSubquery('ci');
    $notice = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Suspension Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($notice);
    $statement->execute();
    $noticeDue = $statement->fetchAll();

    $reminder = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Suspend Letter') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($reminder);
    $statement->execute();
    $reminderDue = $statement->fetchAll();

	$suspension = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Withdrawal Reminder' or action = 'Withdrawal Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($suspension);
    $statement->execute();
    $suspensionDue = $statement->fetchAll();
    
    $withdrawn = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Withdrawal Letter' or action = 'Withdraw Letter') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($withdrawn);
    $statement->execute();
    $withdrawnDue = $statement->fetchAll();

	$expireReminder1 = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = '1st Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expireReminder1);
    $statement->execute();
    $expireReminderDue1 = $statement->fetchAll();
	
	$expireReminder2 = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = '2nd Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expireReminder2);
    $statement->execute();
    $expireReminderDue2 = $statement->fetchAll();
	
    $expired = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and action = 'Expire Letter' and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expired);
    $statement->execute();
    $expireDue = $statement->fetchAll();
    ?>

    <style>
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
    </style>

    <div id="page-content-wrapper">

        <div id="sendStatusToast" class="alert" style="display:none; position: fixed; top: 20px; right: 20px; z-index: 1051; min-width: 260px;">
        </div>

        <div class="modal fade" id="sendConfirmModal" tabindex="-1" role="dialog" aria-labelledby="sendConfirmTitle">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#053c6d; color: #fff;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="sendConfirmTitle">Send Intimation Letter</h4>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:15px; color:#333; margin-bottom: 0;">Are you sure you want to send this letter to the client?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmSendBtn">Yes, Send</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="loading-div-background" >
            <div id="loading-div" class="ui-corner-all text-center">
                <h1><i class="fa fa-spinner fa-spin" ></i></h1>
                <h4 class="meeting-loading-info"> Please wait...</h4>
            </div>
        </div>

        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" >
                            <h4 style="color: #053c6d; text-align: center">Intimation Due</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Suspention Reminder</h4>
                            </div>
                            <?php foreach ($noticeDue as $notiInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $notiInfo['action'] ?>' data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right;">PDF <i class="fa fa-file-pdf-o"></i></a>
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Send" data-letterType='<?php echo $notiInfo['action'] ?>' data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $notiInfo['id'] ?>" title="Action item description" > <?php echo $notiInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($notiInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Suspend Letter</h4>
                            </div>
                            <?php foreach ($reminderDue as $reminderInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info"     title="Generate" data-letterType='<?php echo $reminderInfo['action'] ?>' data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" data-servicetype="<?php echo $reminderInfo["service_type"] ?>" style="float: right; ">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $reminderInfo['action'] ?>' data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" data-servicetype="<?php echo $reminderInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $reminderInfo['id'] ?>" title="Action item description" > <?php echo $reminderInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date"  id="tourActionItemDate" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($reminderInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Withdrawal Reminder</h4>
                            </div>
                            <?php foreach ($suspensionDue as $suspensionInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate"     data-letterType='<?php echo $suspensionInfo['action'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $suspensionInfo['action'] ?>' data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $suspensionInfo['id'] ?>" title="Action item description" > <?php echo $suspensionInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($suspensionInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Withdrawn Letter</h4>
                            </div>
                            <?php foreach ($withdrawnDue as $withdrawnInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $withdrawnInfo['action'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-servicetype="<?php echo $withdrawnInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $withdrawnInfo['action'] ?>' data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" data-servicetype="<?php echo $withdrawnInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Delete" data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $withdrawnInfo['id'] ?>" title="Action item description" > <?php echo $withdrawnInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($withdrawnInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                </div>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-primary" >
						<h4 style="color: #053c6d; text-align: center">For RC</h4>
					</div>
				</div>
			</div>
            <div class="row">
				<div class="col-md-6 ">
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">1st Reminder</h4>
						</div>
						<?php foreach ($expireReminderDue1 as $expireReminderInfo1) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo1['action'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo1['action'] ?>' data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireReminderInfo1['id'] ?>" title="Action item description" > <?php echo $expireReminderInfo1['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireReminderInfo1['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">2nd Reminder</h4>
						</div>
						<?php foreach ($expireReminderDue2 as $expireReminderInfo2) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo2['action'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo2['action'] ?>' data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireReminderInfo2['id'] ?>" title="Action item description" > <?php echo $expireReminderInfo2['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireReminderInfo2['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="col-md-6 ">
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">Expire </h4>
						</div>
						<?php foreach ($expireDue as $expireInfo) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireInfo['action'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-servicetype="<?php echo $expireInfo["service_type"] ?>" data-servicetype="<?php echo $expireInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireInfo['action'] ?>' data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" data-servicetype="<?php echo $expireInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireInfo['id'] ?>" title="Action item description" > <?php echo $expireInfo['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireInfo['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
            </div>
            </div>
        </div> 
    </div>
</div>

<script type="text/javascript" >
    function ShowProgressAnimation() {
        $("#loading-div-background").fadeIn(150);
    }
    function HideProgressAnimation() {
        $("#loading-div-background").fadeOut(150);
    }

    function showToast(message, isSuccess) {
        var $toast = $('#sendStatusToast');
        $toast.removeClass('alert-success alert-danger');
        $toast.addClass(isSuccess ? 'alert-success' : 'alert-danger');
        $toast.html(message);
        $toast.fadeIn(200);
        setTimeout(function(){
            $toast.fadeOut(200);
        }, 3500);
    }

	function GenerateOrPreviewPDF(customer, service, action, servicetype, letterType, pageAction){
		var url = 'IntimationDueLetters.php';
		var form = $('<form action="' + url + '" method="post" target="_blank">' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="action" value="' + action + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'<input type="hidden" name="lettertype" value="' + letterType + '" />' +
				'<input type="hidden" name="SourcePage" value="intimationLetter.php" />' +
				'<input type="hidden" name="pageAction" value="' + pageAction + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	}
	
	$('.PreviewPDF').click(function () {
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var action = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var lettertype = $(this).attr("data-letterType");
		
		GenerateOrPreviewPDF(customer,service,action,servicetype,lettertype, "PreviewPDF");
	});
	
	$('.SendMailToCust').click(function () {
		pendingSendPayload = {
			customer: $(this).attr('data-customer'),
			service: $(this).attr('data-service'),
			action: $(this).attr('data-action'),
			servicetype: $(this).attr('data-servicetype'),
			lettertype: $(this).attr('data-letterType')
		};
		$('#sendConfirmModal').modal('show');
	});

	$('#confirmSendBtn').click(function(){
		if(!pendingSendPayload){ return; }
		$('#sendConfirmModal').modal('hide');
		ShowProgressAnimation();
		var payload = $.extend({}, pendingSendPayload, {
			SourcePage: 'intimationLetter.php',
			pageAction: 'SendMail'
		});
		$.ajax({
			url: 'IntimationDueLetters.php',
			type: 'POST',
			data: payload,
			dataType: 'json'
		}).done(function(res){
			HideProgressAnimation();
			if(res && res.success){
				showToast('Letter sent successfully.', true);
				setTimeout(function(){ location.reload(); }, 800);
			} else {
				var msg = (res && res.message) ? res.message : 'Unable to send mail. Please try again.';
				showToast(msg, false);
			}
		}).fail(function(xhr){
			HideProgressAnimation();
			var msg = 'Unable to send mail. Please try again.';
			if(xhr && xhr.responseText){
				msg = xhr.responseText;
			}
			showToast(msg, false);
		});
	});
	
	$('.SendSuspensionReminderPDF').click(function () {
		ShowProgressAnimation();
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var acction = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var url = 'TestMailSend.php';
		var form = $('<form action="' + url + '" method="post" >' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="acction" value="' + acction + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	});
	
	$('.notificationSuspensionSend').click(function () {
		ShowProgressAnimation();
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var acction = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var url = 'suspentionMail.php';
		var form = $('<form action="' + url + '" method="post" >' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="acction" value="' + acction + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	});
		
    $(document).ready(function () {
        $(".deleteLetter").click(function () {
            var rowid = $(this).attr("data-action");
            var url = 'addComments.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="rowid" value="' + rowid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });

        $('.customerName').click(function () {
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
        
        $('.notificationReminderSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            if (servicetype == "SA-II") {
                var url = 'sendReminderDueNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="action" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            } else {
                var url = 'suspensionDueNotificationSend.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            }
        });

        $('.notificationWithdrawnSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            if (servicetype == "SA-II") {
                var url = 'sendWithdrawnDueSaiiNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            } else {
                var url = 'sendWithdrawnDueNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            }
        });
        
        $('.expireGenerateSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
                var url = 'sendWithdrawnDueSaiiNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            
        });

    });
</script>
