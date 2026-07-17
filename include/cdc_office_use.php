<?php

/**
 * Utility helpers for managing repeated CDC office-use entries per ISO.
 */

if (!function_exists('ensureCdcOfficeUseTable')) {
    function ensureCdcOfficeUseTable(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `cdc_report_review_office_use` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `crr_id` INT(11) NOT NULL,
                `iso_index` INT(11) NOT NULL,
                `iso_label` VARCHAR(255) DEFAULT NULL,
                `certificate_no` VARCHAR(255) DEFAULT NULL,
                `rev_no` VARCHAR(100) DEFAULT NULL,
                `rev_date` DATE DEFAULT NULL,
                `expire_date` DATE DEFAULT NULL,
                `periodical_audit` DATE DEFAULT NULL,
                `audit_type` VARCHAR(50) DEFAULT NULL,
                `comments` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_cdc_office_use_crr` (`crr_id`),
                KEY `idx_cdc_office_use_iso_index` (`iso_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );
    }
}

if (!function_exists('getCdcOfficeUse')) {
    function getCdcOfficeUse(PDO $db, int $crrId): array
    {
        ensureCdcOfficeUseTable($db);

        $statement = $db->prepare(
            "SELECT iso_index, iso_label, certificate_no, rev_no, rev_date, expire_date, periodical_audit, audit_type, comments
             FROM cdc_report_review_office_use
             WHERE crr_id = :crr_id
             ORDER BY iso_index ASC, id ASC"
        );
        $statement->bindParam(':crr_id', $crrId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('replaceCdcOfficeUse')) {
    function replaceCdcOfficeUse(PDO $db, int $crrId, array $entries): void
    {
        ensureCdcOfficeUseTable($db);

        $db->beginTransaction();
        try {
            deleteCdcOfficeUse($db, $crrId, false);

            if (!empty($entries)) {
                $insert = $db->prepare(
                    "INSERT INTO cdc_report_review_office_use
                        (crr_id, iso_index, iso_label, certificate_no, rev_no, rev_date, expire_date, periodical_audit, audit_type, comments)
                     VALUES
                        (:crr_id, :iso_index, :iso_label, :certificate_no, :rev_no, :rev_date, :expire_date, :periodical_audit, :audit_type, :comments)"
                );

                foreach ($entries as $entry) {
                    $isoIndex = isset($entry['iso_index']) ? (int) $entry['iso_index'] : 0;
                    $isoLabel = $entry['iso_label'] ?? null;
                    $certificateNo = $entry['certificate_no'] ?? null;
                    $revNo = $entry['rev_no'] ?? null;
                    $revDate = !empty($entry['rev_date']) ? date('Y-m-d', strtotime($entry['rev_date'])) : null;
                    $expireDate = !empty($entry['expire_date']) ? date('Y-m-d', strtotime($entry['expire_date'])) : null;
                    $periodicalAudit = !empty($entry['periodical_audit']) ? date('Y-m-d', strtotime($entry['periodical_audit'])) : null;
                    $auditType = $entry['audit_type'] ?? null;
                    $comments = $entry['comments'] ?? null;

                    $insert->bindParam(':crr_id', $crrId, PDO::PARAM_INT);
                    $insert->bindParam(':iso_index', $isoIndex, PDO::PARAM_INT);
                    $insert->bindParam(':iso_label', $isoLabel);
                    $insert->bindParam(':certificate_no', $certificateNo);
                    $insert->bindParam(':rev_no', $revNo);
                    $insert->bindParam(':rev_date', $revDate);
                    $insert->bindParam(':expire_date', $expireDate);
                    $insert->bindParam(':periodical_audit', $periodicalAudit);
                    $insert->bindParam(':audit_type', $auditType);
                    $insert->bindParam(':comments', $comments);
                    $insert->execute();
                }
            }

            $db->commit();
        } catch (Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }
}

if (!function_exists('deleteCdcOfficeUse')) {
    function deleteCdcOfficeUse(PDO $db, int $crrId, bool $ensureTable = true): void
    {
        if ($ensureTable) {
            ensureCdcOfficeUseTable($db);
        }

        $statement = $db->prepare("DELETE FROM cdc_report_review_office_use WHERE crr_id = :crr_id");
        $statement->bindParam(':crr_id', $crrId, PDO::PARAM_INT);
        $statement->execute();
    }
}

if (!function_exists('normaliseCdcOfficePayload')) {
    function normaliseCdcOfficePayload(array $payload): array
    {
        $normalised = [];
        foreach ($payload as $index => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $normalised[] = [
                'iso_index' => isset($entry['iso_index']) ? (int) $entry['iso_index'] : (int) $index,
                'iso_label' => isset($entry['iso_label']) ? trim((string) $entry['iso_label']) : null,
                'certificate_no' => isset($entry['certificate_no']) ? trim((string) $entry['certificate_no']) : null,
                'rev_no' => isset($entry['rev_no']) ? trim((string) $entry['rev_no']) : null,
                'rev_date' => isset($entry['rev_date']) ? trim((string) $entry['rev_date']) : null,
                'expire_date' => isset($entry['expire_date']) ? trim((string) $entry['expire_date']) : null,
                'periodical_audit' => isset($entry['periodical_audit']) ? trim((string) $entry['periodical_audit']) : null,
                'audit_type' => isset($entry['audit_type']) ? trim((string) $entry['audit_type']) : null,
                'comments' => isset($entry['comments']) ? trim((string) $entry['comments']) : null,
            ];
        }

        return $normalised;
    }
}

if (!function_exists('ensureCdcIsoDecisionsTable')) {
    function ensureCdcIsoDecisionsTable(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `cdc_iso_decisions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `crr_id` INT(11) NOT NULL,
                `cdc_member` TINYINT(3) UNSIGNED NOT NULL,
                `iso_standard` VARCHAR(255) NOT NULL,
                `decision` VARCHAR(20) NOT NULL,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_cdc_iso_decisions_crr` (`crr_id`),
                KEY `idx_cdc_iso_decisions_member` (`cdc_member`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );
    }
}

if (!function_exists('ensureCdcOnlyColumn')) {
    function ensureCdcOnlyColumn(PDO $db): void
    {
        $statement = $db->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'cdc_report_review'
               AND COLUMN_NAME = 'is_cdc_only'"
        );
        $statement->execute();

        if ((int) $statement->fetchColumn() > 0) {
            return;
        }

        try {
            $db->exec(
                "ALTER TABLE `cdc_report_review`
                 ADD COLUMN `is_cdc_only` TINYINT(1) DEFAULT 0 AFTER `cdc_status`"
            );
        } catch (PDOException $exception) {
            // Another request may have added the column after the schema check.
            $mysqlErrorCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : 0;
            if ($mysqlErrorCode !== 1060) {
                throw $exception;
            }
        }
    }
}

if (!function_exists('normaliseCdcIsoDecisions')) {
    function normaliseCdcIsoDecisions(array $decisions): array
    {
        $normalised = [];

        foreach ($decisions as $decision) {
            if (!is_array($decision)) {
                continue;
            }

            $member = isset($decision['cdc_member']) ? (int) $decision['cdc_member'] : 0;
            $isoStandard = isset($decision['iso_standard']) ? trim((string) $decision['iso_standard']) : '';
            $decisionValue = isset($decision['decision']) ? trim((string) $decision['decision']) : '';

            if ($member < 1 || $member > 3 || $isoStandard === '' || !in_array($decisionValue, ['Yes', 'No'], true)) {
                continue;
            }

            $key = $member . '|' . $isoStandard;
            $normalised[$key] = [
                'cdc_member' => $member,
                'iso_standard' => $isoStandard,
                'decision' => $decisionValue,
            ];
        }

        return array_values($normalised);
    }
}

if (!function_exists('replaceCdcIsoDecisions')) {
    function replaceCdcIsoDecisions(PDO $db, int $crrId, array $decisions): void
    {
        ensureCdcIsoDecisionsTable($db);
        $decisions = normaliseCdcIsoDecisions($decisions);

        $db->beginTransaction();
        try {
            $delete = $db->prepare("DELETE FROM cdc_iso_decisions WHERE crr_id = :crr_id");
            $delete->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
            $delete->execute();

            if (!empty($decisions)) {
                $insert = $db->prepare(
                    "INSERT INTO cdc_iso_decisions (crr_id, cdc_member, iso_standard, decision)
                     VALUES (:crr_id, :cdc_member, :iso_standard, :decision)"
                );

                foreach ($decisions as $decision) {
                    $insert->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
                    $insert->bindValue(':cdc_member', $decision['cdc_member'], PDO::PARAM_INT);
                    $insert->bindValue(':iso_standard', $decision['iso_standard']);
                    $insert->bindValue(':decision', $decision['decision']);
                    $insert->execute();
                }
            }

            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }
}

if (!function_exists('getCdcIsoDecisions')) {
    function getCdcIsoDecisions(PDO $db, int $crrId): array
    {
        ensureCdcIsoDecisionsTable($db);

        $statement = $db->prepare(
            "SELECT cdc_member, iso_standard, decision
             FROM cdc_iso_decisions
             WHERE crr_id = :crr_id
             ORDER BY cdc_member ASC, iso_standard ASC"
        );
        $statement->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
