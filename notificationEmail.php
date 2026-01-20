<?php



include 'dbConnect.php';
require_once 'testemail.php';

$notice = "SELECT ci.id, customer_name, email_id, address, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and action = '1st Remainder' and due_date <= now() and status != 'sent'";
$statement = $db->prepare($notice);
$statement->execute();
$noticeDue = $statement->fetchAll();

foreach ($noticeDue as $notice) {
    $mailSender = new AITFServiceEmailSender();

    $body = '<html>
                    <body>
                        <pre>Ref: AITF-MCl/QMS/SELF/002                                         Date: 01-01-2016</pre>
    <p style="text-align: justify;text-justify: inter-word;"><font size="2">
    <span>TO: </span>
    <br><br>
    <span><b> '.$notice['customer_name'].'</b> </span>
    <br>
   <span>'.$notice['address'].'</span>
    <br>
    <br>
    <span> Kind Attn: Mrs.Shailaja Jagadale(proprietor)</span>
    <br>
    <br>
    <span><b> Subject:</b> First Surillance Audit Due</span>
    <br>
    <br>
    <span> Dear Sir/Madam, </span>
    <br>
   <br><span>1)Client details (as on our records)
         <br>- Certification no.:14 02 Validity: From 22.03.2014 To 21-02-2017
         <br>- Audits done:initial (certification) audit 14-03-2014
         <br>During last assessment of Quality Management System it is observed that number of employee, including contract
         workers of your organization</span>
   <br><span>2)You are required to intimate to us in case of any organization covered or change in the manpower,address,scope
   (reduction or extension sought),or any major changes in the organization since from last audit conducted by AITF-MCL.
   With submission of Information updating form F 7.31</span> 
   <br><span>3)<u>We have earlier sent to you the following documents: </u><br>
             - Copy of the contract signed by you and AITF-MCL unanimously.<br>
             - Report of Last audit along with the nonconformity reports (NCRs), if any, and other encolsures mentioned there in.<br>
             - Guidelines on the use of longos.<br>
             Please inform us in case you have not received any of the above documents.</span>
    <br><span>Please ensure that the above documents are retained by you for future reference.</span> 
    <br><span>4)You are required to continue to maintain the quality management system as per ISO 9001:2006 standard requirements including documentation,records keeping,effective conduct 
    of Internal audits and management reviews as scheduled, monitoring of quality objectives and demonstration of continual improvement,etc.</span>
   <br><span>5)Please ensure that the logos,If used,are asper the guildelines issued by AITF-MCL</span>
   <br><span>6)Your first Surveillance audit falls due on 13-01-2015
         <br>- Kindly let us know  the convenient dates for the audits within 30 days from the due date so 
            can plan the audit accordingly.
          <br>-Please note that the expenses of sureillance audit are 9000+S.tax as per signed agreement
          during initial certification.</span>
    <br><span>7)Failure to successfully completion of surveillance assment as scheduled may call for
                suspension of certification. Ref. Agreeement.</span>      
   <br><br><span>Kindly contact us if any more clarification. or information needed.</span>
    <br>
    <br>
    <br><span>Thanks &regards,</span>
    <br><span>  Sign</span>
    <br>
    <br>Your faithfully,(certification manager)
    <br>AITF Management Certification Ltd.
    
</font></p>
                    </body>
                </html>';

    $subject = "First Surveillance Audit Due";

    $mailSender->sendEmail($notice['email_id'], "", $body, $subject);
    echo "success";
}
