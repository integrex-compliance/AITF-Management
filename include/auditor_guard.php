<?php
/**
 * Strict deny-by-default access control for the Auditor role.
 *
 * Included from dbConnect.php so that EVERY page and AJAX endpoint that talks
 * to the database is covered automatically. When the logged-in user has the
 * role "Auditor", only an explicit whitelist of scripts is allowed:
 *   - the auditor portal pages,
 *   - the customer account page (which itself limits data to the auditor's
 *     own clients/services),
 *   - the audit-workflow form/upload endpoints for the services the auditor
 *     actually performed (ownership verified per request).
 * Everything else (masterCreator, adminDashboard, homePage, payments,
 * invoices, quotations, delete/edit endpoints, ...) is blocked server-side:
 * pages get redirected to auditorDashboard.php and AJAX calls get a 403.
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/auditor_portal_helper.php';

if (!function_exists('aitf_auditor_request_is_ajax')) {
    function aitf_auditor_request_is_ajax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        return strpos($accept, 'application/json') !== false;
    }
}

if (!function_exists('aitf_auditor_deny_request')) {
    function aitf_auditor_deny_request($reason = 'access_denied')
    {
        if (aitf_auditor_request_is_ajax() || $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(403);
            }
            echo json_encode(array(
                'status' => 'error',
                'code' => 403,
                'message' => 'Access denied for auditor account.',
                'reason' => $reason
            ));
            exit;
        }

        if (!headers_sent()) {
            header('Location: auditorDashboard.php?denied=1');
        } else {
            echo '<script>window.location.href = "auditorDashboard.php?denied=1";</script>';
        }
        exit;
    }
}

if (!function_exists('aitf_auditor_allowed_scripts')) {
    function aitf_auditor_allowed_scripts()
    {
        // Lowercase basenames. Everything NOT listed here is denied for auditors.
        return array(
            // Auth / session
            'login.php', 'logout.php', 'loginselection.php', 'createaccount.php',
            'changepassword.php', 'index.php',
            // Auditor portal pages and conditionally allowed pages (controlled by header.php)
            'auditordashboard.php', 'auditorcustomeraccount.php', 'homepage.php', 'invoicepage.php', 'billspage.php',
            'invoicepdf.php', 'finalinvoicepdf.php', 'finalinvoice.php', 'invoicemailsend.php', 'mailattach.php',
            'exporthomereport.php', 'exporthomereportexcel.php', 'emailhomereport.php', 'updatecustomer.php',
            // Client account page (internally restricted to the auditor's services)
            'customeraccount.php',
            // Service document upload endpoints (ownership enforced below)
            'servicetypeca.php', 'servicetypesai.php', 'servicetypesaii.php', 'servicetyperc.php',
            // Audit-workflow forms the auditor is allowed to fill
            'auditapplication.php', 'getauditapplication.php',
            'addcontractreview.php', 'updatecontractreview.php', 'contractdetails.php',
            'addauditnotification.php', 'addauditnotificationii.php', 'getnotificationinfo.php',
            'addreviewreport.php', 'reviewreportdata.php',
            'addschedulestage.php', 'addscheduleiistage.php',
            'addassessmentplanning.php', 'getassessmentplanning.php',
            'addservelance1.php', 'addservelance2.php',
            'addcontrolformat1.php', 'addcontrolformat2.php',
            'addcustomersurvey.php', 'csa.php', 'addannexiimodel.php',
            'getinfoupdationreview.php', 'saveinfoupdationreview.php',
            'sendstagenotificationmail.php',
            // Read-only lookups used by the allowed forms
            'getclientinfo.php', 'checkcustomer.php', 'checkservicetype.php',
            'mastergetdata.php', 'fetch_nace_classes.php', 'get_allotment_only.php',
            'getcacertificatenumber.php', 'getcertificatenumbers.php',
            'ai_document_review_api.php'
        );
    }
}

if (!function_exists('aitf_auditor_load_allowed_services')) {
    /**
     * Cached per-request map of customer_id => allowed service types for the
     * current auditor session.
     */
    function aitf_auditor_load_allowed_services($db)
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        if (!$db instanceof PDO) {
            return array();
        }
        $auditorId = (int)($_SESSION['auditor_id'] ?? 0);
        $auditorName = trim((string)($_SESSION['username'] ?? ''));
        $cache = aitf_fetch_auditor_client_services($db, $auditorId, $auditorName);
        return $cache;
    }
}

if (!function_exists('aitf_auditor_assert_customer_access')) {
    function aitf_auditor_assert_customer_access($db, $customerId)
    {
        $customerId = (int)$customerId;
        $services = aitf_auditor_load_allowed_services($db);
        if ($customerId <= 0 || empty($services[$customerId])) {
            aitf_auditor_deny_request('customer_not_assigned');
        }
    }
}

if (!function_exists('aitf_auditor_assert_service_access')) {
    function aitf_auditor_assert_service_access($db, $customerId, $serviceType)
    {
        $customerId = (int)$customerId;
        $services = aitf_auditor_load_allowed_services($db);
        if ($customerId <= 0 || !aitf_auditor_can_access_service($services, $customerId, $serviceType)) {
            aitf_auditor_deny_request('service_not_assigned');
        }
    }
}

if (!function_exists('aitf_auditor_enforce_access')) {
    function aitf_auditor_enforce_access($db = null)
    {
        // Never applies to CLI/cron executions.
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            aitf_start_session();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Auditor') {
            return;
        }

        $script = strtolower(basename((string)($_SERVER['SCRIPT_NAME'] ?? '')));
        if ($script === '') {
            aitf_auditor_deny_request('unknown_script');
        }

        if (!in_array($script, aitf_auditor_allowed_scripts(), true)) {
            aitf_auditor_deny_request('script_not_allowed');
        }

        // A valid auditor session must carry auditor_id.
        if (empty($_SESSION['auditor_id'])
            && !in_array($script, array('login.php', 'logout.php', 'loginselection.php', 'createaccount.php', 'index.php'), true)) {
            aitf_auditor_deny_request('missing_auditor_id');
        }

        // Ownership checks for document upload endpoints: the auditor may only
        // touch the service type they audited, for their own clients, and may
        // never create a brand-new service row.
        $uploadEndpointTypes = array(
            'servicetypeca.php' => 'CA',
            'servicetypesai.php' => 'SA-I',
            'servicetypesaii.php' => 'SA-II',
            'servicetyperc.php' => 'RC'
        );

        if (isset($uploadEndpointTypes[$script])) {
            $customerId = (int)($_POST['customerid'] ?? $_POST['dataid'] ?? $_POST['customerId'] ?? 0);
            $dataService = trim((string)($_POST['dataService'] ?? ''));
            if ($customerId <= 0 || $dataService === '') {
                // Blocks the "create new service" branch of these endpoints.
                aitf_auditor_deny_request('service_creation_blocked');
            }
            aitf_auditor_assert_service_access($db, $customerId, $uploadEndpointTypes[$script]);
            return;
        }

        // Generic customer-scoped requests: whenever the request references a
        // customer, it must be one of the auditor's own clients.
        $customerParams = array('customerid', 'customerId', 'customer_id', 'customer', 'cid', 'clientId', 'client_id');
        foreach ($customerParams as $param) {
            $value = $_POST[$param] ?? $_GET[$param] ?? null;
            if ($value !== null && $value !== '' && ctype_digit((string)$value)) {
                aitf_auditor_assert_customer_access($db, (int)$value);
                break;
            }
        }
    }
}
