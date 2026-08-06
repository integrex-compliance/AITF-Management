<?php

session_start();
include_once './dbConnect.php';
include_once './include/cdc_iso_metadata.php';

header('Content-Type: application/json; charset=utf-8');

function cdcIsoDetailsResponse($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

if (
    !isset($_SESSION['id']) ||
    empty($_SESSION['id']) ||
    !isset($_SESSION['username']) ||
    $_SESSION['username'] === ''
) {
    cdcIsoDetailsResponse(array('success' => false, 'message' => 'Unauthorized request.'), 401);
}

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    cdcIsoDetailsResponse(array('success' => false, 'message' => 'POST request required.'), 405);
}

$crrId = filter_input(INPUT_POST, 'crr_id', FILTER_VALIDATE_INT);
$customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
$isoIndexInput = filter_input(INPUT_POST, 'iso_index', FILTER_VALIDATE_INT);
$isoIndex = $isoIndexInput === false || $isoIndexInput === null ? -1 : (int) $isoIndexInput;
$isoLabel = trim((string) ($_POST['iso_label'] ?? ''));
$field = trim((string) ($_POST['field'] ?? ''));
$value = trim((string) ($_POST['value'] ?? ''));

if (!$crrId || !$customerId || $isoIndex < 0) {
    cdcIsoDetailsResponse(array('success' => false, 'message' => 'Valid CDC record and ISO index are required.'), 422);
}

$allowedFields = array(
    'accreditation' => 'accreditation',
    'certification_status' => 'certification_status',
);

if (!isset($allowedFields[$field])) {
    cdcIsoDetailsResponse(array('success' => false, 'message' => 'Invalid ISO field.'), 422);
}

if ($field === 'accreditation') {
    $value = strtoupper($value);
    if (!in_array($value, array('NABCB', 'SELF'), true)) {
        cdcIsoDetailsResponse(array('success' => false, 'message' => 'Accreditation must be NABCB or SELF.'), 422);
    }
    
    include_once './include/customer_feature_flags.php';
    if (!function_exists('aitfCustomerFeatureIsEnabled')) {
        $accreditationEnabled = true;
    } else {
        $accreditationEnabled = aitfCustomerFeatureIsEnabled($db, 'customer_accreditation_change_enabled');
    }
    
    include_once './include/rbac_helper.php';
    if (!$accreditationEnabled && !Utitlity::checkForDirector()) {
        cdcIsoDetailsResponse(array('success' => false, 'message' => 'you are not allowd to change accreditation , director needs to allow this feature'), 403);
    }
} else {
    if ($value === '' || strlen($value) > 50 || !preg_match('/^[a-zA-Z0-9 ._()-]+$/', $value)) {
        cdcIsoDetailsResponse(array('success' => false, 'message' => 'Invalid certification status.'), 422);
    }
}

if ($field === 'certification_status') {
    include_once './include/customer_feature_flags.php';
    if (!function_exists('aitfCustomerFeatureIsEnabled')) {
        $manualEnabled = true;
    } else {
        $manualEnabled = aitfCustomerFeatureIsEnabled($db, 'customer_manual_status_change_enabled');
    }
    
    include_once './include/rbac_helper.php';
    if (!$manualEnabled && !Utitlity::checkForDirector()) {
        cdcIsoDetailsResponse(array('success' => false, 'message' => 'Director needs to allow this feature'), 403);
    }
}

try {
    ensureCdcIsoMetadataTable($db);

    $cdcLookup = $db->prepare(
        'SELECT crr_id FROM cdc_report_review WHERE crr_id = :crr_id AND customer_id = :customer_id LIMIT 1'
    );
    $cdcLookup->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
    $cdcLookup->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
    $cdcLookup->execute();

    if (!$cdcLookup->fetchColumn()) {
        cdcIsoDetailsResponse(array('success' => false, 'message' => 'CDC record not found.'), 404);
    }

    $db->beginTransaction();

    $column = $allowedFields[$field];
    $upsert = $db->prepare(
        "INSERT INTO cdc_iso_metadata
            (crr_id, iso_index, iso_label, `$column`)
         VALUES
            (:crr_id, :iso_index, :iso_label, :value)
         ON DUPLICATE KEY UPDATE
            iso_label = VALUES(iso_label),
            `$column` = VALUES(`$column`)"
    );
    $upsert->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
    $upsert->bindValue(':iso_index', $isoIndex, PDO::PARAM_INT);
    $upsert->bindValue(':iso_label', $isoLabel);
    $upsert->bindValue(':value', $value);
    $upsert->execute();

    // Keep the legacy CDC accreditation column aligned with the first ISO.
    // Existing reports and older screens still read this column directly.
    if ($field === 'accreditation' && $isoIndex === 0) {
        $legacyUpdate = $db->prepare(
            'UPDATE cdc_report_review
             SET accreditation = :accreditation
             WHERE crr_id = :crr_id AND customer_id = :customer_id'
        );
        $legacyUpdate->bindValue(':accreditation', $value);
        $legacyUpdate->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
        $legacyUpdate->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $legacyUpdate->execute();
    }

    // Keep the global customer audit_status aligned with the selected ISO status.
    // Home page tabs and legacy logic rely on this for customer-level state.
    if ($field === 'certification_status') {
        $legacyStatusUpdate = $db->prepare(
            'UPDATE customer_info
             SET audit_status = :status
             WHERE id = :customer_id'
        );
        $legacyStatusUpdate->bindValue(':status', $value);
        $legacyStatusUpdate->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $legacyStatusUpdate->execute();
    }

    $db->commit();

    cdcIsoDetailsResponse(array(
        'success' => true,
        'crr_id' => $crrId,
        'iso_index' => $isoIndex,
        'field' => $field,
        'value' => $value,
        'message' => $field === 'accreditation'
            ? 'ISO accreditation updated successfully.'
            : 'ISO certification status updated successfully.',
    ));
} catch (PDOException $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('CDC ISO detail update error: ' . $exception->getMessage());
    cdcIsoDetailsResponse(array('success' => false, 'message' => 'Unable to update ISO details.'), 500);
}
