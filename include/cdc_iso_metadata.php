<?php

if (!function_exists('ensureCdcIsoMetadataTable')) {
    function ensureCdcIsoMetadataTable(PDO $db): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `cdc_iso_metadata` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `crr_id` INT(11) NOT NULL,
                `iso_index` INT(11) NOT NULL,
                `iso_label` VARCHAR(255) DEFAULT NULL,
                `accreditation` VARCHAR(20) DEFAULT NULL,
                `certification_status` VARCHAR(50) DEFAULT NULL,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_cdc_iso_metadata` (`crr_id`, `iso_index`),
                KEY `idx_cdc_iso_metadata_crr` (`crr_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );

        $ensured = true;
    }
}

if (!function_exists('getCdcIsoMetadata')) {
    function getCdcIsoMetadata(PDO $db, int $crrId): array
    {
        ensureCdcIsoMetadataTable($db);

        $statement = $db->prepare(
            "SELECT iso_index, iso_label, accreditation, certification_status
             FROM cdc_iso_metadata
             WHERE crr_id = :crr_id
             ORDER BY iso_index ASC"
        );
        $statement->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: array();
    }
}

if (!function_exists('upsertCdcIsoMetadata')) {
    function upsertCdcIsoMetadata(PDO $db, int $crrId, array $entries): void
    {
        ensureCdcIsoMetadataTable($db);

        $statement = $db->prepare(
            "INSERT INTO cdc_iso_metadata
                (crr_id, iso_index, iso_label, accreditation)
             VALUES
                (:crr_id, :iso_index, :iso_label, :accreditation)
             ON DUPLICATE KEY UPDATE
                iso_label = VALUES(iso_label),
                accreditation = VALUES(accreditation)"
        );

        foreach ($entries as $index => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $isoIndex = isset($entry['iso_index']) ? (int) $entry['iso_index'] : (int) $index;
            $isoLabel = trim((string) ($entry['iso_label'] ?? ''));
            $accreditation = strtoupper(trim((string) ($entry['accreditation'] ?? '')));

            if ($isoIndex < 0 || $isoLabel === '' || !in_array($accreditation, array('NABCB', 'SELF'), true)) {
                continue;
            }

            $statement->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
            $statement->bindValue(':iso_index', $isoIndex, PDO::PARAM_INT);
            $statement->bindValue(':iso_label', $isoLabel);
            $statement->bindValue(':accreditation', $accreditation);
            $statement->execute();
        }
    }
}
