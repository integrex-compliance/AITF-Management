<?php

    $server = "localhost";
    $username = "aitf-mcl";
    $password='aitfmcl';
    $dbname = "aitf_web";
    
    $conn = mysqli_connect($server,$username,$password,$dbname);
    
    
    
    $action_id = $_GET['id'];
    ?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
</head>
<body>
    
    
    <?php
    
    
    
    $query = "SELECT * FROM `ca_service_dates` WHERE `action_id`=$action_id";
    
    $res = mysqli_query($conn , $query);
    
    while($rows = mysqli_fetch_array($res)){
        
        ?>
    <div class="container col-md-6 mt-5">
        <div class="row">
            
        <form action="updatedates.php" method = "POST">
            <div class="form mb-3">
                <input type="date" class="form-control" name="due_date" placeholder="due_date" value=<?php echo $rows['due_date'] ; ?> >
            </div>
            
             <div class="form mb-3">
                <input type="date" class="form-control" name="sent_date" placeholder="sent_date" value=<?php echo $rows['sent_date'] ; ?> >
            </div>
        
            <div class="form mb-3">
                <input type="text" class="form-control" placeholder="actions" value=<?php echo $rows['action'] ; ?> readonly>
            </div>
            
            <div class="form mb-3">
                <input type="hidden" class="form-control" name="action_id" placeholder="actions" value=<?php echo $action_id ; ?>>
            </div>
        
            <div class="form mb-3">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        
        </form>
        <?php 
    }
    ?>
    
    
   
    
        
        </div>
    </div>
</body>
</html>