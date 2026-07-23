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
            
        <form action="updatedates.php" method="POST">
            <div class="form mb-3">
                <label class="form-label fw-bold">Proposed Date</label>
                <input type="date" class="form-control" name="due_date" placeholder="due_date" value="<?php echo htmlspecialchars($rows['due_date'], ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
            
             <div class="form mb-3">
                <label class="form-label fw-bold">Sent Date</label>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <input type="date" id="sent_date_input" class="form-control" name="sent_date" placeholder="sent_date" value="<?php echo ($rows['sent_date'] == '0000-00-00') ? '' : htmlspecialchars($rows['sent_date'], ENT_QUOTES, 'UTF-8'); ?>" >
                    <div class="form-check text-nowrap m-0">
                        <input class="form-check-input" type="checkbox" id="sent_date_na" name="sent_date_na" value="1" <?php echo ($rows['sent_date'] == '0000-00-00') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="sent_date_na">Not applicable</label>
                    </div>
                </div>
            </div>
        
            <div class="form mb-3">
                <label class="form-label fw-bold">Action Name</label>
                <input type="text" class="form-control" placeholder="actions" value="<?php echo htmlspecialchars($rows['action'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            
            <div class="form mb-3">
                <input type="hidden" class="form-control" name="action_id" placeholder="actions" value="<?php echo htmlspecialchars($action_id, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        
            <div class="form mb-3">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var naCheckbox = document.getElementById('sent_date_na');
                var sentDateInput = document.getElementById('sent_date_input');
                
                function toggleSentDate() {
                    if (naCheckbox.checked) {
                        sentDateInput.value = '';
                        sentDateInput.readOnly = true;
                    } else {
                        sentDateInput.readOnly = false;
                    }
                }
                
                naCheckbox.addEventListener('change', toggleSentDate);
                toggleSentDate();
            });
        </script>
        <?php 
        }
        ?>
        </div>
    </div>
</body>
</html>
