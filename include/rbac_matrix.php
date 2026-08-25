<?php

function aitf_has_access($module) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $role = $_SESSION['role'] ?? '';
    
    // Top Mgmt has access to everything
    if (trim($role) === 'Top Mgmt') {
        return true;
    }
    
    // Matrix Rules mapping
    $access_matrix = [
        'Directors Dashboard' => [],
        'IAF Dashboard' => ['Admin', 'Finance'],
        'Application' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Marketing Head', 'Marketing Executive'],
        'Client List' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Marketing' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Payment Due' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Statement' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit Planning' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Master Creator' => [],
        'TDS Deducted' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Intimation Due' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Invoices' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Bills' => ['Admin', 'Finance'],
        'Creditors' => ['Admin', 'Finance'],
        'Quotations' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Agreements' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit List' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Add User' => [],
        'Recycle Cust' => [],
        'Cert Issue List' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit Performance' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Client Analysis' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'App Review Objective' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Doc Upload Status' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit Data' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Go AI' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        
        // Modules not explicitly in the matrix but normally accessed by staff
        'Admin Dashboard' => ['Admin', 'Office Staff-A', 'Office Staff-B'],
        'Mailbox' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Job Applications' => ['Admin', 'Office Staff-A', 'Office Staff-B'],
    ];

    if (!array_key_exists($module, $access_matrix)) {
        return false;
    }

    return in_array(trim($role), $access_matrix[$module], true);
}
?>
