<?php

function aitf_has_access($module) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $role = $_SESSION['role'] ?? '';
    
    // Top Mgmt has access to everything
    if (in_array(trim($role), ['Top Mgmt', 'Director', 'Main Admin', 'top mgmt', 'Management'], true)) {
        return true;
    }
    
    // Matrix Rules mapping
    $access_matrix = [
        'Directors Dashboard' => [],
        'IAF Dashboard' => ['Admin', 'Finance'],
        'Application' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Marketing Head', 'Marketing Executive'],
        'Client List' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Marketing' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Received Payment' => ['Admin', 'Finance', 'Management'],
        'Received Payments' => ['Admin', 'Finance', 'Management'],
        'Payment Due' => ['Finance', 'finance'],
        'Payment' => ['Finance', 'finance'],
        'Finance Page' => ['Finance', 'finance'],
        'Statement' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit Planning' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Master Creator' => [],
        'TDS Deducted' => ['Finance', 'finance'],
        'TDS list' => ['Finance', 'finance'],
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
        'KPI Analysis' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'KPI ANALYSIS' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Audit Data' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Go AI' => ['Admin', 'Office Staff-A', 'Finance', 'Marketing Head', 'Marketing Executive'],
        
        // Modules not explicitly in the matrix but normally accessed by staff
        'Admin Dashboard' => ['Top Mgmt'],
        'Management Dashboard' => ['Top Mgmt'],
        'Top Mgmt Dashboard' => ['Top Mgmt'],
        'MGMT Dashboard' => ['Top Mgmt'],
        'Mgmt Dashboard' => ['Top Mgmt'],
        'Mailbox' => ['Admin', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Marketing Head', 'Marketing Executive'],
        'Job Applications' => ['Admin', 'Office Staff-A', 'Office Staff-B'],
        'Guidelines' => ['Admin', 'Office Staff', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Report Reviewer', 'Marketing', 'Marketing Head', 'Marketing Executive', 'Working Project'],
        'My Dashboard' => ['Admin', 'Office Staff', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Report Reviewer', 'Marketing', 'Marketing Head', 'Marketing Executive', 'Working Project', 'Management'],
        'MY DASHBOARD' => ['Admin', 'Office Staff', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Report Reviewer', 'Marketing', 'Marketing Head', 'Marketing Executive', 'Working Project', 'Management'],
        'Task Manager' => ['Admin', 'Office Staff', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Report Reviewer', 'Marketing', 'Marketing Head', 'Marketing Executive', 'Working Project', 'Management'],
        'TASK MANAGER' => ['Admin', 'Office Staff', 'Office Staff-A', 'Office Staff-B', 'Finance', 'Auditor', 'auditors', 'Application Reviewer', 'Application Reviewers', 'Report Reviewer', 'Marketing', 'Marketing Head', 'Marketing Executive', 'Working Project', 'Management'],
    ];

    if (!array_key_exists($module, $access_matrix)) {
        return false;
    }

    return in_array(trim($role), $access_matrix[$module], true);
}
?>
