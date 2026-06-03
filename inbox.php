<?php
$pageIdentifier = "Inbox";
$addInboxClass = "active";
?>
<div id="wrapper" class="active">
    <?php
    include 'header.php';
    include 'dbConnect.php';

    $sql = "SELECT 
                c.id AS customer_id,
                c.customer_name,
                cas.ca_id,
                s.service_id AS main_service_id,
                cas.audit_date AS stage1_date,
                cas.stage2_audit AS stage2_date,
                soi.audit_stage_id AS stage1_notification_id,
                asi.audit_schedule_id AS stage1_schedule_id,
                st2.audit_stageii_id AS stage2_notification_id,
                asii.audit_scheduleii_id AS stage2_schedule_id,
                (SELECT sent_date FROM ca_service_dates sd WHERE sd.service_id = s.service_id AND sd.action = 'Stage I Letter Sent' AND (sd.is_deleted IS NULL OR sd.is_deleted <> 'Y') ORDER BY sd.sent_date DESC LIMIT 1) AS stage1_sent_date,
                (SELECT sent_date FROM ca_service_dates sd WHERE sd.service_id = s.service_id AND sd.action = 'Stage II Letter Sent' AND (sd.is_deleted IS NULL OR sd.is_deleted <> 'Y') ORDER BY sd.sent_date DESC LIMIT 1) AS stage2_sent_date
            FROM service_type_ca cas
            INNER JOIN service s ON s.service_id = cas.service
            INNER JOIN customer_info c ON c.id = s.customer_id
            LEFT JOIN stage_oneaudit_id soi ON soi.ca_service_id = cas.ca_id
            LEFT JOIN audit_schedule_id asi ON asi.serviceca_id = cas.ca_id
            LEFT JOIN stage_twoaudit_id st2 ON st2.service_id = cas.ca_id AND st2.customer_id = c.id
            LEFT JOIN audit_scheduleii_id asii ON asii.ca_service = cas.ca_id AND asii.ca_customer = c.id
            WHERE (c.is_deleted IS NULL OR c.is_deleted <> 'YES')
            ORDER BY c.customer_name ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $entries = array();

    function inboxFormatDate($d) {
        if (!$d) return '';
        $ts = strtotime($d);
        if ($ts === false) return (string)$d;
        return date('d.m.Y', $ts);
    }

    foreach ($rows as $r) {
        $customerName = isset($r['customer_name']) ? $r['customer_name'] : '';
        $customerId = isset($r['customer_id']) ? (int)$r['customer_id'] : 0;
        $caId = isset($r['ca_id']) ? (int)$r['ca_id'] : 0;
        $mainServiceId = isset($r['main_service_id']) ? (int)$r['main_service_id'] : 0;

        $stage1Sent = !empty($r['stage1_sent_date']) ? $r['stage1_sent_date'] : $r['stage1_date'];
        $stage2Sent = !empty($r['stage2_sent_date']) ? $r['stage2_sent_date'] : $r['stage2_date'];

        $entries[] = array(
            'stage' => 'Stage I',
            'stage_key' => 'stage1',
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'ca_id' => $caId,
            'main_service_id' => $mainServiceId,
            'sent_date' => $stage1Sent,
            'ref_date' => $r['stage1_date'],
            'notification_id' => $r['stage1_notification_id'],
            'schedule_id' => $r['stage1_schedule_id']
        );

        $entries[] = array(
            'stage' => 'Stage II',
            'stage_key' => 'stage2',
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'ca_id' => $caId,
            'main_service_id' => $mainServiceId,
            'sent_date' => $stage2Sent,
            'ref_date' => $r['stage2_date'],
            'notification_id' => $r['stage2_notification_id'],
            'schedule_id' => $r['stage2_schedule_id']
        );
    }

    usort($entries, function ($a, $b) {
        $aTs = $a['sent_date'] ? strtotime($a['sent_date']) : 0;
        $bTs = $b['sent_date'] ? strtotime($b['sent_date']) : 0;
        if ($aTs === $bTs) {
            return strcasecmp($a['customer_name'], $b['customer_name']);
        }
        return $bTs <=> $aTs;
    });
    ?>

    <style>
        .inbox-wrap {
            background:
                radial-gradient(circle at 10% 12%, rgba(33, 150, 243, 0.12), transparent 32%),
                radial-gradient(circle at 92% 8%, rgba(31, 157, 104, 0.10), transparent 30%),
                linear-gradient(180deg, #eef3f8 0%, #f8fafc 52%, #f4f7fb 100%);
            min-height: 100vh;
            padding: 22px 18px 42px;
        }
        .inbox-shell { max-width: 1320px; margin: 0 auto; }
        .inbox-hero {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(198, 211, 228, 0.9);
            border-radius: 12px;
            padding: 18px 18px 12px;
            box-shadow: 0 16px 34px rgba(25, 42, 70, 0.075);
        }
        .inbox-title { margin: 0; font-weight: 900; color: #0f1f33; font-size: 22px; letter-spacing: .2px; }
        .inbox-sub { margin: 6px 0 0; color: #51657e; font-weight: 600; }
        .inbox-tools { display: flex; gap: 10px; align-items: center; margin-top: 14px; flex-wrap: wrap; }
        .inbox-tools .form-control { max-width: 360px; border-radius: 10px; border: 1px solid #d7e2ef; box-shadow: none; }
        .inbox-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            color: #0f1f33;
            background: #eef5ff;
            border: 1px solid rgba(33, 150, 243, 0.18);
        }
        .inbox-table-wrap {
            margin-top: 14px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(209, 219, 233, .95);
            border-radius: 12px;
            box-shadow: 0 16px 34px rgba(25, 42, 70, 0.075);
            overflow: hidden;
        }
        .inbox-table { width: 100%; border-collapse: collapse; }
        .inbox-table th {
            background: #f6f9fd;
            color: #52647d;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .08em;
            padding: 12px 12px;
            border-bottom: 1px solid #dfe7f2;
        }
        .inbox-table td {
            padding: 12px 12px;
            border-bottom: 1px solid #edf2f8;
            color: #24364f;
            font-weight: 600;
            font-size: 12.5px;
            vertical-align: middle;
        }
        .inbox-client { font-weight: 900; color: #0f1f33; }
        .inbox-muted { color: #6a7f99; font-weight: 700; font-size: 11.5px; }
        .inbox-stage {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 10px;
            background: #f1fbf6;
            border: 1px solid rgba(31, 157, 104, 0.18);
            color: #0f5132;
            font-weight: 900;
            font-size: 12px;
        }
        .inbox-stage.stage2 {
            background: #fff6f0;
            border-color: rgba(255, 145, 0, 0.22);
            color: #8a4b06;
        }
        .inbox-actions { display: flex; gap: 8px; justify-content: flex-end; }
        .inbox-btn {
            border: 1px solid #d7e2ef;
            background: #fff;
            border-radius: 10px;
            padding: 7px 10px;
            font-weight: 900;
            font-size: 12px;
            color: #0f1f33;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
        }
        .inbox-btn.primary { background: #0b3b63; border-color: #0b3b63; color: #fff; }
        .inbox-btn:disabled { opacity: .5; cursor: not-allowed; }
        .inbox-empty { padding: 20px; text-align: center; color: #6a7f99; font-weight: 700; }
        @media (max-width: 980px) { .inbox-actions { justify-content: flex-start; } }
    </style>

    <div id="page-content-wrapper" class="inbox-wrap">
        <div class="page-content inset">
            <div class="inbox-shell">
                <div class="inbox-hero">
                    <div class="row">
                        <div class="col-sm-8">
                            <h1 class="inbox-title">Inbox</h1>
                            <div class="inbox-sub">Client letters (Stage I / Stage II) list with sent date and quick view.</div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <span class="inbox-pill"><i class="fa fa-inbox"></i> Total entries: <span id="inboxTotalCount"><?php echo (int)count($entries); ?></span></span>
                        </div>
                    </div>
                    <div class="inbox-tools">
                        <input type="text" id="inboxSearch" class="form-control" placeholder="Search client name">
                        <select id="inboxStageFilter" class="form-control" style="max-width: 180px;">
                            <option value="">All Stages</option>
                            <option value="stage1">Stage I</option>
                            <option value="stage2">Stage II</option>
                        </select>
                    </div>
                </div>

                <div class="inbox-table-wrap">
                    <table class="inbox-table" id="inboxTable">
                        <thead>
                            <tr>
                                <th style="width: 34%;">Client</th>
                                <th style="width: 12%;">Stage</th>
                                <th style="width: 16%;">Sent Date</th>
                                <th style="width: 16%;">Stage Date</th>
                                <th style="width: 22%; text-align: right;">Letter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entries)) { ?>
                                <tr><td colspan="5" class="inbox-empty">No entries found.</td></tr>
                            <?php } else { ?>
                                <?php foreach ($entries as $e) { ?>
                                    <?php
                                    $stageKey = $e['stage_key'];
                                    $stageClass = $stageKey === 'stage2' ? 'stage2' : 'stage1';
                                    $sentDate = inboxFormatDate($e['sent_date']);
                                    $refDate = inboxFormatDate($e['ref_date']);
                                    $notifId = !empty($e['notification_id']) ? (int)$e['notification_id'] : 0;
                                    $schId = !empty($e['schedule_id']) ? (int)$e['schedule_id'] : 0;
                                    $canView = ($notifId > 0 || $schId > 0);
                                    ?>
                                    <tr class="inbox-row" data-stage="<?php echo htmlspecialchars($stageKey); ?>" data-client="<?php echo htmlspecialchars(strtolower($e['customer_name'])); ?>">
                                        <td>
                                            <div class="inbox-client"><?php echo htmlspecialchars($e['customer_name']); ?></div>
                                            <div class="inbox-muted">Customer ID: <?php echo (int)$e['customer_id']; ?> · CA ID: <?php echo (int)$e['ca_id']; ?></div>
                                        </td>
                                        <td><span class="inbox-stage <?php echo $stageKey === 'stage2' ? 'stage2' : ''; ?>"><?php echo htmlspecialchars($e['stage']); ?></span></td>
                                        <td><?php echo $sentDate !== '' ? htmlspecialchars($sentDate) : '<span class="inbox-muted">-</span>'; ?></td>
                                        <td><?php echo $refDate !== '' ? htmlspecialchars($refDate) : '<span class="inbox-muted">-</span>'; ?></td>
                                        <td style="text-align: right;">
                                            <div class="inbox-actions">
                                                <button type="button"
                                                        class="inbox-btn primary inboxViewLetter"
                                                        <?php echo $canView ? '' : 'disabled'; ?>
                                                        data-stage="<?php echo htmlspecialchars($stageKey); ?>"
                                                        data-customer="<?php echo (int)$e['customer_id']; ?>"
                                                        data-ca="<?php echo (int)$e['ca_id']; ?>"
                                                        data-notification="<?php echo $notifId; ?>"
                                                        data-schedule="<?php echo $schId; ?>">
                                                    <i class="fa fa-file-pdf-o"></i> View Letter
                                                </button>
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
    </div>

    <div class="modal fade" id="inboxLetterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span></button>
                    <h4 class="modal-title">Letter PDFs</h4>
                </div>
                <div class="modal-body">
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <button type="button" class="btn btn-info btn-block" id="inboxOpenNotification"><i class="fa fa-download"></i> Notification PDF</button>
                        <button type="button" class="btn btn-info btn-block" id="inboxOpenPlan"><i class="fa fa-download"></i> Audit Plan PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var current = { stage: '', customer: 0, ca: 0, notification: 0, schedule: 0 };

            function submitPdfForm(url, data) {
                var form = $('<form action="' + url + '" method="post" target="_blank"></form>');
                Object.keys(data).forEach(function (k) {
                    form.append('<input type="hidden" name="' + k + '" value="' + (data[k] || '') + '">');
                });
                $('body').append(form);
                form.submit();
                form.remove();
            }

            function applyFilters() {
                var q = ($('#inboxSearch').val() || '').trim().toLowerCase();
                var stage = ($('#inboxStageFilter').val() || '').trim();
                var visibleCount = 0;

                $('#inboxTable tbody .inbox-row').each(function () {
                    var $tr = $(this);
                    var client = ($tr.data('client') || '');
                    var rowStage = ($tr.data('stage') || '');
                    var ok = true;

                    if (q && client.indexOf(q) === -1) ok = false;
                    if (stage && rowStage !== stage) ok = false;

                    $tr.toggle(ok);
                    if (ok) visibleCount++;
                });

                $('#inboxTotalCount').text(visibleCount);
            }

            $('#inboxSearch').on('input', applyFilters);
            $('#inboxStageFilter').on('change', applyFilters);

            $(document).on('click', '.inboxViewLetter', function () {
                var $btn = $(this);
                current.stage = $btn.data('stage');
                current.customer = parseInt($btn.data('customer') || 0, 10);
                current.ca = parseInt($btn.data('ca') || 0, 10);
                current.notification = parseInt($btn.data('notification') || 0, 10);
                current.schedule = parseInt($btn.data('schedule') || 0, 10);

                $('#inboxOpenNotification').prop('disabled', !current.notification);
                $('#inboxOpenPlan').prop('disabled', !current.schedule);
                $('#inboxLetterModal').modal('show');
            });

            $('#inboxOpenNotification').on('click', function () {
                if (!current.notification) return;
                if (current.stage === 'stage2') {
                    submitPdfForm('AuditNotificationStage2.php', { notificationid: current.notification });
                } else {
                    submitPdfForm('AuditNotificationandAssignment.php', { auditid: current.notification, serviceid: current.ca, customerid: current.customer });
                }
            });

            $('#inboxOpenPlan').on('click', function () {
                if (!current.schedule) return;
                if (current.stage === 'stage2') {
                    submitPdfForm('SurveillanceAudit2.php', { auditnotiid: current.schedule, serviceid: current.ca, customerid: current.customer });
                } else {
                    submitPdfForm('AuditSchedule.php', { auditscheduleid: current.schedule, serviceid: current.ca, customerid: current.customer });
                }
            });

            applyFilters();
        })();
    </script>
</div>

