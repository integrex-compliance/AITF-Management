<?php

session_start();
include_once './dbConnect.php';
include_once './utility.php';
include_once './include/env_helper.php';
include_once './include/ai_document_review_lib.php';

date_default_timezone_set('Asia/Kolkata');

try {
    if (!$db) {
        aitfAiJsonResponse(array('success' => false, 'message' => 'Database connection is unavailable.'), 500);
    }

    if (!Utitlity::checkIfLoggedIn()) {
        aitfAiJsonResponse(array('success' => false, 'message' => 'Login required.'), 401);
    }

    aitfAiEnsureSchema($db);

    $customerId = aitfAiCurrentCustomerId();
    if ($customerId <= 0) {
        aitfAiJsonResponse(array('success' => false, 'message' => 'No current client selected.'), 400);
    }

    $context = aitfAiFetchClientContext($db, $customerId);
    if (!$context) {
        aitfAiJsonResponse(array('success' => false, 'message' => 'Client not found.'), 404);
    }

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'overview';
    $documents = aitfAiFetchClientPdfDocuments($db, $customerId);
    $documents = aitfAiAttachDocumentReviewStatus($db, $customerId, $documents);
    $rules = aitfAiFetchActiveRules($db);

    if ($action === 'overview') {
        $publicDocs = array();
        foreach ($documents as $doc) {
            $publicDocs[] = aitfAiPublicDocument($doc);
        }

        $latest = aitfAiFetchLatestHistory($db, $customerId);
        aitfAiJsonResponse(array(
            'success' => true,
            'client' => array(
                'id' => $context['client']['id'],
                'name' => $context['client']['customer_name']
            ),
            'documents' => $publicDocs,
            'active_rules_count' => count($rules),
            'latest_history' => $latest
        ));
    }

    if ($action === 'review') {
        $documentKey = isset($_REQUEST['document_key']) ? trim((string) $_REQUEST['document_key']) : '';
        $historyId = isset($_REQUEST['history_id']) ? (int) $_REQUEST['history_id'] : 0;
        $review = aitfAiFetchDocumentReview($db, $customerId, $documentKey, $historyId);
        if (!$review) {
            aitfAiJsonResponse(array('success' => false, 'message' => 'No saved AI review found for this document.'), 404);
        }

        $result = json_decode($review['result_json'], true);
        if (!is_array($result)) {
            $result = null;
        }

        $reviewDocs = array();
        foreach ($review['document_history'] as $docHistory) {
            $reviewDocs[] = array(
                'document_key' => $docHistory['document_key'],
                'document_name' => $docHistory['document_name'],
                'label' => $docHistory['document_label'],
                'service_type' => $docHistory['service_type'],
                'service_id' => $docHistory['service_id'],
                'column_name' => $docHistory['column_name'],
                'file_path' => $docHistory['file_path'],
                'file_size_bytes' => $docHistory['file_size_bytes']
            );
        }

        aitfAiJsonResponse(array(
            'success' => true,
            'history_id' => $review['history_id'],
            'model' => $review['model_name'],
            'status' => $review['status'],
            'summary' => $review['summary'],
            'analyzed_by' => $review['analyzed_by'],
            'analyzed_at' => $review['analyzed_at'],
            'documents' => $reviewDocs,
            'rules_count' => $review['rules_count'],
            'document_count' => $review['document_count'],
            'result' => $result,
            'error_message' => $review['error_message']
        ));
    }

    if ($action !== 'analyze') {
        aitfAiJsonResponse(array('success' => false, 'message' => 'Unsupported action.'), 400);
    }

    $documentKeys = array();
    if (isset($_REQUEST['document_keys'])) {
        if (is_array($_REQUEST['document_keys'])) {
            $documentKeys = $_REQUEST['document_keys'];
        } else {
            $documentKeys = explode(',', (string) $_REQUEST['document_keys']);
        }
    }
    $documentKeys = array_values(array_filter(array_map('trim', $documentKeys), function ($key) {
        return $key !== '' && preg_match('/^[a-f0-9]{40}$/', $key);
    }));

    if (!empty($documentKeys)) {
        $documents = aitfAiSelectDocumentsByKeys($documents, $documentKeys);
    }

    $apiKey = trim((string) aitf_env('GEMINI_API_KEY', ''));
    if ($apiKey === '') {
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', '', count($documents), count($rules), 'failed', '', '', '', 'GEMINI_API_KEY is not configured.');
        aitfAiJsonResponse(array('success' => false, 'message' => 'GEMINI_API_KEY is not configured in .env/environment.'), 500);
    }

    if (empty($documents)) {
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', '', 0, count($rules), 'failed', '', '', '', 'No selected uploaded PDF documents found for current client.');
        aitfAiJsonResponse(array('success' => false, 'message' => 'No selected uploaded PDF documents found for this client.'), 400);
    }

    $model = trim((string) aitf_env('GEMINI_MODEL', 'gemini-2.5-flash'));
    if ($model === '') {
        $model = 'gemini-2.5-flash';
    }

    $maxBytes = (int) aitf_env('GEMINI_MAX_INLINE_BYTES', 18000000);
    $totalBytes = 0;
    foreach ($documents as $doc) {
        $totalBytes += (int) $doc['size_bytes'];
    }
    if ($maxBytes > 0 && $totalBytes > $maxBytes) {
        $message = 'Client PDFs total ' . round($totalBytes / 1048576, 2) . ' MB, above GEMINI_MAX_INLINE_BYTES. Increase the limit or reduce file size.';
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $model, count($documents), count($rules), 'failed', '', '', '', $message);
        aitfAiJsonResponse(array('success' => false, 'message' => $message), 413);
    }

    $prompt = aitfAiBuildPrompt($context, $documents, $rules);
    try {
        $gemini = aitfAiCallGemini($apiKey, $model, $prompt, $documents);
        $result = $gemini['result'];
        $summary = isset($result['summary']) ? $result['summary'] : '';
        $historyId = aitfAiInsertHistory(
            $db,
            $customerId,
            isset($_SESSION['username']) ? $_SESSION['username'] : '',
            $model,
            count($documents),
            count($rules),
            'completed',
            $summary,
            json_encode($result),
            isset($gemini['raw']) ? $gemini['raw'] : '',
            ''
        );
        aitfAiInsertDocumentHistory(
            $db,
            $historyId,
            $customerId,
            $documents,
            isset($_SESSION['username']) ? $_SESSION['username'] : '',
            $model,
            'completed',
            $summary
        );

        aitfAiJsonResponse(array(
            'success' => true,
            'history_id' => $historyId,
            'result' => $result,
            'documents' => array_map('aitfAiPublicDocument', aitfAiAttachDocumentReviewStatus($db, $customerId, $documents)),
            'documents_count' => count($documents),
            'rules_count' => count($rules),
            'model' => $model
        ));
    } catch (Exception $ex) {
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $model, count($documents), count($rules), 'failed', '', '', '', $ex->getMessage());
        aitfAiJsonResponse(array('success' => false, 'message' => $ex->getMessage()), 500);
    }
} catch (Exception $ex) {
    aitfAiJsonResponse(array('success' => false, 'message' => $ex->getMessage()), 500);
}
