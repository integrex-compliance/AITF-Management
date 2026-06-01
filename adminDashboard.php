<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Admin Dashboard";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    function dashMoney($value) {
        return 'Rs. ' . number_format((float)$value, 2);
    }

    function dashInt($value) {
        return number_format((int)$value);
    }

    function dashPercent($part, $total) {
        $total = (float)$total;
        if ($total <= 0) {
            return '0%';
        }
        return number_format(((float)$part / $total) * 100, 1) . '%';
    }

    function dashDate($value) {
        if (!isset($value) || $value === '' || $value === '0000-00-00') {
            return '-';
        }
        return simpleDateConversion(substr($value, 0, 10));
    }

    function dashFetchOne($db, $sql, $params = array()) {
        if (!$db) {
            return array();
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : array();
    }

    function dashFetchAll($db, $sql, $params = array()) {
        if (!$db) {
            return array();
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function dashAmountExpr($field) {
        return "CAST(REPLACE(REPLACE(REPLACE($field, ',', ''), 'Rs.', ''), '₹', '') AS DECIMAL(12,2))";
    }

    $dashboardType = isset($_GET['dashboard_type']) ? $_GET['dashboard_type'] : 'business';
    $allowedDashboards = array('business', 'invoice', 'letters', 'payments', 'clients');
    if (!in_array($dashboardType, $allowedDashboards)) {
        $dashboardType = 'business';
    }

    $invoiceType = isset($_GET['invoice_type']) ? $_GET['invoice_type'] : 'all';
    $allowedInvoiceTypes = array('all', 'proforma', 'final');
    if (!in_array($invoiceType, $allowedInvoiceTypes)) {
        $invoiceType = 'all';
    }

    $invoiceStatus = isset($_GET['invoice_status']) ? $_GET['invoice_status'] : 'all';
    $allowedInvoiceStatuses = array('all', 'Rcv', 'NotRcv');
    if (!in_array($invoiceStatus, $allowedInvoiceStatuses)) {
        $invoiceStatus = 'all';
    }

    $letterAction = isset($_GET['letter_action']) ? $_GET['letter_action'] : 'all';
    $letterStatus = isset($_GET['letter_status']) ? $_GET['letter_status'] : 'all';
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

    $invoiceWhere = "WHERE is_deleted = 'No'";
    $invoiceParams = array();
    if ($invoiceType !== 'all') {
        $invoiceWhere .= " AND invoice_type = ?";
        $invoiceParams[] = $invoiceType;
    }
    if ($invoiceStatus !== 'all') {
        $invoiceWhere .= " AND invoice_status = ?";
        $invoiceParams[] = $invoiceStatus;
    }
    if ($dateFrom !== '') {
        $invoiceWhere .= " AND invoice_date >= ?";
        $invoiceParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $invoiceWhere .= " AND invoice_date <= ?";
        $invoiceParams[] = $dateTo;
    }

    $recentInvoiceWhere = "WHERE inv.is_deleted = 'No'";
    $recentInvoiceParams = array();
    if ($invoiceType !== 'all') {
        $recentInvoiceWhere .= " AND inv.invoice_type = ?";
        $recentInvoiceParams[] = $invoiceType;
    }
    if ($invoiceStatus !== 'all') {
        $recentInvoiceWhere .= " AND inv.invoice_status = ?";
        $recentInvoiceParams[] = $invoiceStatus;
    }
    if ($dateFrom !== '') {
        $recentInvoiceWhere .= " AND inv.invoice_date >= ?";
        $recentInvoiceParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $recentInvoiceWhere .= " AND inv.invoice_date <= ?";
        $recentInvoiceParams[] = $dateTo;
    }

    $letterWhere = "WHERE svcdt.is_deleted = 'N'";
    $letterParams = array();
    if ($letterAction !== 'all') {
        $letterWhere .= " AND svcdt.action = ?";
        $letterParams[] = $letterAction;
    }
    if ($letterStatus === 'sent') {
        $letterWhere .= " AND svcdt.status = 'sent'";
    } else if ($letterStatus === 'pending') {
        $letterWhere .= " AND svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE()";
    } else if ($letterStatus === 'upcoming') {
        $letterWhere .= " AND svcdt.status <> 'sent' AND svcdt.due_date > CURDATE()";
    }
    if ($dateFrom !== '') {
        $letterWhere .= " AND svcdt.due_date >= ?";
        $letterParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $letterWhere .= " AND svcdt.due_date <= ?";
        $letterParams[] = $dateTo;
    }

    $moneyExpr = dashAmountExpr('final_amount');
    $invoiceTotals = dashFetchOne($db, "SELECT COUNT(*) total_count, COALESCE(SUM($moneyExpr),0) total_amount, COALESCE(SUM(CASE WHEN invoice_status = 'Rcv' THEN $moneyExpr ELSE 0 END),0) received_amount, COALESCE(SUM(CASE WHEN invoice_status <> 'Rcv' THEN $moneyExpr ELSE 0 END),0) pending_amount FROM invoice_details $invoiceWhere", $invoiceParams);
    $invoiceTypeRows = dashFetchAll($db, "SELECT invoice_type label, COUNT(*) total, COALESCE(SUM($moneyExpr),0) amount FROM invoice_details $invoiceWhere GROUP BY invoice_type ORDER BY invoice_type", $invoiceParams);
    $invoiceTrendRows = dashFetchAll($db, "SELECT DATE_FORMAT(invoice_date, '%b %Y') label, COUNT(*) total, COALESCE(SUM($moneyExpr),0) amount FROM invoice_details $invoiceWhere GROUP BY YEAR(invoice_date), MONTH(invoice_date) ORDER BY YEAR(invoice_date), MONTH(invoice_date) DESC LIMIT 12", $invoiceParams);
    $recentInvoices = dashFetchAll($db, "SELECT inv.invoice_id, inv.customer_id, inv.invoice_type, inv.invoice_status, inv.invoice_date, inv.final_amount, ci.customer_name FROM invoice_details inv LEFT JOIN customer_info ci ON inv.customer_id = ci.id $recentInvoiceWhere ORDER BY inv.invoice_date DESC, inv.invoice_id DESC LIMIT 8", $recentInvoiceParams);
    $invoiceTrendOrdered = array_reverse($invoiceTrendRows);
    $invoiceTrendFirst = !empty($invoiceTrendOrdered) ? reset($invoiceTrendOrdered) : array('amount' => 0, 'total' => 0, 'label' => '-');
    $invoiceTrendLast = !empty($invoiceTrendOrdered) ? end($invoiceTrendOrdered) : array('amount' => 0, 'total' => 0, 'label' => '-');
    $invoiceTrendDiff = (float)(isset($invoiceTrendLast['amount']) ? $invoiceTrendLast['amount'] : 0) - (float)(isset($invoiceTrendFirst['amount']) ? $invoiceTrendFirst['amount'] : 0);
    $invoiceTrendDirection = $invoiceTrendDiff > 0 ? 'up' : ($invoiceTrendDiff < 0 ? 'down' : 'flat');

    $letterTotals = dashFetchOne($db, "SELECT COUNT(*) total_letters, SUM(CASE WHEN svcdt.status = 'sent' THEN 1 ELSE 0 END) sent_letters, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE() THEN 1 ELSE 0 END) due_letters, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date > CURDATE() THEN 1 ELSE 0 END) upcoming_letters FROM ca_service_dates svcdt $letterWhere", $letterParams);
    $letterActionRows = dashFetchAll($db, "SELECT svcdt.action label, COUNT(*) total, SUM(CASE WHEN svcdt.status = 'sent' THEN 1 ELSE 0 END) sent, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE() THEN 1 ELSE 0 END) due FROM ca_service_dates svcdt $letterWhere GROUP BY svcdt.action ORDER BY total DESC", $letterParams);
    $recentLetters = dashFetchAll($db, "SELECT ci.id customer_id, ci.customer_name, svc.service_type, svcdt.action, svcdt.due_date, svcdt.sent_date, svcdt.status FROM ca_service_dates svcdt LEFT JOIN service svc ON svcdt.service_id = svc.service_id LEFT JOIN customer_info ci ON svc.customer_id = ci.id $letterWhere ORDER BY svcdt.due_date DESC, svcdt.action_id DESC LIMIT 8", $letterParams);

    $paymentTotals = dashFetchOne($db, "SELECT COUNT(*) rows_count, COALESCE(SUM(CAST(REPLACE(balance, ',', '') AS DECIMAL(12,2))),0) pending_amount, COALESCE(SUM(CAST(REPLACE(payment_received, ',', '') AS DECIMAL(12,2))),0) received_amount, COALESCE(SUM(CAST(REPLACE(amount_decided, ',', '') AS DECIMAL(12,2))),0) decided_amount FROM payment_system WHERE service_type <> 'TDS'");
    $paymentServiceRows = dashFetchAll($db, "SELECT service_type label, COALESCE(SUM(CAST(REPLACE(balance, ',', '') AS DECIMAL(12,2))),0) amount, COUNT(*) total FROM payment_system WHERE service_type <> 'TDS' GROUP BY service_type ORDER BY amount DESC LIMIT 8");
    $recentPayments = dashFetchAll($db, "SELECT ps.payment_id, ps.customer_id, ci.customer_name, ps.service_type, ps.amount_decided, ps.payment_received, ps.balance, ps.payment_date, ps.cheque_details FROM payment_system ps LEFT JOIN customer_info ci ON ps.customer_id = ci.id WHERE ps.service_type <> 'TDS' ORDER BY ps.payment_date DESC, ps.payment_id DESC LIMIT 10");

    $clientTotals = dashFetchOne($db, "SELECT COUNT(*) total_clients, SUM(CASE WHEN audit_status = 'Application canceled' THEN 1 ELSE 0 END) canceled_clients, SUM(CASE WHEN audit_status = 'Withdrawn' THEN 1 ELSE 0 END) withdrawn_clients, SUM(CASE WHEN is_deleted = 'YES' THEN 1 ELSE 0 END) deleted_clients FROM customer_info");
    $clientStatusRows = dashFetchAll($db, "SELECT IF(audit_status = '', 'No Status', audit_status) label, COUNT(*) total FROM customer_info WHERE is_deleted <> 'YES' GROUP BY audit_status ORDER BY total DESC LIMIT 8");
    $clientServiceRows = dashFetchAll($db, "SELECT service_type label, COUNT(*) total FROM service GROUP BY service_type ORDER BY total DESC LIMIT 8");
    $applicationTotals = dashFetchOne($db, "SELECT COUNT(*) online_total, SUM(CASE WHEN read_flag = 0 THEN 1 ELSE 0 END) unread_total FROM online_audit_applications");
    $auditApplicationTotals = dashFetchOne($db, "SELECT COUNT(*) audit_total, SUM(CASE WHEN review_date IS NOT NULL THEN 1 ELSE 0 END) reviewed_total FROM audit_application");
    $recentClients = dashFetchAll($db, "SELECT ci.id, ci.customer_name, ci.unique_id, ci.audit_status, ci.next_service, ci.contact_person, ci.contact, ci.email_id, COUNT(svc.service_id) service_count, COALESCE(SUM(CAST(REPLACE(ps.balance, ',', '') AS DECIMAL(12,2))),0) pending_amount FROM customer_info ci LEFT JOIN service svc ON ci.id = svc.customer_id LEFT JOIN payment_system ps ON ci.id = ps.customer_id AND ps.service_type <> 'TDS' WHERE ci.is_deleted <> 'YES' GROUP BY ci.id ORDER BY ci.id DESC LIMIT 10");
    $recentOnlineApplications = dashFetchAll($db, "SELECT id, org_name, contact_person, phone, email, standard_applied, created_at, read_flag FROM online_audit_applications ORDER BY created_at DESC LIMIT 8");

    $letterActionOptions = dashFetchAll($db, "SELECT DISTINCT action FROM ca_service_dates WHERE is_deleted = 'N' ORDER BY action");
    $totalInvoiceAmount = isset($invoiceTotals['total_amount']) ? $invoiceTotals['total_amount'] : 0;
    $receivedInvoiceAmount = isset($invoiceTotals['received_amount']) ? $invoiceTotals['received_amount'] : 0;
    $pendingInvoiceAmount = isset($invoiceTotals['pending_amount']) ? $invoiceTotals['pending_amount'] : 0;
    $totalClients = isset($clientTotals['total_clients']) ? $clientTotals['total_clients'] : 0;
    $activeClients = $totalClients - (isset($clientTotals['deleted_clients']) ? $clientTotals['deleted_clients'] : 0);
    $cardSets = array(
        'business' => array(
            array('label' => 'Invoice Value', 'value' => dashMoney($totalInvoiceAmount), 'meta' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0) . ' invoices generated', 'icon' => 'fa-inr', 'raw' => $totalInvoiceAmount, 'format' => 'money'),
            array('label' => 'Payment Pending', 'value' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0), 'meta' => 'Collection pipeline', 'icon' => 'fa-credit-card', 'raw' => isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, 'format' => 'money'),
            array('label' => 'Due Letters', 'value' => dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0), 'meta' => dashInt(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0) . ' sent', 'icon' => 'fa-envelope-o', 'raw' => isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0, 'format' => 'int'),
            array('label' => 'Active Clients', 'value' => dashInt($activeClients), 'meta' => dashInt($totalClients) . ' total records', 'icon' => 'fa-building', 'raw' => $activeClients, 'format' => 'int')
        ),
        'invoice' => array(
            array('label' => 'Invoice Value', 'value' => dashMoney($totalInvoiceAmount), 'meta' => 'Filtered total invoice amount', 'icon' => 'fa-inr', 'raw' => $totalInvoiceAmount, 'format' => 'money'),
            array('label' => 'Received Value', 'value' => dashMoney($receivedInvoiceAmount), 'meta' => dashPercent($receivedInvoiceAmount, $totalInvoiceAmount) . ' recovery', 'icon' => 'fa-check-circle', 'raw' => $receivedInvoiceAmount, 'format' => 'money'),
            array('label' => 'Pending Value', 'value' => dashMoney($pendingInvoiceAmount), 'meta' => dashPercent($pendingInvoiceAmount, $totalInvoiceAmount) . ' still open', 'icon' => 'fa-clock-o', 'raw' => $pendingInvoiceAmount, 'format' => 'money'),
            array('label' => 'Invoices Count', 'value' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0), 'meta' => 'Generated in selected range', 'icon' => 'fa-file-text-o', 'raw' => isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0, 'format' => 'int')
        ),
        'letters' => array(
            array('label' => 'Total Letters', 'value' => dashInt(isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0), 'meta' => 'All filtered letters', 'icon' => 'fa-envelope', 'raw' => isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0, 'format' => 'int'),
            array('label' => 'Due Pending', 'value' => dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0), 'meta' => 'Due date crossed', 'icon' => 'fa-exclamation-circle', 'raw' => isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0, 'format' => 'int'),
            array('label' => 'Sent Letters', 'value' => dashInt(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0), 'meta' => dashPercent(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0) . ' completed', 'icon' => 'fa-send', 'raw' => isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, 'format' => 'int'),
            array('label' => 'Upcoming', 'value' => dashInt(isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0), 'meta' => 'Not due yet', 'icon' => 'fa-calendar', 'raw' => isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0, 'format' => 'int')
        ),
        'payments' => array(
            array('label' => 'Payment Decided', 'value' => dashMoney(isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0), 'meta' => 'Total committed fee', 'icon' => 'fa-calculator', 'raw' => isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0, 'format' => 'money'),
            array('label' => 'Payment Received', 'value' => dashMoney(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0), 'meta' => dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0) . ' collected', 'icon' => 'fa-bank', 'raw' => isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, 'format' => 'money'),
            array('label' => 'Payment Pending', 'value' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0), 'meta' => 'Balance to collect', 'icon' => 'fa-hourglass-half', 'raw' => isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, 'format' => 'money'),
            array('label' => 'Open Payment Rows', 'value' => dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0), 'meta' => 'Service payment entries', 'icon' => 'fa-list', 'raw' => isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0, 'format' => 'int')
        ),
        'clients' => array(
            array('label' => 'Active Clients', 'value' => dashInt($activeClients), 'meta' => 'Visible client accounts', 'icon' => 'fa-building', 'raw' => $activeClients, 'format' => 'int'),
            array('label' => 'Online Applications', 'value' => dashInt(isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0), 'meta' => dashInt(isset($applicationTotals['unread_total']) ? $applicationTotals['unread_total'] : 0) . ' unread', 'icon' => 'fa-inbox', 'raw' => isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0, 'format' => 'int'),
            array('label' => 'Audit Applications', 'value' => dashInt(isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0), 'meta' => dashInt(isset($auditApplicationTotals['reviewed_total']) ? $auditApplicationTotals['reviewed_total'] : 0) . ' reviewed', 'icon' => 'fa-clipboard', 'raw' => isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0, 'format' => 'int'),
            array('label' => 'Withdrawn Clients', 'value' => dashInt(isset($clientTotals['withdrawn_clients']) ? $clientTotals['withdrawn_clients'] : 0), 'meta' => 'Customer lifecycle risk', 'icon' => 'fa-sign-out', 'raw' => isset($clientTotals['withdrawn_clients']) ? $clientTotals['withdrawn_clients'] : 0, 'format' => 'int')
        )
    );
    $dashboardCards = isset($cardSets[$dashboardType]) ? $cardSets[$dashboardType] : $cardSets['business'];
    $insightSets = array(
        'business' => array(
            array('title' => 'Live Business Snapshot', 'text' => 'Invoices, payments, clients and letters are calculated from database rows at page load.'),
            array('title' => 'Collection Health', 'text' => dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0) . ' of decided payment is marked received.'),
            array('title' => 'Action Load', 'text' => dashInt(isset($applicationTotals['unread_total']) ? $applicationTotals['unread_total'] : 0) . ' unread applications and ' . dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0) . ' due letters need action.')
        ),
        'invoice' => array(
            array('title' => 'Invoice Recovery', 'text' => dashPercent($receivedInvoiceAmount, $totalInvoiceAmount) . ' of filtered invoice value is received.'),
            array('title' => 'Open Invoice Value', 'text' => dashMoney($pendingInvoiceAmount) . ' is still marked not received.'),
            array('title' => 'Invoice Volume', 'text' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0) . ' invoices match the selected filters.')
        ),
        'letters' => array(
            array('title' => 'Letter Completion', 'text' => dashPercent(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0) . ' filtered letters are sent.'),
            array('title' => 'Due Queue', 'text' => dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0) . ' letters are due and pending.'),
            array('title' => 'Upcoming Work', 'text' => dashInt(isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0) . ' letters are scheduled for future dates.')
        ),
        'payments' => array(
            array('title' => 'Payment Collection', 'text' => dashMoney(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0) . ' has been received against decided payment.'),
            array('title' => 'Pending Collection', 'text' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0) . ' is open in payment rows.'),
            array('title' => 'Service Exposure', 'text' => dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0) . ' service payment entries are being tracked.')
        ),
        'clients' => array(
            array('title' => 'Client Base', 'text' => dashInt($activeClients) . ' active client accounts are available to open.'),
            array('title' => 'Applications', 'text' => dashInt(isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0) . ' online applications and ' . dashInt(isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0) . ' audit applications exist.'),
            array('title' => 'Client Risk', 'text' => dashInt(isset($clientTotals['withdrawn_clients']) ? $clientTotals['withdrawn_clients'] : 0) . ' withdrawn and ' . dashInt(isset($clientTotals['canceled_clients']) ? $clientTotals['canceled_clients'] : 0) . ' canceled clients are visible.')
        )
    );
    $selectedInsights = isset($insightSets[$dashboardType]) ? $insightSets[$dashboardType] : $insightSets['business'];

    $chartData = array(
        'invoiceTrend' => $invoiceTrendOrdered,
        'invoiceTypes' => $invoiceTypeRows,
        'letterActions' => $letterActionRows,
        'paymentServices' => $paymentServiceRows,
        'clientStatuses' => $clientStatusRows,
        'clientServices' => $clientServiceRows,
        'letterTotals' => array(
            array('label' => 'Due', 'total' => (int)(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0)),
            array('label' => 'Sent', 'total' => (int)(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0)),
            array('label' => 'Upcoming', 'total' => (int)(isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0))
        )
    );
    ?>

    <style>
        .admin-dash-wrap {
            background:
                radial-gradient(circle at 12% 8%, rgba(38, 121, 162, 0.12), transparent 28%),
                radial-gradient(circle at 92% 18%, rgba(31, 157, 104, 0.10), transparent 26%),
                linear-gradient(180deg, #eef3f8 0%, #f8fafc 42%, #f4f7fb 100%);
            min-height: 100vh;
            padding-bottom: 48px;
            color: #132238;
        }
        .admin-dashboard { max-width: 1320px; margin: 0 auto; padding: 30px 20px 64px; }
        .dash-hero {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(198, 211, 228, 0.9);
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 22px 55px rgba(25, 42, 70, 0.11);
            position: relative;
            overflow: hidden;
        }
        .dash-hero:before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(21, 94, 149, 0.09), transparent 34%),
                linear-gradient(280deg, rgba(31, 157, 104, 0.09), transparent 36%);
            pointer-events: none;
        }
        .dash-hero:after { content: ""; position: absolute; right: 24px; bottom: 15px; width: 260px; height: 2px; background: repeating-linear-gradient(90deg, #155e95 0 8px, transparent 8px 17px); opacity: .22; }
        .dash-hero > * { position: relative; z-index: 1; }
        .dash-title { margin: 0; color: #0f1f33; font-weight: 900; font-size: 32px; letter-spacing: 0; line-height: 1.15; }
        .dash-subtitle { color: #5c6c83; margin-top: 9px; font-size: 14px; font-weight: 500; }
        .live-pulse { display: inline-flex; align-items: center; gap: 7px; padding: 8px 13px; border-radius: 999px; background: #e7f8ef; color: #12633a; font-weight: 800; font-size: 12px; border: 1px solid #c8ecd8; box-shadow: 0 8px 20px rgba(31, 157, 104, 0.12); }
        .live-pulse span { width: 8px; height: 8px; border-radius: 50%; background: #1fc16b; box-shadow: 0 0 0 0 rgba(31,193,107,.55); animation: dashPulse 1.6s infinite; }
        @keyframes dashPulse { 70% { box-shadow: 0 0 0 9px rgba(31,193,107,0); } 100% { box-shadow: 0 0 0 0 rgba(31,193,107,0); } }
        .dash-filter { margin-top: 22px; display: grid; grid-template-columns: repeat(6, minmax(132px, 1fr)); gap: 12px; align-items: end; padding: 15px; background: rgba(255,255,255,.74); border: 1px solid rgba(209, 219, 233, .9); border-radius: 9px; box-shadow: inset 0 1px 0 rgba(255,255,255,.75); }
        .dash-filter label { display: block; color: #52647d; font-size: 10px; text-transform: uppercase; font-weight: 900; margin-bottom: 7px; letter-spacing: 0; }
        .dash-filter .form-control { height: 40px; border: 1px solid #c7d3e2; border-radius: 7px; box-shadow: none; color: #17233a; background: #fff; font-weight: 700; font-size: 12px; transition: border-color .18s ease, box-shadow .18s ease; }
        .dash-filter .form-control:focus { border-color: #155e95; box-shadow: 0 0 0 3px rgba(21, 94, 149, .12); }
        .dash-btn { height: 40px; border-radius: 7px; border: 0; background: linear-gradient(135deg, #155e95, #1f7a8c); color: #fff; font-weight: 900; padding: 0 18px; box-shadow: 0 12px 24px rgba(21, 94, 149, .22); transition: transform .18s ease, box-shadow .18s ease; }
        .dash-btn:hover { transform: translateY(-1px); box-shadow: 0 16px 28px rgba(21, 94, 149, .26); color: #fff; }
        .dash-card-grid { display: grid; grid-template-columns: repeat(4, minmax(190px, 1fr)); gap: 16px; margin-top: 18px; }
        .dash-card { background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%); border: 1px solid rgba(209, 219, 233, .95); border-radius: 10px; padding: 19px; box-shadow: 0 16px 34px rgba(25, 42, 70, 0.08); position: relative; overflow: hidden; min-height: 148px; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
        .dash-card:hover { transform: translateY(-3px); box-shadow: 0 22px 44px rgba(25, 42, 70, 0.12); border-color: #b8cadf; }
        .dash-card:before { content: ""; position: absolute; left: 0; top: 0; width: 5px; height: 100%; background: #155e95; }
        .dash-card:after { content: ""; position: absolute; right: -32px; top: -32px; width: 92px; height: 92px; border-radius: 50%; background: rgba(21, 94, 149, .07); }
        .dash-card:nth-child(2):before { background: #1f9d68; }
        .dash-card:nth-child(3):before { background: #ef7b45; }
        .dash-card:nth-child(4):before { background: #9b6bdf; }
        .dash-card .icon { position: absolute; right: 17px; top: 18px; color: #7e96b1; font-size: 24px; z-index: 1; }
        .dash-card .label { color: #61738d; display: block; padding: 0; font-size: 11px; text-align: left; text-transform: uppercase; font-weight: 900; background: transparent; }
        .dash-card .value { color: #0f1f33; font-size: 27px; font-weight: 900; margin-top: 9px; word-break: break-word; line-height: 1.15; }
        .dash-card .meta { color: #74839a; margin-top: 7px; font-size: 12px; font-weight: 600; padding-right: 26px; }
        .dash-spark { position: absolute; left: 19px; right: 19px; bottom: 13px; height: 19px; display: flex; align-items: end; gap: 4px; opacity: .85; }
        .dash-spark span { flex: 1; min-width: 4px; border-radius: 3px 3px 0 0; background: #dce8f7; }
        .dash-spark span:nth-child(2n) { background: #cdebdc; }
        .dash-spark span:nth-child(3n) { background: #f6d8c5; }
        .insight-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 16px; }
        .insight-box { background: linear-gradient(145deg, #ffffff 0%, #f9fcff 100%); border: 1px solid rgba(202, 215, 231, .98); border-radius: 10px; padding: 18px 18px 17px 58px; color: #334963; box-shadow: 0 16px 32px rgba(25,42,70,.075); line-height: 1.48; position: relative; overflow: hidden; min-height: 104px; font-weight: 650; }
        .insight-box:before { content: ""; position: absolute; left: 18px; top: 18px; width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #155e95, #1f7a8c); box-shadow: 0 10px 18px rgba(21, 94, 149, .22); }
        .insight-box:after { content: ""; position: absolute; left: 27px; top: 27px; width: 10px; height: 10px; border-radius: 50%; background: #fff; box-shadow: 0 0 0 4px rgba(255,255,255,.28); }
        .insight-box:nth-child(2):before { background: linear-gradient(135deg, #1f9d68, #67b26f); }
        .insight-box:nth-child(3):before { background: linear-gradient(135deg, #ef7b45, #d14f62); }
        .insight-box strong { display: block; color: #0f1f33; font-size: 15px; margin-bottom: 6px; font-weight: 900; }
        .insight-box strong + br { display: none; }
        .dash-section-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(330px, 1fr); gap: 16px; margin-top: 16px; }
        .dash-panel { background: rgba(255,255,255,.96); border: 1px solid rgba(209, 219, 233, .95); border-radius: 10px; padding: 18px; box-shadow: 0 16px 34px rgba(25, 42, 70, 0.075); overflow: hidden; }
        .dash-panel h3 { margin: 0 0 15px; color: #0f1f33; font-size: 16px; font-weight: 900; display: flex; align-items: center; gap: 8px; }
        .dash-panel h3:before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: #1f9d68; box-shadow: 0 0 0 5px rgba(31,157,104,.12); }
        .dash-chart { width: 100%; height: 320px; display: block; }
        .dash-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
        .dash-table th { color: #52647d; text-transform: uppercase; font-size: 10px; border-bottom: 1px solid #dfe7f2; padding: 10px 9px; background: #f6f9fd; font-weight: 900; position: sticky; top: 0; z-index: 1; }
        .dash-table th:first-child { border-radius: 7px 0 0 0; }
        .dash-table th:last-child { border-radius: 0 7px 0 0; }
        .dash-table td { color: #24364f; border-bottom: 1px solid #edf2f8; padding: 11px 9px; vertical-align: top; font-weight: 600; }
        .dash-table small { color: #71829a; font-weight: 600; }
        .dash-table tr.clickable-row { cursor: pointer; }
        .dash-table tr.clickable-row:hover td { background: #f4f9ff; color: #0f1f33; }
        .dash-pill { display: inline-block; border-radius: 999px; padding: 4px 9px; background: #edf6ff; color: #155e95; font-weight: 900; font-size: 10px; border: 1px solid #d8eaff; text-transform: uppercase; }
        .dash-kicker { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 10px; color: #155e95; font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .dash-kicker:before { content: ""; width: 22px; height: 2px; border-radius: 3px; background: #ef7b45; }
        .dash-module-badge { display: inline-block; margin-left: 8px; padding: 4px 9px; border-radius: 999px; color: #fff; background: #155e95; font-size: 11px; font-weight: 900; vertical-align: middle; }
        .dash-card:nth-child(2) .dash-module-badge { background: #1f9d68; }
        .letter-rank-list { display: flex; flex-direction: column; gap: 11px; max-height: 320px; overflow-y: auto; padding-right: 4px; }
        .letter-rank-item { border: 1px solid #e3ebf5; border-radius: 9px; padding: 11px 12px; background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%); }
        .letter-rank-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; color: #0f1f33; font-weight: 900; font-size: 12px; }
        .letter-rank-title { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .letter-rank-count { flex: 0 0 auto; color: #155e95; background: #edf6ff; border: 1px solid #d8eaff; border-radius: 999px; padding: 3px 8px; font-size: 11px; }
        .letter-rank-track { height: 8px; border-radius: 999px; background: #edf2f8; overflow: hidden; margin: 9px 0 7px; }
        .letter-rank-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #155e95, #1f9d68); }
        .letter-rank-meta { display: flex; justify-content: space-between; color: #6a7b92; font-size: 11px; font-weight: 700; }
        .letter-rank-empty { color: #6a7b92; font-weight: 700; padding: 20px 0; text-align: center; }
        .trend-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px; }
        .trend-chip { border: 1px solid #e3ebf5; border-radius: 9px; background: #f8fbff; padding: 10px 11px; }
        .trend-chip span { display: block; color: #6a7b92; font-size: 10px; font-weight: 900; text-transform: uppercase; margin-bottom: 4px; }
        .trend-chip strong { display: block; color: #0f1f33; font-size: 14px; font-weight: 900; }
        .trend-chip.up strong { color: #16703c; }
        .trend-chip.down strong { color: #c2410c; }
        .trend-chip.flat strong { color: #155e95; }
        .hidden-dashboard { display: none; }
        @media (max-width: 1100px) { .dash-filter { grid-template-columns: repeat(3, 1fr); } .dash-card-grid { grid-template-columns: repeat(2, 1fr); } .dash-section-grid, .insight-strip { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .admin-dashboard { padding: 16px 10px 40px; } .dash-filter, .dash-card-grid, .trend-summary { grid-template-columns: 1fr; } .dash-title { font-size: 22px; } .dash-chart { height: 260px; } }
    </style>

    <div id="page-content-wrapper" class="admin-dash-wrap">
        <div class="page-content inset">
            <div class="admin-dashboard">
                <div class="dash-hero">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="dash-kicker">Super Admin Control Room</div>
                            <h1 class="dash-title">Admin Dashboard <span class="dash-module-badge"><?php echo ucfirst($dashboardType); ?></span></h1>
                            <div class="dash-subtitle">Business insights, invoices, due letters, payments and client health in one place.</div>
                        </div>
                        <div class="col-sm-4 text-right"><div class="live-pulse"><span></span> Live from database</div></div>
                    </div>
                    <form class="dash-filter" method="get" action="adminDashboard.php">
                        <div>
                            <label>Dashboard</label>
                            <select name="dashboard_type" class="form-control">
                                <option value="business" <?php echo $dashboardType === 'business' ? 'selected' : ''; ?>>Business Insights</option>
                                <option value="invoice" <?php echo $dashboardType === 'invoice' ? 'selected' : ''; ?>>Invoice</option>
                                <option value="letters" <?php echo $dashboardType === 'letters' ? 'selected' : ''; ?>>Due Letters</option>
                                <option value="payments" <?php echo $dashboardType === 'payments' ? 'selected' : ''; ?>>Payments</option>
                                <option value="clients" <?php echo $dashboardType === 'clients' ? 'selected' : ''; ?>>Clients</option>
                            </select>
                        </div>
                        <div>
                            <label>Invoice Type</label>
                            <select name="invoice_type" class="form-control">
                                <option value="all" <?php echo $invoiceType === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="proforma" <?php echo $invoiceType === 'proforma' ? 'selected' : ''; ?>>Proforma</option>
                                <option value="final" <?php echo $invoiceType === 'final' ? 'selected' : ''; ?>>Final</option>
                            </select>
                        </div>
                        <div>
                            <label>Invoice Status</label>
                            <select name="invoice_status" class="form-control">
                                <option value="all" <?php echo $invoiceStatus === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="Rcv" <?php echo $invoiceStatus === 'Rcv' ? 'selected' : ''; ?>>Received</option>
                                <option value="NotRcv" <?php echo $invoiceStatus === 'NotRcv' ? 'selected' : ''; ?>>Not Received</option>
                            </select>
                        </div>
                        <div>
                            <label>Letter</label>
                            <select name="letter_action" class="form-control">
                                <option value="all" <?php echo $letterAction === 'all' ? 'selected' : ''; ?>>All Letters</option>
                                <?php foreach ($letterActionOptions as $actionRow) { ?>
                                    <option value="<?php echo htmlspecialchars($actionRow['action']); ?>" <?php echo $letterAction === $actionRow['action'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($actionRow['action']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>Letter Status</label>
                            <select name="letter_status" class="form-control">
                                <option value="all" <?php echo $letterStatus === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="pending" <?php echo $letterStatus === 'pending' ? 'selected' : ''; ?>>Due Pending</option>
                                <option value="sent" <?php echo $letterStatus === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                <option value="upcoming" <?php echo $letterStatus === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            </select>
                        </div>
                        <div>
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div>
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <div><button type="submit" class="dash-btn"><i class="fa fa-filter"></i> Apply</button></div>
                    </form>
                </div>

                <div class="dash-card-grid">
                    <?php foreach ($dashboardCards as $cardIndex => $card) { ?>
                        <div class="dash-card">
                            <i class="fa <?php echo $card['icon']; ?> icon"></i>
                            <span class="label"><?php echo $card['label']; ?></span>
                            <div class="value dash-count" data-raw="<?php echo htmlspecialchars($card['raw']); ?>" data-format="<?php echo htmlspecialchars($card['format']); ?>"><?php echo $card['value']; ?></div>
                            <div class="meta"><?php echo $card['meta']; ?></div>
                            <div class="dash-spark">
                                <?php for ($spark = 0; $spark < 9; $spark++) { ?>
                                    <span style="height: <?php echo 5 + (($spark + $cardIndex * 2) % 7) * 2; ?>px"></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="insight-strip">
                    <?php foreach ($selectedInsights as $insight) { ?>
                        <div class="insight-box"><strong><?php echo htmlspecialchars($insight['title']); ?></strong><?php echo htmlspecialchars($insight['text']); ?></div>
                    <?php } ?>
                </div>

                <div class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'invoice') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Invoice Trend</h3>
                        <div class="trend-summary">
                            <div class="trend-chip">
                                <span>First Month</span>
                                <strong><?php echo htmlspecialchars(isset($invoiceTrendFirst['label']) ? $invoiceTrendFirst['label'] : '-'); ?> / <?php echo dashMoney(isset($invoiceTrendFirst['amount']) ? $invoiceTrendFirst['amount'] : 0); ?></strong>
                            </div>
                            <div class="trend-chip">
                                <span>Latest Month</span>
                                <strong><?php echo htmlspecialchars(isset($invoiceTrendLast['label']) ? $invoiceTrendLast['label'] : '-'); ?> / <?php echo dashMoney(isset($invoiceTrendLast['amount']) ? $invoiceTrendLast['amount'] : 0); ?></strong>
                            </div>
                            <div class="trend-chip <?php echo $invoiceTrendDirection; ?>">
                                <span>Movement</span>
                                <strong><?php echo $invoiceTrendDirection === 'up' ? 'Up' : ($invoiceTrendDirection === 'down' ? 'Down' : 'Flat'); ?> <?php echo dashMoney(abs($invoiceTrendDiff)); ?></strong>
                            </div>
                        </div>
                        <canvas id="invoiceTrendChart" class="dash-chart"></canvas>
                    </div>
                    <div class="dash-panel">
                        <h3>Invoice Split</h3>
                        <canvas id="invoiceTypeChart" class="dash-chart"></canvas>
                    </div>
                </div>

                <div class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'letters') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Due Letter Status</h3>
                        <canvas id="letterStatusChart" class="dash-chart"></canvas>
                    </div>
                    <div class="dash-panel">
                        <h3>Letter Types</h3>
                        <?php
                        $maxLetterAction = 0;
                        foreach ($letterActionRows as $row) {
                            $maxLetterAction = max($maxLetterAction, (int)$row['total']);
                        }
                        ?>
                        <div class="letter-rank-list">
                            <?php if (empty($letterActionRows)) { ?>
                                <div class="letter-rank-empty">No letter data found.</div>
                            <?php } ?>
                            <?php foreach ($letterActionRows as $row) {
                                $letterTotal = (int)$row['total'];
                                $letterPercent = $maxLetterAction > 0 ? max(4, round(($letterTotal / $maxLetterAction) * 100)) : 0;
                                ?>
                                <div class="letter-rank-item">
                                    <div class="letter-rank-head">
                                        <span class="letter-rank-title" title="<?php echo htmlspecialchars($row['label']); ?>"><?php echo htmlspecialchars($row['label']); ?></span>
                                        <span class="letter-rank-count"><?php echo dashInt($letterTotal); ?></span>
                                    </div>
                                    <div class="letter-rank-track"><div class="letter-rank-fill" style="width: <?php echo $letterPercent; ?>%;"></div></div>
                                    <div class="letter-rank-meta">
                                        <span>Due <?php echo dashInt(isset($row['due']) ? $row['due'] : 0); ?></span>
                                        <span>Sent <?php echo dashInt(isset($row['sent']) ? $row['sent'] : 0); ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'payments' || $dashboardType === 'clients') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Pending Payment by Service</h3>
                        <canvas id="paymentChart" class="dash-chart"></canvas>
                    </div>
                    <div class="dash-panel">
                        <h3>Client Status Dots</h3>
                        <canvas id="clientDotChart" class="dash-chart"></canvas>
                    </div>
                </div>

                <div class="dash-section-grid">
                    <div class="dash-panel <?php echo ($dashboardType === 'business' || $dashboardType === 'invoice') ? '' : 'hidden-dashboard'; ?>">
                        <h3>Recent Invoices</h3>
                        <table class="dash-table">
                            <thead><tr><th>Client</th><th>Type</th><th>Status</th><th>Date</th><th>Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $row) { ?>
                                    <tr class="clickable-row open-client" data-customer="<?php echo (int)$row['customer_id']; ?>"><td><?php echo htmlspecialchars($row['customer_name']); ?></td><td><span class="dash-pill"><?php echo htmlspecialchars($row['invoice_type']); ?></span></td><td><?php echo htmlspecialchars($row['invoice_status']); ?></td><td><?php echo dashDate($row['invoice_date']); ?></td><td><?php echo dashMoney($row['final_amount']); ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-panel <?php echo ($dashboardType === 'business' || $dashboardType === 'letters') ? '' : 'hidden-dashboard'; ?>">
                        <h3>Latest Letter Activity</h3>
                        <table class="dash-table">
                            <thead><tr><th>Client</th><th>Service</th><th>Letter</th><th>Status</th><th>Due</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentLetters as $row) { ?>
                                    <tr class="clickable-row open-client" data-customer="<?php echo (int)$row['customer_id']; ?>"><td><?php echo htmlspecialchars($row['customer_name']); ?></td><td><?php echo htmlspecialchars($row['service_type']); ?></td><td><?php echo htmlspecialchars($row['action']); ?></td><td><span class="dash-pill"><?php echo htmlspecialchars($row['status'] === 'sent' ? 'sent' : 'due'); ?></span></td><td><?php echo dashDate($row['due_date']); ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'payments') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Payment Information</h3>
                        <table class="dash-table">
                            <thead><tr><th>Client</th><th>Service</th><th>Decided</th><th>Received</th><th>Pending</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentPayments as $row) { ?>
                                    <tr class="clickable-row open-client" data-customer="<?php echo (int)$row['customer_id']; ?>">
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><span class="dash-pill"><?php echo htmlspecialchars($row['service_type']); ?></span></td>
                                        <td><?php echo dashMoney($row['amount_decided']); ?></td>
                                        <td><?php echo dashMoney($row['payment_received']); ?></td>
                                        <td><?php echo dashMoney($row['balance']); ?></td>
                                        <td><?php echo dashDate($row['payment_date']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-panel">
                        <h3>Payment Focus</h3>
                        <table class="dash-table">
                            <tbody>
                                <tr><td>Collection Ratio</td><td><strong><?php echo dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0); ?></strong></td></tr>
                                <tr><td>Pending Ratio</td><td><strong><?php echo dashPercent(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0); ?></strong></td></tr>
                                <tr><td>Pending Collection</td><td><strong><?php echo dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0); ?></strong></td></tr>
                                <tr><td>Payment Rows</td><td><strong><?php echo dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0); ?></strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'clients') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Client Details</h3>
                        <table class="dash-table">
                            <thead><tr><th>Client</th><th>Unique ID</th><th>Status</th><th>Next Service</th><th>Services</th><th>Pending</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentClients as $row) { ?>
                                    <tr class="clickable-row open-client" data-customer="<?php echo (int)$row['id']; ?>">
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small><?php echo htmlspecialchars($row['contact_person']); ?> / <?php echo htmlspecialchars($row['contact']); ?></small></td>
                                        <td><?php echo htmlspecialchars($row['unique_id']); ?></td>
                                        <td><span class="dash-pill"><?php echo htmlspecialchars($row['audit_status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['next_service']); ?></td>
                                        <td><?php echo dashInt($row['service_count']); ?></td>
                                        <td><?php echo dashMoney($row['pending_amount']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-panel">
                        <h3>Online Applications</h3>
                        <table class="dash-table">
                            <thead><tr><th>Organization</th><th>Contact</th><th>Standard</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentOnlineApplications as $row) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['org_name']); ?><br><small><?php echo dashDate($row['created_at']); ?></small></td>
                                        <td><?php echo htmlspecialchars($row['contact_person']); ?><br><small><?php echo htmlspecialchars($row['phone']); ?></small></td>
                                        <td><?php echo htmlspecialchars($row['standard_applied']); ?></td>
                                        <td><span class="dash-pill"><?php echo ((int)$row['read_flag'] === 1) ? 'read' : 'unread'; ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="openClientForm" action="customerAccount.php" method="post" style="display:none;">
        <input type="hidden" name="customerid" id="openClientId" value="">
    </form>
</div>

<script>
    var dashData = <?php echo json_encode($chartData); ?>;

    function setupCanvas(id) {
        var canvas = document.getElementById(id);
        if (!canvas) return null;
        var rect = canvas.getBoundingClientRect();
        var scale = window.devicePixelRatio || 1;
        canvas.width = rect.width * scale;
        canvas.height = rect.height * scale;
        var ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);
        return { canvas: canvas, ctx: ctx, width: rect.width, height: rect.height };
    }

    function compactLabel(value, maxLength) {
        value = String(value || '');
        maxLength = maxLength || 10;
        if (value.length <= maxLength) {
            return value;
        }
        return value.substring(0, maxLength - 1) + '.';
    }

    function compactNumber(value) {
        value = Number(value) || 0;
        if (Math.abs(value) >= 10000000) {
            return (value / 10000000).toFixed(1).replace('.0', '') + 'Cr';
        }
        if (Math.abs(value) >= 100000) {
            return (value / 100000).toFixed(1).replace('.0', '') + 'L';
        }
        if (Math.abs(value) >= 1000) {
            return (value / 1000).toFixed(1).replace('.0', '') + 'K';
        }
        return Math.round(value);
    }

    function drawAxisLabel(ctx, text, x, y, maxWidth) {
        text = compactLabel(text, Math.max(5, Math.floor(maxWidth / 7)));
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(-Math.PI / 5);
        ctx.textAlign = 'right';
        ctx.fillText(text, 0, 0);
        ctx.restore();
    }

    function drawBars(id, rows, key, color) {
        var c = setupCanvas(id);
        if (!c) return;
        var ctx = c.ctx, w = c.width, h = c.height, leftPad = 34, topPad = 24, rightPad = 16, bottomPad = 72;
        ctx.clearRect(0, 0, w, h);
        rows = rows || [];
        var max = Math.max.apply(null, rows.map(function (r) { return parseFloat(r[key] || 0); }).concat([1]));
        var plotW = w - leftPad - rightPad;
        var plotH = h - topPad - bottomPad;
        var gap = rows.length > 8 ? 8 : 12;
        var barW = Math.max(14, Math.min(42, (plotW / Math.max(rows.length, 1)) - gap));
        ctx.strokeStyle = '#e3eaf3';
        ctx.beginPath();
        ctx.moveTo(leftPad, topPad);
        ctx.lineTo(leftPad, h - bottomPad);
        ctx.lineTo(w - rightPad, h - bottomPad);
        ctx.stroke();
        rows.forEach(function (row, i) {
            var slotW = plotW / Math.max(rows.length, 1);
            var x = leftPad + i * slotW + (slotW - barW) / 2;
            var val = parseFloat(row[key] || 0);
            var bh = plotH * val / max;
            var y = h - bottomPad - bh;
            ctx.fillStyle = color;
            ctx.fillRect(x, y, barW, bh);
            ctx.fillStyle = '#59708d';
            ctx.font = '11px Arial';
            drawAxisLabel(ctx, row.label || '', x + barW / 2, h - 38, slotW - 4);
            ctx.fillStyle = '#132238';
            ctx.font = '700 11px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(compactNumber(val), x + barW / 2, Math.max(14, y - 7));
            ctx.textAlign = 'left';
        });
    }

    function drawInvoiceTrend(id, rows) {
        var c = setupCanvas(id);
        if (!c) return;
        var ctx = c.ctx, w = c.width, h = c.height, leftPad = 54, topPad = 26, rightPad = 24, bottomPad = 76;
        rows = rows || [];
        var maxAmount = Math.max.apply(null, rows.map(function (r) { return parseFloat(r.amount || 0); }).concat([1]));
        var maxCount = Math.max.apply(null, rows.map(function (r) { return parseFloat(r.total || 0); }).concat([1]));
        ctx.clearRect(0, 0, w, h);
        var plotW = w - leftPad - rightPad;
        var plotH = h - topPad - bottomPad;
        for (var g = 0; g < 4; g++) {
            var gy = topPad + g * (plotH / 3);
            ctx.strokeStyle = '#edf2f8';
            ctx.beginPath();
            ctx.moveTo(leftPad, gy);
            ctx.lineTo(w - rightPad, gy);
            ctx.stroke();
            var gridValue = maxAmount - (maxAmount * g / 3);
            ctx.fillStyle = '#8a9aaf';
            ctx.font = '10px Arial';
            ctx.textAlign = 'right';
            ctx.fillText(compactNumber(gridValue), leftPad - 8, gy + 3);
        }
        var slotW = plotW / Math.max(rows.length, 1);
        var barW = Math.max(16, Math.min(34, slotW * .44));
        rows.forEach(function (row, i) {
            var val = parseFloat(row.amount || 0);
            var x = leftPad + i * slotW + (slotW - barW) / 2;
            var bh = plotH * val / maxAmount;
            var y = h - bottomPad - bh;
            var gradient = ctx.createLinearGradient(0, y, 0, h - bottomPad);
            gradient.addColorStop(0, 'rgba(21, 94, 149, .72)');
            gradient.addColorStop(1, 'rgba(21, 94, 149, .14)');
            ctx.fillStyle = gradient;
            ctx.fillRect(x, y, barW, bh);
        });
        ctx.strokeStyle = '#155e95';
        ctx.lineWidth = 2.5;
        ctx.beginPath();
        rows.forEach(function (row, i) {
            var x = leftPad + i * slotW + slotW / 2;
            var y = h - bottomPad - (plotH * parseFloat(row.total || 0) / maxCount);
            if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        });
        ctx.stroke();
        rows.forEach(function (row, i) {
            var amount = parseFloat(row.amount || 0);
            var count = parseFloat(row.total || 0);
            var x = leftPad + i * slotW + slotW / 2;
            var y = h - bottomPad - (plotH * count / maxCount);
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.arc(x, y, 5, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#ef7b45';
            ctx.lineWidth = 2.5;
            ctx.stroke();
            ctx.fillStyle = '#0f1f33';
            ctx.font = '800 10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(compactNumber(amount), x, Math.max(14, h - bottomPad - (plotH * amount / maxAmount) - 7));
            ctx.fillStyle = '#59708d';
            ctx.font = '11px Arial';
            drawAxisLabel(ctx, row.label || '', x, h - 34, 58);
        });
        ctx.textAlign = 'left';
        ctx.fillStyle = '#155e95';
        ctx.fillRect(leftPad, h - 18, 10, 6);
        ctx.fillStyle = '#52647d';
        ctx.font = '11px Arial';
        ctx.fillText('Amount bars', leftPad + 15, h - 12);
        ctx.beginPath();
        ctx.arc(leftPad + 106, h - 15, 4, 0, Math.PI * 2);
        ctx.fillStyle = '#ef7b45';
        ctx.fill();
        ctx.fillStyle = '#52647d';
        ctx.fillText('Invoice count line', leftPad + 116, h - 12);
    }

    function drawDonut(id, rows) {
        var c = setupCanvas(id);
        if (!c) return;
        var ctx = c.ctx, w = c.width, h = c.height;
        rows = rows || [];
        var colors = ['#155e95', '#1f9d68', '#ef7b45', '#9b6bdf', '#d14f62'];
        var total = rows.reduce(function (sum, r) { return sum + parseFloat(r.total || 0); }, 0) || 1;
        var cx = w / 2, cy = h / 2 - 8, radius = Math.min(w, h) / 3, start = -Math.PI / 2;
        rows.forEach(function (row, i) {
            var part = (parseFloat(row.total || 0) / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.arc(cx, cy, radius, start, start + part);
            ctx.lineWidth = 28;
            ctx.strokeStyle = colors[i % colors.length];
            ctx.stroke();
            start += part;
        });
        ctx.fillStyle = '#12263f';
        ctx.font = '800 22px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(Math.round(total), cx, cy + 7);
        ctx.textAlign = 'left';
        rows.forEach(function (row, i) {
            ctx.fillStyle = colors[i % colors.length];
            ctx.fillRect(14, h - 54 + i * 17, 10, 10);
            ctx.fillStyle = '#566881';
            ctx.font = '12px Arial';
            ctx.fillText((row.label || '') + ' ' + (row.total || 0), 30, h - 45 + i * 17);
        });
    }

    function drawDots(id, rows) {
        var c = setupCanvas(id);
        if (!c) return;
        var ctx = c.ctx, w = c.width, h = c.height;
        ctx.clearRect(0, 0, w, h);
        rows = rows || [];
        var max = Math.max.apply(null, rows.map(function (r) { return parseFloat(r.total || 0); }).concat([1]));
        rows.forEach(function (row, i) {
            var count = Math.min(24, Math.max(1, Math.ceil((parseFloat(row.total || 0) / max) * 24)));
            var y = 34 + i * 30;
            ctx.fillStyle = '#566881';
            ctx.font = '12px Arial';
            ctx.fillText(String(row.label || '').substring(0, 18), 12, y + 4);
            for (var d = 0; d < count; d++) {
                ctx.beginPath();
                ctx.arc(150 + d * 12, y, 4, 0, Math.PI * 2);
                ctx.fillStyle = d % 3 === 0 ? '#ef7b45' : '#155e95';
                ctx.fill();
            }
            ctx.fillStyle = '#12263f';
            ctx.font = '800 12px Arial';
            ctx.fillText(row.total || 0, w - 36, y + 4);
        });
    }

    function drawAllCharts() {
        drawInvoiceTrend('invoiceTrendChart', dashData.invoiceTrend);
        drawDonut('invoiceTypeChart', dashData.invoiceTypes);
        drawBars('letterStatusChart', dashData.letterTotals, 'total', '#ef7b45');
        drawBars('paymentChart', dashData.paymentServices, 'amount', '#1f9d68');
        drawDots('clientDotChart', dashData.clientStatuses);
    }

    function formatCounter(value, format) {
        value = Number(value) || 0;
        if (format === 'money') {
            return 'Rs. ' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return Math.round(value).toLocaleString('en-IN');
    }

    function animateCounters() {
        $('.dash-count').each(function () {
            var el = $(this);
            var target = parseFloat(el.attr('data-raw') || '0');
            var format = el.attr('data-format');
            var start = null;
            function tick(timestamp) {
                if (!start) start = timestamp;
                var progress = Math.min((timestamp - start) / 900, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.text(formatCounter(target * eased, format));
                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    el.text(formatCounter(target, format));
                }
            }
            window.requestAnimationFrame(tick);
        });
    }

    $(document).ready(function () {
        drawAllCharts();
        animateCounters();
        $(window).on('resize', drawAllCharts);
        $('[name="dashboard_type"]').on('change', function () { $(this).closest('form').submit(); });
        $('.open-client').on('click', function () {
            var customerId = $(this).attr('data-customer');
            if (!customerId || customerId === '0') {
                return;
            }
            $('#openClientId').val(customerId);
            $('#openClientForm').submit();
        });
        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            setTimeout(drawAllCharts, 250);
        });
    });
</script>
