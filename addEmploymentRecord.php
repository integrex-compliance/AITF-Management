<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
include 'utility.php';

if (!Utitlity::checkForDirector()) {
    header("Location: login.php?reason=login_required");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM employment_records WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['director_notice'] = "Employment record deleted successfully.";
    } elseif (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = trim($_POST['name']);
        $age = (int)$_POST['age'];
        $email = trim($_POST['email']);
        $mobile = trim($_POST['mobile']);
        $ctc = trim($_POST['ctc']);
        $job_profile = trim($_POST['job_profile']);
        $emp_id = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;

        if ($emp_id > 0) {
            $stmt = $db->prepare("UPDATE employment_records SET name = :name, age = :age, email = :email, mobile = :mobile, ctc = :ctc, job_profile = :job_profile WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':ctc', $ctc);
            $stmt->bindParam(':job_profile', $job_profile);
            $stmt->bindParam(':id', $emp_id);
            $stmt->execute();
            $_SESSION['director_notice'] = "Employment record updated successfully.";
        } else {
            $stmt = $db->prepare("INSERT INTO employment_records (name, age, email, mobile, ctc, job_profile) VALUES (:name, :age, :email, :mobile, :ctc, :job_profile)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':ctc', $ctc);
            $stmt->bindParam(':job_profile', $job_profile);
            $stmt->execute();
            $_SESSION['director_notice'] = "Employment record added successfully.";
        }
    }
}

header("Location: directorSpace.php");
exit;
