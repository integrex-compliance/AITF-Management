<?php

    $server = "localhost";
    $username = "aitf-mcl";
    $password='aitfmcl';
    $dbname = "aitf_web";
    
    $conn = mysqli_connect($server,$username,$password,$dbname);
    
    
    
    // $action_id = $_GET['id'];
    
    
    echo $due_date = $_POST['due_date'];
    
    $dd = strtotime($due_date);
    
    echo $newdate = date('Y-m-d',$dd);
    
  
    
    
    
    $sent_date = $_POST['sent_date'];
    
    $action_id = $_POST['action_id'];
    
    // to find service id
    
    
    $query = "SELECT * FROM `ca_service_dates` WHERE `action_id`=$action_id";
    
    $res = mysqli_query($conn , $query);
    
    $row = mysqli_fetch_assoc($res);
    
    $service_id = $row['service_id'];
    
    
    // to find customer id
    
    
    $query = "SELECT * FROM `service` WHERE `service_id`=$service_id";
    
    $res = mysqli_query($conn , $query);
    
    $row = mysqli_fetch_assoc($res);
    
    $cust_id = $row['customer_id'];
    
    
    
    
    
    
    
    
    // Validation: Check if the action is a reminder (1st Reminder, 2nd Reminder, Expire Letter)
    // and if so, validate that the due_date is greater than CA CDC date
    $actionQuery = "SELECT action FROM `ca_service_dates` WHERE `action_id`=$action_id";
    $actionRes = mysqli_query($conn, $actionQuery);
    $actionRow = mysqli_fetch_assoc($actionRes);
    $action = $actionRow['action'];
    
    // Check if this is a reminder action for RC service
    if (in_array($action, ['1st Reminder', '2nd Reminder', 'Expire Letter'])) {
        // Get CA CDC date for this customer
        $caCdcQuery = "SELECT crr.Date FROM cdc_report_review crr 
                       INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                       INNER JOIN service s ON ca.service = s.service_id 
                       WHERE s.customer_id = $cust_id AND s.service_type = 'CA' 
                       ORDER BY crr.Date DESC LIMIT 1";
        $caCdcRes = mysqli_query($conn, $caCdcQuery);
        
        if ($caCdcRes && mysqli_num_rows($caCdcRes) > 0) {
            $caCdcRow = mysqli_fetch_assoc($caCdcRes);
            $caCdcDate = $caCdcRow['Date'];
            
            // Validate that reminder date is greater than CA CDC date
            if ($newdate <= $caCdcDate) {
                echo "<script>
                alert ('Error: " . $action . " date must be greater than CA CDC date (" . date('d.m.Y', strtotime($caCdcDate)) . ")');
                window.location.replace('customerAccount.php?customerid=$cust_id');
                </script>";
                exit;
            }
        }
    }
    
    $query = "UPDATE `ca_service_dates` SET `due_date`='$newdate',`sent_date`='$sent_date' WHERE `action_id`=$action_id ";
    
    $res = mysqli_query($conn,$query);
    
    if(!$res){
        echo "<script>
        alert ('Something Went Wrong');
        window.location.replace('customerAccount.php?customerid=$cust_id');
        
        </script>";
    }
    
    else{
       echo "<script>
        alert ('Dates Updated Successfully!!');
        window.location.replace('customerAccount.php?customerid=$cust_id');
        
        </script>";
    }
    
    
    
    
?>