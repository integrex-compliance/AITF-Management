<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Auditor Checklist";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    function acRegisterCertificate(&$serviceMap, &$customerMap, $customerId, $typedServiceId, $certificateNo)
    {
        $customerId = trim((string) $customerId);
        $typedServiceId = trim((string) $typedServiceId);
        $certificateNo = trim((string) $certificateNo);

        if ($customerId === '' || $certificateNo === '') {
            return;
        }

        if ($typedServiceId !== '') {
            $serviceKey = $customerId . ':' . $typedServiceId;
            if (!isset($serviceMap[$serviceKey])) {
                $serviceMap[$serviceKey] = $certificateNo;
            }
        }

        if (!isset($customerMap[$customerId])) {
            $customerMap[$customerId] = $certificateNo;
        }
    }

    // ─── Fetch all auditors for filter dropdown ───
    $auditorSql = "SELECT auditors_id, auditor_name FROM auditors ORDER BY auditor_name ASC";
    $stmt = $db->prepare($auditorSql);
    $stmt->execute();
    $auditorsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Fetch distinct statuses for filter dropdown ───
    $statusSql = "
        SELECT DISTINCT ci.audit_status
        FROM customer_info ci
        WHERE ci.is_deleted <> 'YES'
          AND ci.audit_status IS NOT NULL
          AND TRIM(ci.audit_status) <> ''
          AND COALESCE(LOWER(REPLACE(REPLACE(TRIM(ci.audit_status), '-', ''), ' ', '')), '') <> 'inprocess'
          AND EXISTS (
              SELECT 1
              FROM service s
              WHERE s.customer_id = ci.id
                AND UPPER(TRIM(s.service_type)) NOT IN ('CR', 'CONTRACT REVIEW', 'PTR')
          )
        ORDER BY ci.audit_status ASC
    ";
    $stmt = $db->prepare($statusSql);
    $stmt->execute();
    $statusList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Fetch distinct service types for filter dropdown ───
    $serviceTypeSql = "
        SELECT DISTINCT s.service_type
        FROM service s
        JOIN customer_info ci ON ci.id = s.customer_id
        WHERE ci.is_deleted <> 'YES'
          AND UPPER(TRIM(s.service_type)) NOT IN ('CR', 'CONTRACT REVIEW', 'PTR')
          AND COALESCE(LOWER(REPLACE(REPLACE(TRIM(ci.audit_status), '-', ''), ' ', '')), '') <> 'inprocess'
        ORDER BY s.service_type ASC
    ";
    $stmt = $db->prepare($serviceTypeSql);
    $stmt->execute();
    $serviceTypeList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Build accreditation map per customer (latest from audit_application via uni_svc_id) ───
    $accredMap = array();
    $accredSql = "
        SELECT s.customer_id, aa.accereditation_requirement
        FROM audit_application aa
        JOIN service s ON s.service_id = aa.uni_svc_id
        WHERE aa.accereditation_requirement IS NOT NULL AND aa.accereditation_requirement != ''
        ORDER BY aa.audit_id DESC
    ";
    $stmt = $db->prepare($accredSql);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (!isset($accredMap[$row['customer_id']])) {
            $accredMap[$row['customer_id']] = $row['accereditation_requirement'];
        }
    }

    // ─── Build auditor map per service from all subtype tables ───
    $auditorMap = array(); // service_id => auditor_name(s)
    
    // From service_type_ca (stage1_auditor + stage2_auditor)
    $stmt = $db->query("SELECT service, stage1_auditor, stage2_auditor FROM service_type_ca WHERE (stage1_auditor IS NOT NULL AND stage1_auditor != '') OR (stage2_auditor IS NOT NULL AND stage2_auditor != '')");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['stage2_auditor'] ?: $row['stage1_auditor'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service']] = $names;
        }
    }

    // From service_type_sai
    $stmt = $db->query("SELECT service, auditors FROM service_type_sai WHERE auditors IS NOT NULL AND auditors != ''");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['auditors'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service']] = $names;
        }
    }

    // From service_type_saii
    $stmt = $db->query("SELECT service, auditors FROM service_type_saii WHERE auditors IS NOT NULL AND auditors != ''");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['auditors'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service']] = $names;
        }
    }

    // From service_type_rc
    $stmt = $db->query("SELECT service, auditors FROM service_type_rc WHERE auditors IS NOT NULL AND auditors != ''");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['auditors'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service']] = $names;
        }
    }

    // From service_type_sp
    $stmt = $db->query("SELECT service, auditors FROM service_type_sp WHERE auditors IS NOT NULL AND auditors != ''");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['auditors'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service']] = $names;
        }
    }

    // From service_type_ta
    $stmt = $db->query("SELECT service_id, auditors FROM service_type_ta WHERE auditors IS NOT NULL AND auditors != ''");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names = trim($row['auditors'], ', ');
        if (!empty($names)) {
            $auditorMap[$row['service_id']] = $names;
        }
    }

    // Fallback: notification_auditor (for services not already in map)
    $stmt = $db->query("SELECT service_id, GROUP_CONCAT(DISTINCT auditor_name SEPARATOR ', ') as auditor_names FROM notification_auditor WHERE auditor_name IS NOT NULL AND auditor_name != '' GROUP BY service_id");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (!isset($auditorMap[$row['service_id']]) && !empty($row['auditor_names'])) {
            $auditorMap[$row['service_id']] = $row['auditor_names'];
        }
    }

    // ─── Build certificate maps using the latest certificate-bearing records ───
    // Priority is CDC office-use, certificate_print, then the legacy CDC field.
    $certificateByService = array();
    $certificateByCustomer = array();

    $officeCertificateSql = "
        SELECT
            crr.customer_id,
            crr.ca_service_id AS typed_service_id,
            crr.crr_id AS source_id,
            GROUP_CONCAT(
                DISTINCT TRIM(cou.certificate_no)
                ORDER BY cou.iso_index ASC
                SEPARATOR '||'
            ) AS certificate_no
        FROM cdc_report_review crr
        INNER JOIN (
            SELECT c.customer_id, c.ca_service_id, MAX(c.crr_id) AS latest_crr_id
            FROM cdc_report_review c
            WHERE EXISTS (
                SELECT 1
                FROM cdc_report_review_office_use office_use
                WHERE office_use.crr_id = c.crr_id
                  AND TRIM(COALESCE(office_use.certificate_no, '')) <> ''
            )
            GROUP BY c.customer_id, c.ca_service_id
        ) latest
            ON latest.latest_crr_id = crr.crr_id
        INNER JOIN cdc_report_review_office_use cou
            ON cou.crr_id = crr.crr_id
           AND TRIM(COALESCE(cou.certificate_no, '')) <> ''
        GROUP BY crr.customer_id, crr.ca_service_id, crr.crr_id
        ORDER BY crr.crr_id DESC
    ";
    $stmt = $db->query($officeCertificateSql);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        acRegisterCertificate(
            $certificateByService,
            $certificateByCustomer,
            $row['customer_id'],
            $row['typed_service_id'],
            $row['certificate_no']
        );
    }

    $printedCertificateSql = "
        SELECT cp.customer_id, cp.service_id AS typed_service_id, cp.certificate_id AS source_id, cp.certificate_no
        FROM certificate_print cp
        INNER JOIN (
            SELECT customer_id, service_id, MAX(certificate_id) AS latest_certificate_id
            FROM certificate_print
            WHERE TRIM(COALESCE(certificate_no, '')) <> ''
            GROUP BY customer_id, service_id
        ) latest
            ON latest.latest_certificate_id = cp.certificate_id
        ORDER BY cp.certificate_id DESC
    ";
    $stmt = $db->query($printedCertificateSql);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        acRegisterCertificate(
            $certificateByService,
            $certificateByCustomer,
            $row['customer_id'],
            $row['typed_service_id'],
            $row['certificate_no']
        );
    }

    $legacyCertificateSql = "
        SELECT crr.customer_id, crr.ca_service_id AS typed_service_id, crr.crr_id AS source_id, crr.certificateNo AS certificate_no
        FROM cdc_report_review crr
        INNER JOIN (
            SELECT customer_id, ca_service_id, MAX(crr_id) AS latest_crr_id
            FROM cdc_report_review
            WHERE TRIM(COALESCE(certificateNo, '')) <> ''
            GROUP BY customer_id, ca_service_id
        ) latest
            ON latest.latest_crr_id = crr.crr_id
        ORDER BY crr.crr_id DESC
    ";
    $stmt = $db->query($legacyCertificateSql);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        acRegisterCertificate(
            $certificateByService,
            $certificateByCustomer,
            $row['customer_id'],
            $row['typed_service_id'],
            $row['certificate_no']
        );
    }

    // ─── Main query: Services with customer info ───
    $mainSql = "
        SELECT 
            ci.id AS client_id,
            ci.customer_name,
            ci.certification_no,
            ci.contact_person,
            ci.scope,
            ci.aif_nace,
            ci.audit_status AS client_status,
            s.service_id,
            s.service_type,
            s.audit_date,
            s.service_status,
            CASE
                WHEN UPPER(TRIM(s.service_type)) = 'CA'
                    THEN (SELECT MAX(stca.ca_id) FROM service_type_ca stca WHERE stca.service = s.service_id)
                WHEN UPPER(TRIM(s.service_type)) IN ('SA-I', 'SA1', 'SAI')
                    THEN (SELECT MAX(stsai.sai_id) FROM service_type_sai stsai WHERE stsai.service = s.service_id)
                WHEN UPPER(TRIM(s.service_type)) IN ('SA-II', 'SA2', 'SAII')
                    THEN (SELECT MAX(stsaii.saii_id) FROM service_type_saii stsaii WHERE stsaii.service = s.service_id)
                WHEN UPPER(TRIM(s.service_type)) = 'RC'
                    THEN (SELECT MAX(strc.rc_id) FROM service_type_rc strc WHERE strc.service = s.service_id)
                WHEN UPPER(TRIM(s.service_type)) = 'SP'
                    THEN (SELECT MAX(stsp.sp_id) FROM service_type_sp stsp WHERE stsp.service = s.service_id)
                WHEN UPPER(TRIM(s.service_type)) = 'TA'
                    THEN (SELECT MAX(stta.ta_id) FROM service_type_ta stta WHERE stta.service_id = s.service_id)
                ELSE s.service_id
            END AS typed_service_id,
            (
                SELECT MIN(crr.Date)
                FROM cdc_report_review crr
                INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id
                INNER JOIN service s2 ON ca.service = s2.service_id
                WHERE s2.customer_id = ci.id
                  AND s2.service_type = 'CA'
                  AND crr.Date IS NOT NULL
                  AND crr.Date <> ''
                  AND crr.Date <> '0000-00-00'
            ) AS original_registration_date
        FROM service s
        JOIN customer_info ci ON ci.id = s.customer_id
        WHERE ci.is_deleted <> 'YES'
          AND UPPER(TRIM(s.service_type)) NOT IN ('CR', 'CONTRACT REVIEW', 'PTR')
          AND COALESCE(LOWER(REPLACE(REPLACE(TRIM(ci.audit_status), '-', ''), ' ', '')), '') <> 'inprocess'
        ORDER BY s.audit_date DESC, ci.customer_name ASC
    ";
    $stmt = $db->prepare($mainSql);
    $stmt->execute();
    $allRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div id="page-content-wrapper">
        <div class="auditor-checklist-page">
            <!-- ═══ Page Header ═══ -->
            <div class="ac-page-header">
                <div class="ac-header-left">
                    <div class="ac-header-icon">
                        <i class="fa fa-clipboard"></i>
                    </div>
                    <div class="ac-header-text">
                        <h1>Auditor Checklist</h1>
                        <p>Comprehensive audit records across all clients and auditors</p>
                    </div>
                </div>
                <div class="ac-header-stats">
                    <div class="ac-stat-card">
                        <span class="ac-stat-number"><?php echo count($allRecords); ?></span>
                        <span class="ac-stat-label">Total Records</span>
                    </div>
                    <div class="ac-stat-card ac-stat-auditor">
                        <span class="ac-stat-number"><?php echo count($auditorsList); ?></span>
                        <span class="ac-stat-label">Auditors</span>
                    </div>
                </div>
            </div>

            <!-- ═══ Filter Panel ═══ -->
            <div class="ac-filter-panel">
                <div class="ac-filter-header">
                    <i class="fa fa-filter"></i>
                    <span>Advanced Filters</span>
                    <button type="button" class="ac-filter-reset" id="acResetFilters" title="Reset All Filters">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
                <div class="ac-filter-grid">
                    <!-- Filter by Service Type -->
                    <div class="ac-filter-item">
                        <label for="filterServiceType"><i class="fa fa-cogs"></i> Service Type</label>
                        <select id="filterServiceType" class="ac-filter-select">
                            <option value="">All Service Types</option>
                            <?php foreach ($serviceTypeList as $stype) { 
                                $sTypeName = htmlspecialchars($stype['service_type'] ?? '');
                                if ($sTypeName === '') continue;
                            ?>
                                <option value="<?php echo strtolower($sTypeName); ?>">
                                    <?php echo $sTypeName; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Filter by Certificate Number -->
                    <div class="ac-filter-item">
                        <label for="filterCertNo"><i class="fa fa-certificate"></i> Certificate Number</label>
                        <input type="text" id="filterCertNo" class="ac-filter-input"
                            placeholder="Type certificate no..." autocomplete="off">
                    </div>

                    <!-- Filter by IAF/NACE -->
                    <div class="ac-filter-item">
                        <label for="filterIafNace"><i class="fa fa-tags"></i> IAF/NACE</label>
                        <input type="text" id="filterIafNace" class="ac-filter-input"
                            placeholder="Type IAF/NACE code..." autocomplete="off">
                    </div>

                    <!-- Filter by Auditor -->
                    <div class="ac-filter-item">
                        <label for="filterAuditor"><i class="fa fa-user"></i> Auditor</label>
                        <select id="filterAuditor" class="ac-filter-select">
                            <option value="">All Auditors</option>
                            <?php foreach ($auditorsList as $aud) { ?>
                                <option value="<?php echo htmlspecialchars($aud['auditor_name']); ?>">
                                    <?php echo htmlspecialchars($aud['auditor_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Filter by Accreditation -->
                    <div class="ac-filter-item">
                        <label for="filterAccreditation"><i class="fa fa-shield"></i> Accreditation</label>
                        <select id="filterAccreditation" class="ac-filter-select">
                            <option value="">All</option>
                            <option value="SELF">SELF</option>
                            <option value="NABCB">NABCB</option>
                        </select>
                    </div>

                    <!-- Filter by Status -->
                    <div class="ac-filter-item">
                        <label for="filterStatus"><i class="fa fa-info-circle"></i> Status</label>
                        <select id="filterStatus" class="ac-filter-select">
                            <option value="">All Status</option>
                            <?php foreach ($statusList as $st) { ?>
                                <option value="<?php echo htmlspecialchars($st['audit_status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($st['audit_status'])); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Filter by Date Range -->
                    <div class="ac-filter-item ac-filter-date-range">
                        <label><i class="fa fa-calendar"></i> Audit Date Range</label>
                        <div class="ac-date-inputs">
                            <input type="date" id="filterDateFrom" class="ac-filter-input ac-date-input"
                                title="From Date">
                            <span class="ac-date-separator">to</span>
                            <input type="date" id="filterDateTo" class="ac-filter-input ac-date-input" title="To Date">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Results Count ═══ -->
            <div class="ac-results-bar">
                <span id="acResultCount" class="ac-result-text">
                    Showing <strong><?php echo count($allRecords); ?></strong> records
                </span>
                <button class="ac-export-btn" id="acExportBtn" title="Export to Excel">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
            </div>

            <!-- ═══ Data Table ═══ -->
            <div class="ac-table-wrapper">
                <div class="ac-table-container" id="acTableContainer">
                    <table class="ac-table" id="auditorChecklistTable">
                        <thead>
                            <tr>
                                <th class="ac-th-sno">#</th>
                                <th class="ac-th-client">Client Name</th>
                                <th class="ac-th-cert">Certificate No.</th>
                                <th class="ac-th-contact">Contact Person</th>
                                <th class="ac-th-orig-reg">Original Registration Date</th>
                                <th class="ac-th-accred">Accreditation</th>
                                <th class="ac-th-scope">Scope</th>
                                <th class="ac-th-iaf">IAF/NACE</th>
                                <th class="ac-th-auditor">Auditor Name</th>
                                <th class="ac-th-type">Service Type</th>
                                <th class="ac-th-date">Audit Date</th>
                                <th class="ac-th-status">Status</th>
                            </tr>
                        </thead>
                        <tbody id="auditorChecklistBody">
                            <?php
                            $sno = 0;
                            foreach ($allRecords as $record) {
                                $sno++;
                                $clientId = trim((string) ($record['client_id'] ?? ''));
                                $serviceId = trim((string) ($record['service_id'] ?? ''));
                                $typedServiceId = trim((string) ($record['typed_service_id'] ?? ''));
                                $auditTypeRaw = trim((string) ($record['service_type'] ?? ''));

                                $clientName = htmlspecialchars($record['customer_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                $contactPerson = htmlspecialchars($record['contact_person'] ?? '');
                                $scope = htmlspecialchars($record['scope'] ?? '');
                                $iafNace = htmlspecialchars($record['aif_nace'] ?? '');

                                // CA certificates are stored per CA workflow in CDC office-use.
                                // Keep the existing master certificate behavior for every other audit type.
                                $certNoRaw = trim((string) ($record['certification_no'] ?? ''));
                                if (strtoupper($auditTypeRaw) === 'CA') {
                                    $serviceCertificateKey = $clientId . ':' . $typedServiceId;
                                    if ($typedServiceId !== '' && isset($certificateByService[$serviceCertificateKey])) {
                                        $certNoRaw = $certificateByService[$serviceCertificateKey];
                                    } elseif ($certNoRaw === '' && isset($certificateByCustomer[$clientId])) {
                                        $certNoRaw = $certificateByCustomer[$clientId];
                                    }
                                }
                                $certificateNumbers = array_values(array_unique(array_filter(array_map('trim', explode('||', $certNoRaw)))));
                                $certSearchValue = strtolower(implode(' ', $certificateNumbers));

                                // Auditor from preloaded map
                                $auditorName = isset($auditorMap[$serviceId]) ? htmlspecialchars(trim($auditorMap[$serviceId], ', ')) : '-';

                                $auditType = htmlspecialchars($auditTypeRaw, ENT_QUOTES, 'UTF-8');
                                $rawDate = $record['audit_date'] ?? '';
                                $auditDate = ($rawDate && $rawDate !== '0000-00-00') ? date('d.m.Y', strtotime($rawDate)) : '-';

                                $rawOrigRegDate = $record['original_registration_date'] ?? '';
                                $origRegDate = ($rawOrigRegDate && $rawOrigRegDate !== '0000-00-00') ? date('d.m.Y', strtotime($rawOrigRegDate)) : '-';

                                // Accreditation from preloaded map (per customer)
                                $accreditation = isset($accredMap[$clientId]) ? htmlspecialchars($accredMap[$clientId]) : '-';

                                $clientStatus = htmlspecialchars($record['client_status'] ?? '');

                                // Status badge class
                                $statusClass = 'ac-status-default';
                                $statusLower = strtolower($clientStatus);
                                if (strpos($statusLower, 'active') !== false) {
                                    $statusClass = 'ac-status-active';
                                } elseif (strpos($statusLower, 'withdrawn') !== false || strpos($statusLower, 'expire') !== false) {
                                    $statusClass = 'ac-status-withdrawn';
                                } elseif (strpos($statusLower, 'suspend') !== false) {
                                    $statusClass = 'ac-status-suspended';
                                } elseif (strpos($statusLower, 'inprocess') !== false || strpos($statusLower, 'in-process') !== false) {
                                    $statusClass = 'ac-status-inprocess';
                                }

                                // Audit type badge class
                                $typeClass = 'ac-type-default';
                                $typeLower = strtolower($auditType);
                                if ($typeLower === 'ca')
                                    $typeClass = 'ac-type-ca';
                                elseif ($typeLower === 'sa-i')
                                    $typeClass = 'ac-type-sai';
                                elseif ($typeLower === 'sa-ii')
                                    $typeClass = 'ac-type-saii';
                                elseif ($typeLower === 'rc')
                                    $typeClass = 'ac-type-rc';
                                elseif ($typeLower === 'sp')
                                    $typeClass = 'ac-type-sp';
                                ?>
                                <tr class="ac-data-row" data-client="<?php echo strtolower($clientName); ?>"
                                    data-cert="<?php echo htmlspecialchars($certSearchValue, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-iaf="<?php echo strtolower($iafNace); ?>"
                                    data-auditor="<?php echo strtolower($auditorName); ?>"
                                    data-accreditation="<?php echo strtolower($accreditation); ?>"
                                    data-status="<?php echo strtolower($clientStatus); ?>"
                                    data-date="<?php echo $rawDate; ?>"
                                    data-service-type="<?php echo strtolower($auditType); ?>">
                                    <td class="ac-td-sno"><?php echo $sno; ?></td>
                                    <td class="ac-td-client" title="<?php echo $clientName; ?>"><?php echo $clientName; ?>
                                    </td>
                                    <td class="ac-td-cert">
                                        <?php if (!empty($certificateNumbers)) { ?>
                                            <div class="ac-cert-list">
                                                <?php foreach ($certificateNumbers as $certificateNumber) { ?>
                                                    <span
                                                        class="ac-cert-badge"><?php echo htmlspecialchars($certificateNumber, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="ac-no-data">-</span>
                                        <?php } ?>
                                    </td>
                                    <td class="ac-td-contact"><?php echo $contactPerson ?: '-'; ?></td>
                                    <td class="ac-td-orig-reg"><?php echo $origRegDate; ?></td>
                                    <td class="ac-td-accred">
                                        <?php if ($accreditation !== '-') { ?>
                                            <span
                                                class="ac-accred-badge <?php echo ($accreditation === 'NABCB') ? 'ac-accred-nabcb' : 'ac-accred-self'; ?>">
                                                <?php echo $accreditation; ?>
                                            </span>
                                        <?php } else {
                                            echo '-';
                                        } ?>
                                    </td>
                                    <td class="ac-td-scope" title="<?php echo $scope; ?>"><?php echo $scope ?: '-'; ?></td>
                                    <td class="ac-td-iaf"><?php echo $iafNace ?: '-'; ?></td>
                                    <td class="ac-td-auditor">
                                        <?php if ($auditorName !== '-') { ?>
                                            <span class="ac-auditor-chip"><i class="fa fa-user-circle"></i>
                                                <?php echo $auditorName; ?></span>
                                        <?php } else {
                                            echo '<span class="ac-no-data">-</span>';
                                        } ?>
                                    </td>
                                    <td class="ac-td-type"><span
                                            class="ac-type-badge <?php echo $typeClass; ?>"><?php echo $auditType; ?></span>
                                    </td>
                                    <td class="ac-td-date"><?php echo $auditDate; ?></td>
                                    <td class="ac-td-status"><span
                                            class="ac-status-badge <?php echo $statusClass; ?>"><?php echo $clientStatus ?: '-'; ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Scroll indicator -->
                <div class="ac-scroll-hint" id="acScrollHint">
                    <i class="fa fa-arrows-h"></i> Scroll horizontally to see all columns
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ═══════════════════════════════════════════════════════════
   AUDITOR CHECKLIST PAGE — Premium Styles with Dark Theme
   ═══════════════════════════════════════════════════════════ */

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .auditor-checklist-page {
        padding: 20px 24px 40px 24px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        margin-top: 72px;
        min-height: calc(100vh - 72px);
        background: linear-gradient(145deg, #f0f4f8 0%, #e2e8f0 50%, #dbeafe 100%);
    }

    /* ── Page Header ── */
    .ac-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .ac-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ac-header-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        flex-shrink: 0;
    }

    .ac-header-icon i {
        font-size: 24px;
        color: #fff;
    }

    .ac-header-text h1 {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .ac-header-text p {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0 0;
        font-weight: 500;
    }

    .ac-header-stats {
        display: flex;
        gap: 10px;
    }

    .ac-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 12px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        min-width: 90px;
    }

    .ac-stat-number {
        font-size: 22px;
        font-weight: 800;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .ac-stat-auditor .ac-stat-number {
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .ac-stat-label {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 2px;
    }

    /* ── Filter Panel ── */
    .ac-filter-panel {
        background: #fff;
        border-radius: 14px;
        margin-bottom: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .ac-filter-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.3px;
    }

    .ac-filter-header i {
        font-size: 15px;
    }

    .ac-filter-reset {
        margin-left: auto;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        padding: 5px 14px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .ac-filter-reset:hover {
        background: rgba(255, 255, 255, 0.32);
        transform: translateY(-1px);
    }

    .ac-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        padding: 18px 20px;
    }

    .ac-filter-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .ac-filter-item label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ac-filter-item label i {
        font-size: 12px;
        color: #6366f1;
    }

    .ac-filter-input,
    .ac-filter-select {
        height: 38px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        color: #334155;
        background: #f8fafc;
        transition: all 0.2s ease;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }

    .ac-filter-input:focus,
    .ac-filter-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        background: #fff;
    }

    .ac-filter-input::placeholder {
        color: #94a3b8;
        font-size: 12px;
    }

    .ac-filter-date-range {
        grid-column: span 2;
    }

    .ac-date-inputs {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ac-date-input {
        flex: 1;
    }

    .ac-date-separator {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    /* ── Results Bar ── */
    .ac-results-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 0 2px;
    }

    .ac-result-text {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .ac-result-text strong {
        color: #6366f1;
        font-weight: 700;
    }

    .ac-export-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
        border: none;
        padding: 9px 18px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        font-family: inherit;
    }

    .ac-export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .ac-export-btn:disabled {
        cursor: wait;
        opacity: 0.72;
        transform: none;
        box-shadow: none;
    }

    /* ── Table Wrapper & Scrolling ── */
    .ac-table-wrapper {
        position: relative;
    }

    .ac-table-container {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        overflow-y: auto;
        max-height: calc(100vh - 280px);
        width: 100%;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
    }

    .ac-scroll-hint {
        text-align: center;
        padding: 8px 0 0;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        animation: acPulseHint 2s ease-in-out infinite;
    }

    @keyframes acPulseHint {

        0%,
        100% {
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }
    }

    /* Custom scrollbar */
    .ac-table-container::-webkit-scrollbar {
        height: 10px;
    }

    .ac-table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 0 0 14px 14px;
    }

    .ac-table-container::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #94a3b8, #6366f1);
        border-radius: 10px;
        border: 2px solid #f1f5f9;
    }

    .ac-table-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }

    /* ── Data Table ── */
    .ac-table {
        width: 100%;
        min-width: 1400px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .ac-table thead tr {
        /* Gradient on TR might not work with sticky TH, setting TH background instead */
    }

    .ac-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 14px 12px;
        text-align: left;
        border-bottom: none;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .ac-th-sno {
        width: 45px;
        text-align: center;
    }

    .ac-th-client {
        min-width: 200px;
    }

    .ac-th-cert {
        min-width: 140px;
    }

    .ac-th-contact {
        min-width: 150px;
    }

    .ac-th-orig-reg {
        min-width: 140px;
        text-align: center;
    }

    .ac-th-accred {
        min-width: 110px;
        text-align: center;
    }

    .ac-th-scope {
        min-width: 200px;
    }

    .ac-th-iaf {
        min-width: 130px;
    }

    .ac-th-auditor {
        min-width: 200px;
    }

    .ac-th-type {
        min-width: 100px;
        text-align: center;
    }

    .ac-th-date {
        min-width: 110px;
    }

    .ac-th-status {
        min-width: 110px;
        text-align: center;
    }

    .ac-table tbody tr {
        transition: all 0.12s ease;
    }

    .ac-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%) !important;
    }

    .ac-table tbody tr:nth-child(even) {
        background: #fafbfd;
    }

    .ac-table tbody td {
        padding: 10px 12px;
        color: #334155;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .ac-td-sno {
        font-weight: 700;
        color: #94a3b8;
        text-align: center;
        font-size: 12px;
    }

    .ac-td-client {
        font-weight: 600;
        color: #1e293b;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ac-td-contact {
        font-size: 12px;
        color: #475569;
    }

    .ac-td-orig-reg {
        text-align: center;
        font-size: 12px;
        color: #475569;
    }

    .ac-td-scope {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        color: #64748b;
    }

    .ac-td-iaf {
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        font-size: 12px;
        color: #6366f1;
        font-weight: 500;
    }

    .ac-td-accred {
        text-align: center;
    }

    .ac-td-type {
        text-align: center;
    }

    .ac-td-status {
        text-align: center;
    }

    .ac-no-data {
        color: #cbd5e1;
        font-weight: 500;
    }

    .ac-cert-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        min-width: 0;
    }

    .ac-cert-badge {
        background: #eff6ff;
        color: #2563eb;
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        overflow-wrap: anywhere;
    }

    /* ── Accreditation Badges ── */
    .ac-accred-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: inline-block;
    }

    .ac-accred-nabcb {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
        border: 1px solid #93c5fd;
    }

    .ac-accred-self {
        background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%);
        color: #a16207;
        border: 1px solid #fcd34d;
    }

    /* ── Auditor Chip ── */
    .ac-auditor-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #15803d;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
        white-space: nowrap;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ac-auditor-chip i {
        font-size: 13px;
        color: #22c55e;
        flex-shrink: 0;
    }

    /* ── Audit Type Badges ── */
    .ac-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ac-type-ca {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .ac-type-sai {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .ac-type-saii {
        background: #ffedd5;
        color: #9a3412;
        border: 1px solid #fdba74;
    }

    .ac-type-rc {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #a5b4fc;
    }

    .ac-type-sp {
        background: #fce7f3;
        color: #9d174d;
        border: 1px solid #f9a8d4;
    }

    .ac-type-default {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    /* ── Status Badges ── */
    .ac-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        text-transform: capitalize;
        letter-spacing: 0.3px;
    }

    .ac-status-active {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
        border: 1px solid #86efac;
    }

    .ac-status-withdrawn {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .ac-status-suspended {
        background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
        color: #9a3412;
        border: 1px solid #fdba74;
    }

    .ac-status-inprocess {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #3730a3;
        border: 1px solid #a5b4fc;
    }

    .ac-status-default {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    /* ═══ Responsive ═══ */
    @media (max-width: 1200px) {
        .ac-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .ac-filter-date-range {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .auditor-checklist-page {
            padding: 14px 10px 30px 10px;
        }

        .ac-page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .ac-filter-grid {
            grid-template-columns: 1fr;
        }

        .ac-filter-date-range {
            grid-column: span 1;
        }
    }

    /* ═══ Dark Theme ═══ */
    .theme-dark .auditor-checklist-page {
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    }

    .theme-dark .ac-header-text h1 {
        color: #f1f5f9;
    }

    .theme-dark .ac-header-text p {
        color: #94a3b8;
    }

    .theme-dark .ac-stat-card {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .theme-dark .ac-stat-label {
        color: #64748b;
    }

    .theme-dark .ac-filter-panel {
        background: #1e293b;
        border-color: #334155;
    }

    .theme-dark .ac-filter-item label {
        color: #94a3b8;
    }

    .theme-dark .ac-filter-input,
    .theme-dark .ac-filter-select {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    .theme-dark .ac-filter-input:focus,
    .theme-dark .ac-filter-select:focus {
        border-color: #6366f1;
        background: #1e293b;
    }

    .theme-dark .ac-filter-input::placeholder {
        color: #475569;
    }

    .theme-dark .ac-result-text {
        color: #94a3b8;
    }

    .theme-dark .ac-table-container {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        scrollbar-color: #6366f1 #0f172a;
    }

    .theme-dark .ac-table-container::-webkit-scrollbar-track {
        background: #0f172a;
    }

    .theme-dark .ac-table-container::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        border-color: #0f172a;
    }

    .theme-dark .ac-table thead tr {
        background: linear-gradient(90deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    }

    .theme-dark .ac-table thead th {
        color: #94a3b8;
    }

    .theme-dark .ac-table tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.015);
    }

    .theme-dark .ac-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.06) !important;
    }

    .theme-dark .ac-table tbody td {
        color: #cbd5e1;
        border-bottom-color: rgba(255, 255, 255, 0.04);
    }

    .theme-dark .ac-td-client {
        color: #f1f5f9;
    }

    .theme-dark .ac-td-contact {
        color: #94a3b8;
    }

    .theme-dark .ac-td-orig-reg {
        color: #94a3b8;
    }

    .theme-dark .ac-td-iaf {
        color: #818cf8;
    }

    .theme-dark .ac-cert-badge {
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
    }

    .theme-dark .ac-accred-nabcb {
        background: rgba(59, 130, 246, 0.12);
        color: #60a5fa;
        border-color: rgba(59, 130, 246, 0.25);
    }

    .theme-dark .ac-accred-self {
        background: rgba(251, 191, 36, 0.1);
        color: #fbbf24;
        border-color: rgba(251, 191, 36, 0.25);
    }

    .theme-dark .ac-auditor-chip {
        background: rgba(34, 197, 94, 0.08);
        color: #4ade80;
        border-color: rgba(34, 197, 94, 0.2);
    }

    .theme-dark .ac-auditor-chip i {
        color: #4ade80;
    }

    .theme-dark .ac-type-ca {
        background: rgba(22, 163, 74, 0.1);
        color: #4ade80;
        border-color: rgba(22, 163, 74, 0.25);
    }

    .theme-dark .ac-type-sai {
        background: rgba(251, 191, 36, 0.1);
        color: #fbbf24;
        border-color: rgba(251, 191, 36, 0.25);
    }

    .theme-dark .ac-type-saii {
        background: rgba(249, 115, 22, 0.1);
        color: #fb923c;
        border-color: rgba(249, 115, 22, 0.25);
    }

    .theme-dark .ac-type-rc {
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8;
        border-color: rgba(99, 102, 241, 0.25);
    }

    .theme-dark .ac-type-sp {
        background: rgba(236, 72, 153, 0.1);
        color: #f472b6;
        border-color: rgba(236, 72, 153, 0.25);
    }

    .theme-dark .ac-type-default {
        background: rgba(100, 116, 139, 0.1);
        color: #94a3b8;
        border-color: rgba(100, 116, 139, 0.2);
    }

    .theme-dark .ac-status-active {
        background: rgba(22, 163, 74, 0.12);
        color: #4ade80;
        border-color: rgba(22, 163, 74, 0.25);
    }

    .theme-dark .ac-status-withdrawn {
        background: rgba(239, 68, 68, 0.1);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.25);
    }

    .theme-dark .ac-status-suspended {
        background: rgba(249, 115, 22, 0.1);
        color: #fb923c;
        border-color: rgba(249, 115, 22, 0.25);
    }

    .theme-dark .ac-status-inprocess {
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8;
        border-color: rgba(99, 102, 241, 0.25);
    }

    .theme-dark .ac-status-default {
        background: rgba(100, 116, 139, 0.1);
        color: #94a3b8;
        border-color: rgba(100, 116, 139, 0.2);
    }

    .theme-dark .ac-scroll-hint {
        color: #475569;
    }

    .theme-dark .ac-no-data {
        color: #475569;
    }

    /* ── Row enter animation ── */
    @keyframes acRowFadeIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ac-data-row {
        animation: acRowFadeIn 0.2s ease forwards;
    }
</style>

<script>
    $(document).ready(function () {
        var $rows = $('#auditorChecklistBody tr.ac-data-row');
        var $resultCount = $('#acResultCount');
        var totalRows = $rows.length;

        // Hide scroll hint after first scroll
        var $container = $('#acTableContainer');
        var $scrollHint = $('#acScrollHint');
        $container.on('scroll', function () {
            if ($(this).scrollLeft() > 10) {
                $scrollHint.fadeOut(300);
            }
        });

        // Filter function
        function applyFilters() {
            var serviceTypeVal = $('#filterServiceType').val().toLowerCase().trim();
            var certVal = $('#filterCertNo').val().toLowerCase().trim();
            var iafVal = $('#filterIafNace').val().toLowerCase().trim();
            var auditorVal = $('#filterAuditor').val().toLowerCase().trim();
            var accredVal = $('#filterAccreditation').val().toLowerCase().trim();
            var statusVal = $('#filterStatus').val().toLowerCase().trim();
            var dateFrom = $('#filterDateFrom').val();
            var dateTo = $('#filterDateTo').val();

            var visibleCount = 0;
            var sno = 0;

            $rows.each(function () {
                var $row = $(this);
                var show = true;

                if (serviceTypeVal && $row.attr('data-service-type') !== serviceTypeVal) show = false;
                if (show && certVal && $row.attr('data-cert').indexOf(certVal) === -1) show = false;
                if (show && iafVal && $row.attr('data-iaf').indexOf(iafVal) === -1) show = false;
                if (show && auditorVal && $row.attr('data-auditor').indexOf(auditorVal) === -1) show = false;
                if (show && accredVal && $row.attr('data-accreditation') !== accredVal) show = false;
                if (show && statusVal && $row.attr('data-status') !== statusVal) show = false;

                if (show && (dateFrom || dateTo)) {
                    var rowDate = $row.attr('data-date');
                    if (rowDate && rowDate !== '' && rowDate !== '0000-00-00') {
                        if (dateFrom && rowDate < dateFrom) show = false;
                        if (dateTo && rowDate > dateTo) show = false;
                    } else if (dateFrom || dateTo) {
                        show = false;
                    }
                }

                if (show) {
                    visibleCount++;
                    sno++;
                    $row.show();
                    $row.find('.ac-td-sno').text(sno);
                } else {
                    $row.hide();
                }
            });

            $resultCount.html('Showing <strong>' + visibleCount + '</strong> of <strong>' + totalRows + '</strong> records');
        }

        // Bind filter events
        $('#filterCertNo, #filterIafNace').on('keyup input', function () {
            applyFilters();
        });

        $('#filterServiceType, #filterAuditor, #filterAccreditation, #filterStatus').on('change', function () {
            applyFilters();
        });

        $('#filterDateFrom, #filterDateTo').on('change', function () {
            applyFilters();
        });

        // Reset filters
        $('#acResetFilters').on('click', function () {
            $('#filterServiceType').val('');
            $('#filterCertNo').val('');
            $('#filterIafNace').val('');
            $('#filterAuditor').val('');
            $('#filterAccreditation').val('');
            $('#filterStatus').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            applyFilters();
            $scrollHint.fadeIn(200);
        });

        function cleanExportText(value) {
            value = $.trim((value || '').replace(/\s+/g, ' '));
            return value === '-' ? '' : value;
        }

        function rowExportData($row) {
            var certificates = [];
            $row.find('.ac-cert-badge').each(function () {
                var certificate = cleanExportText($(this).text());
                if (certificate) {
                    certificates.push(certificate);
                }
            });

            return {
                client: cleanExportText($row.find('.ac-td-client').text()),
                certificate_no: certificates.join(', '),
                contact_person: cleanExportText($row.find('.ac-td-contact').text()),
                original_registration: cleanExportText($row.find('.ac-td-orig-reg').text()),
                accreditation: cleanExportText($row.find('.ac-td-accred').text()),
                scope: cleanExportText($row.find('.ac-td-scope').text()),
                iaf_nace: cleanExportText($row.find('.ac-td-iaf').text()),
                auditor: cleanExportText($row.find('.ac-td-auditor').text()),
                audit_type: cleanExportText($row.find('.ac-td-type').text()),
                audit_date: cleanExportText($row.find('.ac-td-date').text()),
                status: cleanExportText($row.find('.ac-td-status').text())
            };
        }

        // Export the currently visible checklist rows as a formatted native XLSX workbook.
        $('#acExportBtn').on('click', function () {
            var $button = $(this);
            var visibleRows = [];

            $rows.filter(':visible').each(function () {
                visibleRows.push(rowExportData($(this)));
            });

            if (!visibleRows.length) {
                alert('No visible records are available to export.');
                return;
            }

            var payload = {
                rows: visibleRows,
                filters: {
                    serviceType: $('#filterServiceType').val(),
                    certificateNo: $('#filterCertNo').val(),
                    iafNace: $('#filterIafNace').val(),
                    auditor: $('#filterAuditor').val(),
                    accreditation: $('#filterAccreditation').val(),
                    status: $('#filterStatus').val(),
                    dateFrom: $('#filterDateFrom').val(),
                    dateTo: $('#filterDateTo').val()
                }
            };

            var originalButtonHtml = $button.html();
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing Excel');

            fetch('exportAuditorChecklistExcel.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                },
                body: JSON.stringify(payload)
            }).then(function (response) {
                if (!response.ok) {
                    return response.text().then(function (responseText) {
                        var message = 'Excel report could not be generated.';
                        try {
                            var errorData = JSON.parse(responseText);
                            if (errorData.message) {
                                message = errorData.message;
                            }
                        } catch (ignore) { }
                        throw new Error(message);
                    });
                }

                var disposition = response.headers.get('Content-Disposition') || '';
                var fileNameMatch = disposition.match(/filename="?([^";]+)"?/i);
                var fileName = fileNameMatch ? fileNameMatch[1] : 'Auditor_Checklist_Report.xlsx';

                return response.blob().then(function (blob) {
                    return { blob: blob, fileName: fileName };
                });
            }).then(function (download) {
                var url = URL.createObjectURL(download.blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = download.fileName;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.setTimeout(function () {
                    URL.revokeObjectURL(url);
                }, 1000);
            }).catch(function (error) {
                alert(error.message || 'Excel report could not be generated.');
            }).then(function () {
                $button.prop('disabled', false).html(originalButtonHtml);
            });
        });
    });
</script>