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
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid goai-page">
                <div class="goai-header">
                    <div>
                        <h2><i class="fa fa-magic"></i> GoAI</h2>
                        <p>AI scan activity for document scanning and software-filled form scanning.</p>
                    </div>
                    <form method="get" action="goAi.php" class="goai-search">
                        <input type="text" name="q" class="form-control" value="<?php echo goAiH($search); ?>" placeholder="Search client, agreement, certificate">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                        <?php if ($search !== '') { ?>
                            <a href="goAi.php" class="btn btn-default">Clear</a>
                        <?php } ?>
                    </form>
                </div>

                <ul class="nav nav-tabs goai-tabs" role="tablist">
                    <li class="active"><a href="#goaiActivity" role="tab" data-toggle="tab">Activity</a></li>
                    <li><a href="#goaiDocuments" role="tab" data-toggle="tab">Document Scanning <span class="badge"><?php echo count($documentRows); ?></span></a></li>
                    <li><a href="#goaiSoftware" role="tab" data-toggle="tab">Software Fill Scanning <span class="badge"><?php echo count($softwareRows); ?></span></a></li>
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
                                            <tr><td colspan="5" class="text-center text-muted">No GoAI activity found.</td></tr>
                                        <?php } ?>
                                        <?php foreach ($activityRows as $row) {
                                            $docOk = (int) $row['document_scans'] > 0;
                                            $softwareOk = (int) $row['software_scans'] > 0;
                                            $lastActivity = $row['last_activity_at'] !== '1000-01-01 00:00:00' ? $row['last_activity_at'] : '-';
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo goAiH($row['customer_name']); ?></strong><br>
                                                    <span class="text-muted"><?php echo goAiH($row['unique_id']); ?> <?php echo goAiH($row['certification_no']); ?></span>
                                                </td>
                                                <td><?php echo goAiStatusPill($docOk, $docOk ? 'Scanned (' . (int) $row['document_scans'] . ')' : 'Not scanned'); ?><br><small><?php echo goAiH($row['last_document_scan_at']); ?></small></td>
                                                <td><?php echo goAiStatusPill($softwareOk, $softwareOk ? 'Scanned (' . (int) $row['software_scans'] . ')' : 'Not scanned'); ?><br><small><?php echo goAiH($row['last_software_fill_at']); ?></small></td>
                                                <td><?php echo goAiH($lastActivity); ?></td>
                                                <td><a class="btn btn-xs btn-default" href="customerAccount.php?customerid=<?php echo (int) $row['id']; ?>"><i class="fa fa-folder-open"></i> Open Client</a></td>
                                            </tr>
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
                                        <span class="goai-muted"><?php echo goAiH($row['document_label']); ?> - <?php echo goAiH($row['document_name']); ?></span>
                                    </span>
                                    <span class="goai-item-meta"><?php echo goAiH($row['analyzed_at']); ?> | <?php echo goAiH($row['model_name']); ?></span>
                                </summary>
                                <div class="goai-activity-body">
                                    <p>
                                        <?php echo goAiStatusPill(true, 'Scanned'); ?>
                                        <a class="btn btn-xs btn-default" href="customerAccount.php?customerid=<?php echo (int) $row['customer_id']; ?>"><i class="fa fa-folder-open"></i> Client</a>
                                    </p>
                                    <p><strong>Report:</strong> <?php echo goAiH($row['document_label']); ?> | <strong>Service:</strong> <?php echo goAiH($row['service_type']); ?> #<?php echo goAiH($row['service_id']); ?></p>
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
                                    <span class="goai-item-meta"><?php echo goAiH($row['created_at']); ?> | <?php echo goAiH($row['model_name']); ?></span>
                                </summary>
                                <div class="goai-activity-body">
                                    <p>
                                        <?php echo goAiStatusPill(true, 'Scanned'); ?>
                                        <a class="btn btn-xs btn-default" href="customerAccount.php?customerid=<?php echo (int) $row['customer_id']; ?>"><i class="fa fa-folder-open"></i> Client</a>
                                    </p>
                                    <p><strong>Scan type:</strong> Software Fill Scanning | <strong>Rules applied:</strong> <?php echo (int) $row['rules_count']; ?> | <strong>By:</strong> <?php echo goAiH($row['requested_by']); ?></p>
                                    <?php goAiRenderResponse($row['result_json'], $row['raw_response']); ?>
                                </div>
                            </details>
                        <?php } ?>
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
            border-bottom-color: #dbe3ef;
        }
        .goai-tab-content {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-top: 0;
            padding: 18px;
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
