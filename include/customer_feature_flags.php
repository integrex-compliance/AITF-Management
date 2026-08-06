<?php

if (!function_exists('aitfCustomerFeatureDefaults')) {
    function aitfCustomerFeatureDefaults()
    {
        return array(
            'customer_docora_ai_visible' => 1,
            'customer_date_graph_visible' => 1,
            'customer_letters_edit_icon_visible' => 0,
            'customer_letters_delete_icon_visible' => 0,
            'audit_date_conflict_visible' => 0,
            'audit_member_conflict_visible' => 0,
            'customer_manual_status_change_enabled' => 0,
            'customer_accreditation_change_enabled' => 0
        );
    }
}

if (!function_exists('aitfCustomerFeatureEnsureSchema')) {
    function aitfCustomerFeatureEnsureSchema($db)
    {
        if (!$db) {
            return;
        }

        $db->exec("CREATE TABLE IF NOT EXISTS app_feature_settings (
            setting_key VARCHAR(120) NOT NULL,
            setting_value VARCHAR(20) NOT NULL DEFAULT '1',
            updated_by VARCHAR(120) DEFAULT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        foreach (aitfCustomerFeatureDefaults() as $key => $enabled) {
            $stmt = $db->prepare("INSERT IGNORE INTO app_feature_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)");
            $stmt->execute(array(
                ':setting_key' => $key,
                ':setting_value' => $enabled ? '1' : '0'
            ));
        }
    }
}

if (!function_exists('aitfCustomerFeatureIsEnabled')) {
    function aitfCustomerFeatureIsEnabled($db, $key)
    {
        $defaults = aitfCustomerFeatureDefaults();
        $default = isset($defaults[$key]) ? (int) $defaults[$key] : 0;

        if (!$db) {
            return $default === 1;
        }

        try {
            aitfCustomerFeatureEnsureSchema($db);
            $stmt = $db->prepare("SELECT setting_value FROM app_feature_settings WHERE setting_key = :setting_key LIMIT 1");
            $stmt->execute(array(':setting_key' => $key));
            $value = $stmt->fetchColumn();
            if ($value === false) {
                return $default === 1;
            }
            return (string) $value === '1';
        } catch (Exception $ex) {
            error_log('[Customer Feature Flags] ' . $ex->getMessage());
            return $default === 1;
        }
    }
}

if (!function_exists('aitfCustomerFeatureSet')) {
    function aitfCustomerFeatureSet($db, $key, $enabled, $updatedBy = '')
    {
        $defaults = aitfCustomerFeatureDefaults();
        if (!$db || !isset($defaults[$key])) {
            return false;
        }

        aitfCustomerFeatureEnsureSchema($db);
        $stmt = $db->prepare("INSERT INTO app_feature_settings (setting_key, setting_value, updated_by)
            VALUES (:setting_key, :setting_value, :updated_by)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)");
        $stmt->execute(array(
            ':setting_key' => $key,
            ':setting_value' => $enabled ? '1' : '0',
            ':updated_by' => $updatedBy
        ));

        return true;
    }
}
