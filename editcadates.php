<?php
    include_once './dbConnect.php';

    if (!$db) {
        die('Database connection failed. Please check local MySQL settings in dbConnect.php.');
    }

    $action_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$action_id) {
        die('Invalid action id.');
    }

    $stmt = $db->prepare("SELECT * FROM `ca_service_dates` WHERE `action_id` = :action_id");
    $stmt->execute(array(':action_id' => $action_id));
    $dateRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    
    <div class="container col-md-6 mt-5">
        <div class="row">
        <?php if (empty($dateRows)) { ?>
            <div class="alert alert-warning">No dates found for this action.</div>
        <?php } ?>
        <?php foreach ($dateRows as $rows) { ?>
            
        <form action="updatedates.php" method = "POST">
            <div class="form mb-3">
                <input type="date" class="form-control" name="due_date" placeholder="due_date" value="<?php echo htmlspecialchars($rows['due_date'], ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
            
             <div class="form mb-3">
                <input type="date" class="form-control" name="sent_date" placeholder="sent_date" value="<?php echo htmlspecialchars($rows['sent_date'], ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
        
            <div class="form mb-3">
                <input type="text" class="form-control" placeholder="actions" value="<?php echo htmlspecialchars($rows['action'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            
            <div class="form mb-3">
                <input type="hidden" class="form-control" name="action_id" placeholder="actions" value="<?php echo htmlspecialchars($action_id, ENT_QUOTES, 'UTF-8'); ?>">
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
