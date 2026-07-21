<?php

session_start();
include_once './dbConnect.php';
include_once './include/cdc_iso_metadata.php';

header('Content-Type: application/json; charset=utf-8');

function cdcAccreditationResponse($payload, $statusCode = 200)
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
    cdcAccreditationResponse(array('success' => false, 'message' => 'Unauthorized request.'), 401);
}

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    cdcAccreditationResponse(array('success' => false, 'message' => 'POST request required.'), 405);
}

$crrId = filter_input(INPUT_POST, 'crr_id', FILTER_VALIDATE_INT);
$customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
$accreditation = strtoupper(trim((string) ($_POST['accreditation'] ?? '')));
$isoLabel = trim((string) ($_POST['iso_label'] ?? ''));

if (!$crrId || !$customerId || !in_array($accreditation, array('NABCB', 'SELF'), true)) {
    cdcAccreditationResponse(array(
        'success' => false,
        'message' => 'Valid CDC record, customer, and accreditation are required.'
    ), 422);
}

try {
    ensureCdcIsoMetadataTable($db);

    $lookup = $db->prepare(
        'SELECT crr_id FROM cdc_report_review WHERE crr_id = :crr_id AND customer_id = :customer_id LIMIT 1'
    );
    $lookup->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
    $lookup->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
    $lookup->execute();

    if (!$lookup->fetchColumn()) {
        cdcAccreditationResponse(array('success' => false, 'message' => 'CDC record not found.'), 404);
    }

    $db->beginTransaction();

    $update = $db->prepare(
        'UPDATE cdc_report_review
         SET accreditation = :accreditation
         WHERE crr_id = :crr_id AND customer_id = :customer_id'
    );
    $update->bindValue(':accreditation', $accreditation, PDO::PARAM_STR);
    $update->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
    $update->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
    $update->execute();

    if ($isoLabel !== '') {
        upsertCdcIsoMetadata($db, $crrId, array(
            array(
                'iso_index' => 0,
                'iso_label' => $isoLabel,
                'accreditation' => $accreditation,
            ),
        ));
    }

    $db->commit();

    cdcAccreditationResponse(array(
        'success' => true,
        'accreditation' => $accreditation,
        'message' => 'Accreditation updated successfully.'
    ));
} catch (PDOException $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('CDC accreditation update error: ' . $exception->getMessage());
    cdcAccreditationResponse(array('success' => false, 'message' => 'Unable to update accreditation.'), 500);
}
