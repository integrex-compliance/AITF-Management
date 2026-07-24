<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "GoAI";
    $addGoAiClass = "active";
    include_once './header.php';
    include_once './dbConnect.php';
    include_once './utility.php';
    include_once './include/ai_document_review_lib.php';
    date_default_timezone_set('Asia/Kolkata');

    if (Utitlity::checkClient() || Utitlity::checkExternalUser()) {
        header("Location: homePage.php");
        exit;
    }

    aitfAiEnsureSchema($db);

    function goAiH($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function goAiShort($value, $length = 160)
    {
        $value = trim(preg_replace('/\s+/', ' ', (string) $value));
        if (strlen($value) <= $length) {
            return $value;
        }
        return substr($value, 0, $length - 3) . '...';
    }

    function goAiStatusPill($ok, $text)
    {
        return '<span class="goai-pill ' . ($ok ? 'goai-pill-ok' : 'goai-pill-wait') . '"><i class="fa ' . ($ok ? 'fa-check' : 'fa-clock-o') . '"></i> ' . goAiH($text) . '</span>';
    }

    function goAiRenderResponse($resultJson, $rawResponse = '')
    {
        $decoded = json_decode((string) $resultJson, true);
        if (is_array($decoded)) {
            echo '<div class="goai-response-summary">';
            if (!empty($decoded['overall_status'])) {
                echo '<p><strong>Overall status:</strong> ' . goAiH(str_replace('_', ' ', $decoded['overall_status'])) . '</p>';
            }
            if (!empty($decoded['summary'])) {
                echo '<p><strong>Summary:</strong> ' . goAiH($decoded['summary']) . '</p>';
            }

            $findings = array();
            if (!empty($decoded['findings']) && is_array($decoded['findings'])) {
                $findings = $decoded['findings'];
            } elseif (!empty($decoded['issues']) && is_array($decoded['issues'])) {
                $findings = $decoded['issues'];
            }

            if (!empty($findings)) {
                echo '<h5>Findings</h5>';
                echo '<ul class="goai-response-list">';
                foreach ($findings as $finding) {
                    $area = isset($finding['check_area']) ? $finding['check_area'] : 'Review';
                    $severity = isset($finding['severity']) ? strtoupper($finding['severity']) : 'INFO';
                    $text = isset($finding['finding']) ? $finding['finding'] : (isset($finding['reason']) ? $finding['reason'] : '');
                    echo '<li><strong>' . goAiH($area) . ' (' . goAiH($severity) . '):</strong> ' . goAiH($text) . '</li>';
                }
                echo '</ul>';
            }

            if (!empty($decoded['manual_verifications']) && is_array($decoded['manual_verifications'])) {
                echo '<h5>Manual Verification</h5>';
                echo '<ul class="goai-response-list">';
                foreach ($decoded['manual_verifications'] as $item) {
                    $area = isset($item['check_area']) ? $item['check_area'] : 'Manual verification';
                    $reason = isset($item['reason']) ? $item['reason'] : '';
                    echo '<li><strong>' . goAiH($area) . ':</strong> ' . goAiH($reason) . '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';

            echo '<details class="goai-json"><summary>Full saved JSON response</summary><pre>' . goAiH(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre></details>';
        } elseif (trim((string) $resultJson) !== '') {
            echo '<pre class="goai-json-pre">' . goAiH($resultJson) . '</pre>';
        } else {
            echo '<p class="text-muted">No saved AI response found for this scan.</p>';
        }

        if (trim((string) $rawResponse) !== '') {
            echo '<details class="goai-json"><summary>Raw provider response</summary><pre>' . goAiH($rawResponse) . '</pre></details>';
        }
    }

    $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
    $params = array();
    $searchSql = '';
    if ($search !== '') {
        $searchSql = " AND (ci.customer_name LIKE :search OR ci.unique_id LIKE :search OR ci.certification_no LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $activitySql = "SELECT
            ci.id,
            ci.customer_name,
            ci.unique_id,
            ci.certification_no,
            COUNT(DISTINCT CASE WHEN dh.status = 'completed' THEN dh.id END) AS document_scans,
            COUNT(DISTINCT CASE WHEN h.status = 'completed' AND h.scan_scope = 'software_fill' THEN h.id END) AS software_scans,
            MAX(dh.analyzed_at) AS last_document_scan_at,
            MAX(CASE WHEN h.scan_scope = 'software_fill' THEN h.created_at ELSE NULL END) AS last_software_fill_at,
            GREATEST(
                COALESCE(MAX(dh.analyzed_at), '1000-01-01 00:00:00'),
                COALESCE(MAX(h.created_at), '1000-01-01 00:00:00')
            ) AS last_activity_at
        FROM customer_info ci
        LEFT JOIN ai_review_history h ON h.customer_id = ci.id
        LEFT JOIN ai_review_document_history dh ON dh.customer_id = ci.id
        WHERE (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES') " . $searchSql . "
        GROUP BY ci.id, ci.customer_name, ci.unique_id, ci.certification_no";
    if ($search === '') {
        $activitySql .= " HAVING document_scans > 0 OR software_scans > 0";
    }
    $activitySql .= " ORDER BY last_activity_at DESC, ci.customer_name ASC LIMIT 250";

    $stmt = $db->prepare($activitySql);
    $stmt->execute($params);
    $activityRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $documentSql = "SELECT dh.*, h.result_json, h.raw_response, h.created_at AS history_created_at, h.requested_by, h.model_name, ci.customer_name, ci.unique_id
        FROM ai_review_document_history dh
        INNER JOIN ai_review_history h ON h.id = dh.review_history_id
        INNER JOIN customer_info ci ON ci.id = dh.customer_id
        WHERE dh.status = 'completed' AND h.status = 'completed' " . $searchSql . "
        ORDER BY dh.analyzed_at DESC, dh.id DESC LIMIT 250";
    $stmt = $db->prepare($documentSql);
    $stmt->execute($params);
    $documentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $softwareSql = "SELECT h.*, ci.customer_name, ci.unique_id
        FROM ai_review_history h
        INNER JOIN customer_info ci ON ci.id = h.customer_id
        WHERE h.status = 'completed' AND h.scan_scope = 'software_fill' " . $searchSql . "
        ORDER BY h.created_at DESC, h.id DESC LIMIT 250";
    $stmt = $db->prepare($softwareSql);
    $stmt->execute($params);
    $softwareRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total scanned clients
    $scannedClientsQuery = "SELECT DISTINCT ci.id, ci.customer_name 
        FROM customer_info ci
        INNER JOIN (
            SELECT customer_id FROM ai_review_history WHERE status = 'completed'
            UNION
            SELECT customer_id FROM ai_review_document_history WHERE status = 'completed'
        ) AS sc ON sc.customer_id = ci.id
        WHERE (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
        ORDER BY ci.customer_name ASC";
    $stmt = $db->query($scannedClientsQuery);
    $scannedClientsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalScannedCount = count($scannedClientsList);

    $clientScans = array();
    foreach ($documentRows as $r) {
        $clientScans[$r['customer_id']]['docs'][] = $r;
    }
    foreach ($softwareRows as $r) {
        $clientScans[$r['customer_id']]['software'][] = $r;
    }
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid goai-page">
                <div class="goai-header">
                    <div>

                    </div>
                    <form method="get" action="goAi.php" class="goai-search">
                        <input type="text" name="q" class="form-control" value="<?php echo goAiH($search); ?>"
                            placeholder="Search client, agreement, certificate">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                        <?php if ($search !== '') { ?>
                            <a href="goAi.php" class="btn btn-default">Clear</a>
                        <?php } ?>
                    </form>
                </div>

                <div class="alert alert-info"
                    style="border-left: 4px solid #31708f; background-color: #d9edf7; color: #31708f; margin-bottom: 20px;">
                    <i class="fa fa-info-circle" style="margin-right: 5px;"></i>
                    <strong>GoAI Scanned client = <?php echo $totalScannedCount; ?></strong>
                    <a href="#scannedClientsModal" data-toggle="modal"
                        style="text-decoration: underline; font-weight: bold; margin-left: 10px; color: #245269;">Click
                        here to view them</a>
                </div>

                <ul class="nav nav-pills goai-tabs-v2" role="tablist">
                    <li class="active"><a href="#goaiActivity" role="tab" data-toggle="tab">Activity</a></li>
                    <li><a href="#goaiDocuments" role="tab" data-toggle="tab">Document Scanning <span
                                class="badge"><?php echo count($documentRows); ?></span></a></li>
                    <li><a href="#goaiSoftware" role="tab" data-toggle="tab">Software Fill Scanning <span
                                class="badge"><?php echo count($softwareRows); ?></span></a></li>
                </ul>

                <div class="tab-content goai-tab-content">
                    <div class="tab-pane active" id="goaiActivity">
                        <div class="goai-card">
                            <div class="goai-card-title">Client Scan Activity</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover goai-table">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Document Scan</th>
                                            <th>Software Fill Scan</th>
                                            <th>Last Activity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activityRows)) { ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No GoAI activity found.</td>
                                            </tr>
                                        <?php } ?>
                                        <?php foreach ($activityRows as $row) {
                                            $docOk = (int) $row['document_scans'] > 0;
                                            $softwareOk = (int) $row['software_scans'] > 0;
                                            $lastActivity = $row['last_activity_at'] !== '1000-01-01 00:00:00' ? $row['last_activity_at'] : '-';
                                            $clientId = (int) $row['id'];
                                            $hasScans = isset($clientScans[$clientId]);
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo goAiH($row['customer_name']); ?></strong><br>
                                                    <span class="text-muted"><?php echo goAiH($row['unique_id']); ?>
                                                        <?php echo goAiH($row['certification_no']); ?></span>
                                                </td>
                                                <td><?php echo goAiStatusPill($docOk, $docOk ? 'Scanned (' . (int) $row['document_scans'] . ')' : 'Not scanned'); ?><br><small><?php echo goAiH($row['last_document_scan_at']); ?></small>
                                                </td>
                                                <td><?php echo goAiStatusPill($softwareOk, $softwareOk ? 'Scanned (' . (int) $row['software_scans'] . ')' : 'Not scanned'); ?><br><small><?php echo goAiH($row['last_software_fill_at']); ?></small>
                                                </td>
                                                <td><?php echo goAiH($lastActivity); ?></td>
                                                <td>
                                                    <a class="btn btn-xs btn-default"
                                                        href="customerAccount.php?customerid=<?php echo $clientId; ?>"><i
                                                            class="fa fa-folder-open"></i> Open Client</a>
                                                    <?php if ($hasScans) { ?>
                                                        <button type="button" class="btn btn-xs btn-info" data-toggle="collapse"
                                                            data-target="#scans-<?php echo $clientId; ?>"><i
                                                                class="fa fa-eye"></i> View AI Response</button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php if ($hasScans) { ?>
                                                <tr>
                                                    <td colspan="5" style="padding: 0; border: 0;">
                                                        <div class="collapse" id="scans-<?php echo $clientId; ?>">
                                                            <div
                                                                style="padding: 15px; background: #f8fafc; border-bottom: 2px solid #dbe3ef; margin-bottom: 0;">
                                                                <?php
                                                                if (isset($clientScans[$clientId]['docs'])) {
                                                                    echo '<h5 style="margin-top:0;font-weight:bold;"><i class="fa fa-file-text-o"></i> Document Scans</h5>';
                                                                    foreach ($clientScans[$clientId]['docs'] as $dRow) {
                                                                        ?>
                                                                        <div class="goai-activity-item"
                                                                            style="background:#fff; margin-bottom:10px;">
                                                                            <div
                                                                                style="padding: 10px 15px; border-bottom: 1px solid #dbe3ef; background: #f1f5f9;">
                                                                                <strong><?php echo goAiH($dRow['document_label']); ?> -
                                                                                    <?php echo goAiH($dRow['document_name']); ?></strong>
                                                                                <span class="pull-right goai-muted"
                                                                                    style="display:inline; margin:0;"><?php echo goAiH($dRow['analyzed_at']); ?></span>
                                                                            </div>
                                                                            <div class="goai-activity-body">
                                                                                <?php goAiRenderResponse($dRow['result_json'], $dRow['raw_response']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                }
                                                                if (isset($clientScans[$clientId]['software'])) {
                                                                    echo '<h5 style="margin-top:15px;font-weight:bold;"><i class="fa fa-desktop"></i> Software Fill Scans</h5>';
                                                                    foreach ($clientScans[$clientId]['software'] as $sRow) {
                                                                        ?>
                                                                        <div class="goai-activity-item"
                                                                            style="background:#fff; margin-bottom:10px;">
                                                                            <div
                                                                                style="padding: 10px 15px; border-bottom: 1px solid #dbe3ef; background: #f1f5f9;">
                                                                                <strong>Software Fill Scan</strong>
                                                                                <span class="pull-right goai-muted"
                                                                                    style="display:inline; margin:0;"><?php echo goAiH($sRow['created_at']); ?></span>
                                                                            </div>
                                                                            <div class="goai-activity-body">
                                                                                <?php goAiRenderResponse($sRow['result_json'], $sRow['raw_response']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="goaiDocuments">
                        <?php if (empty($documentRows)) { ?>
                            <div class="goai-empty">No document scan responses found.</div>
                        <?php } ?>
                        <?php foreach ($documentRows as $row) { ?>
                            <details class="goai-activity-item">
                                <summary>
                                    <span>
                                        <strong><?php echo goAiH($row['customer_name']); ?></strong>
                                        <span class="goai-muted"><?php echo goAiH($row['document_label']); ?> -
                                            <?php echo goAiH($row['document_name']); ?></span>
                                    </span>
                                    <span class="goai-item-meta"><?php echo goAiH($row['analyzed_at']); ?> |
                                        <?php echo goAiH($row['model_name']); ?></span>
                                </summary>
                                <div class="goai-activity-body">
                                    <p>
                                        <?php echo goAiStatusPill(true, 'Scanned'); ?>
                                        <a class="btn btn-xs btn-default"
                                            href="customerAccount.php?customerid=<?php echo (int) $row['customer_id']; ?>"><i
                                                class="fa fa-folder-open"></i> Client</a>
                                    </p>
                                    <p><strong>Report:</strong> <?php echo goAiH($row['document_label']); ?> |
                                        <strong>Service:</strong> <?php echo goAiH($row['service_type']); ?>
                                        #<?php echo goAiH($row['service_id']); ?>
                                    </p>
                                    <?php goAiRenderResponse($row['result_json'], $row['raw_response']); ?>
                                </div>
                            </details>
                        <?php } ?>
                    </div>

                    <div class="tab-pane" id="goaiSoftware">
                        <?php if (empty($softwareRows)) { ?>
                            <div class="goai-empty">No software-fill scan responses found.</div>
                        <?php } ?>
                        <?php foreach ($softwareRows as $row) { ?>
                            <details class="goai-activity-item">
                                <summary>
                                    <span>
                                        <strong><?php echo goAiH($row['customer_name']); ?></strong>
                                        <span class="goai-muted">Software-filled forms and saved client information</span>
                                    </span>
                                    <span class="goai-item-meta"><?php echo goAiH($row['created_at']); ?> |
                                        <?php echo goAiH($row['model_name']); ?></span>
                                </summary>
                                <div class="goai-activity-body">
                                    <p>
                                        <?php echo goAiStatusPill(true, 'Scanned'); ?>
                                        <a class="btn btn-xs btn-default"
                                            href="customerAccount.php?customerid=<?php echo (int) $row['customer_id']; ?>"><i
                                                class="fa fa-folder-open"></i> Client</a>
                                    </p>
                                    <p><strong>Scan type:</strong> Software Fill Scanning | <strong>Rules applied:</strong>
                                        <?php echo (int) $row['rules_count']; ?> | <strong>By:</strong>
                                        <?php echo goAiH($row['requested_by']); ?></p>
                                    <?php goAiRenderResponse($row['result_json'], $row['raw_response']); ?>
                                </div>
                            </details>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scanned Clients Modal -->
        <div class="modal fade" id="scannedClientsModal" tabindex="-1" role="dialog"
            aria-labelledby="scannedClientsModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="scannedClientsModalLabel">Clients Scanned by GoAI</h4>
                    </div>
                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($scannedClientsList)) { ?>
                            <p>No clients have been scanned yet.</p>
                        <?php } else { ?>
                            <div class="list-group">
                                <?php foreach ($scannedClientsList as $c) { ?>
                                    <a href="customerAccount.php?customerid=<?php echo (int) $c['id']; ?>"
                                        class="list-group-item">
                                        <i class="fa fa-user" style="margin-right:8px; color:#64748b;"></i>
                                        <?php echo goAiH($c['customer_name']); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .goai-page {
        margin-top: 82px;
        padding: 18px 24px 40px;
    }

    .goai-header {
        align-items: flex-end;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .goai-header h2 {
        color: #172554;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .goai-header p {
        color: #64748b;
        margin: 0;
    }

    .goai-search {
        display: flex;
        gap: 8px;
        min-width: 420px;
    }

    .goai-tabs {
        border-bottom: 0 !important;
        display: flex !important;
        justify-content: flex-start !important;
        gap: 12px;
        margin-bottom: 20px;
        padding: 0;
        list-style: none;
        flex-wrap: wrap !important;
    }

    .goai-tabs>li {
        float: none !important;
        margin: 0 !important;
    }

    .goai-tabs>li>a {
        white-space: nowrap !important;
        margin: 0 !important;
        padding: 10px 24px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        color: #64748b !important;
        font-weight: 700 !important;
        font-size: 14px;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        display: flex !important;
        align-items: center;
        gap: 8px;
    }

    .goai-tabs>li>a:hover {
        border-color: #94a3b8 !important;
        color: #0f172a !important;
        background: #f8fafc !important;
    }

    .goai-tabs>li.active>a,
    .goai-tabs>li.active>a:hover,
    .goai-tabs>li.active>a:focus {
        background: #0f172a !important;
        color: #facc15 !important;
        border-color: #0f172a !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2) !important;
    }

    .goai-tabs .badge {
        background: #f1f5f9 !important;
        color: #475569 !important;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: 800;
    }

    .goai-tabs>li.active .badge {
        background: rgba(250, 204, 21, 0.2) !important;
        color: #facc15 !important;
    }

    .goai-tab-content {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .goai-card-title {
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .goai-table th {
        background: #0f172a;
        color: #fff;
        font-size: 12px;
        text-transform: uppercase;
    }

    .goai-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 11px;
        font-weight: 800;
        gap: 5px;
        padding: 4px 9px;
        text-transform: uppercase;
    }

    .goai-pill-ok {
        background: #dcfce7;
        color: #166534;
    }

    .goai-pill-wait {
        background: #f1f5f9;
        color: #64748b;
    }

    .goai-activity-item {
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .goai-activity-item summary {
        align-items: center;
        background: #f8fafc;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 15px;
    }

    .goai-muted,
    .goai-item-meta {
        color: #64748b;
        display: block;
        font-size: 12px;
        margin-top: 3px;
    }

    .goai-activity-body {
        padding: 15px;
    }

    .goai-response-list {
        padding-left: 18px;
    }

    .goai-json {
        margin-top: 12px;
    }

    .goai-json pre,
    .goai-json-pre {
        background: #0f172a;
        border: 0;
        color: #e2e8f0;
        max-height: 360px;
        overflow: auto;
        padding: 12px;
        white-space: pre-wrap;
    }

    .goai-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        padding: 24px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .goai-header {
            align-items: stretch;
            flex-direction: column;
        }

        .goai-search {
            min-width: 0;
            width: 100%;
        }

        .goai-activity-item summary {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
</div>