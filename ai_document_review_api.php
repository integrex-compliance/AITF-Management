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

    if ($action === 'view_document') {
        $documentKey = isset($_GET['document_key']) ? trim((string) $_GET['document_key']) : '';
        if ($documentKey === '' || !preg_match('/^[a-f0-9]{40}$/', $documentKey)) {
            aitfAiJsonResponse(array('success' => false, 'message' => 'Invalid document selected.'), 400);
        }

        $selectedDocuments = aitfAiSelectDocumentsByKeys($documents, array($documentKey));
        if (empty($selectedDocuments) || empty($selectedDocuments[0]['full_path']) || !is_file($selectedDocuments[0]['full_path'])) {
            aitfAiJsonResponse(array('success' => false, 'message' => 'Document not found for this client.'), 404);
        }

        $document = $selectedDocuments[0];
        $filePath = $document['full_path'];
        $fileName = isset($document['document_name']) && $document['document_name'] !== '' ? $document['document_name'] : basename($filePath);
        $fileName = str_replace(array("\r", "\n", '"'), '', $fileName);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }

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
    $userPrompt = isset($_REQUEST['user_prompt']) ? trim((string) $_REQUEST['user_prompt']) : '';
    $scanScope = isset($_REQUEST['scan_scope']) ? trim((string) $_REQUEST['scan_scope']) : '';
    if ($scanScope !== 'software_fill') {
        $scanScope = '';
    }
    if ($scanScope === 'software_fill' || empty($documents)) {
        $context['software_filled_forms'] = aitfAiFetchSoftwareFilledContext($db, $customerId);
    }

    $requestedModelId = isset($_REQUEST['model']) ? trim((string) $_REQUEST['model']) : '';
    $modelId = aitfAiNormalizeModelId($requestedModelId);
    if ($modelId === '') {
        aitfAiJsonResponse(array('success' => false, 'message' => 'Invalid AI model selection.'), 400);
    }
    $aiModels = aitfAiModelConfigs();
    $selectedModel = $aiModels[$modelId];
    $historyModelName = $selectedModel['display_name'];

    if (empty($documents) && $userPrompt === '' && $scanScope !== 'software_fill') {
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, 0, count($rules), 'failed', '', '', '', 'No prompt or scan target supplied.');
        aitfAiJsonResponse(array('success' => false, 'message' => 'Type a prompt or use Scan Software Fill.'), 400);
    }

    $totalBytes = 0;
    foreach ($documents as $doc) {
        $totalBytes += (int) $doc['size_bytes'];
    }

    try {
        if ($selectedModel['provider'] === 'gemini') {
            $apiKey = trim((string) aitf_env('GEMINI_API_KEY', ''));
            if ($apiKey === '') {
                throw new AitfAiProviderException('GEMINI_API_KEY is not configured.');
            }

            $providerModel = trim((string) aitf_env('GEMINI_MODEL', 'gemini-2.5-flash'));
            if ($providerModel === '') {
                $providerModel = 'gemini-2.5-flash';
            }

            $maxBytes = (int) aitf_env('GEMINI_MAX_INLINE_BYTES', 18000000);
            if ($maxBytes > 0 && $totalBytes > $maxBytes) {
                $message = 'Client PDFs total ' . round($totalBytes / 1048576, 2) . ' MB, above GEMINI_MAX_INLINE_BYTES.';
                aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $message);
                aitfAiJsonResponse(array('success' => false, 'message' => 'Fly-512B can process large documents, but this selection exceeds the current upload analysis limit. Select fewer PDFs and try again.'), 413);
            }

            $providerResponse = aitfAiCallGemini($apiKey, $providerModel, aitfAiBuildPrompt($context, $documents, $rules, $userPrompt), $documents);
        } elseif ($selectedModel['provider'] === 'groq') {
            $apiKey = trim((string) aitf_env('GROQ_API_KEY', ''));
            if ($apiKey === '') {
                throw new AitfAiProviderException('GROQ_API_KEY is not configured.');
            }

            $maxBytes = (int) aitf_env('GROQ_MAX_SOURCE_BYTES', 8000000);
            if ($maxBytes > 0 && $totalBytes > $maxBytes) {
                $message = 'Client PDFs total ' . round($totalBytes / 1048576, 2) . ' MB, above GROQ_MAX_SOURCE_BYTES.';
                aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $message);
                aitfAiJsonResponse(array('success' => false, 'message' => 'Swim-128B is designed for smaller document sets. Select fewer PDFs or switch to Fly-512B and try again.'), 413);
            }

            $inputMaxChars = (int) aitf_env('GROQ_MAX_INPUT_CHARS', 6000);
            if ($inputMaxChars <= 0) {
                $inputMaxChars = 6000;
            }
            $groqDebugDetails = array();
            $groqDocumentInput = aitfAiBuildGroqDocumentInput($context, $documents, $rules, $userPrompt, $inputMaxChars, $groqDebugDetails);
            $providerResponse = aitfAiCallGroq(
                $apiKey,
                $selectedModel['model'],
                aitfAiBuildSharedSystemInstruction(),
                $groqDocumentInput,
                $groqDebugDetails
            );
        } else {
            aitfAiJsonResponse(array('success' => false, 'message' => 'Invalid AI model selection.'), 400);
        }

        $result = $providerResponse['result'];
        $summary = isset($result['summary']) ? $result['summary'] : '';
        $historyId = aitfAiInsertHistory(
            $db,
            $customerId,
            isset($_SESSION['username']) ? $_SESSION['username'] : '',
            $historyModelName,
            count($documents),
            count($rules),
            'completed',
            $summary,
            json_encode($result),
            isset($providerResponse['raw']) ? $providerResponse['raw'] : '',
            ''
        );
        aitfAiInsertDocumentHistory(
            $db,
            $historyId,
            $customerId,
            $documents,
            isset($_SESSION['username']) ? $_SESSION['username'] : '',
            $historyModelName,
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
            'model' => $historyModelName,
            'scan_scope' => $scanScope
        ));
    } catch (AitfAiProviderException $ex) {
        aitfAiLogProviderError($ex->getMessage());
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $ex->getMessage());
        $statusCode = $ex->isRateLimited() ? 429 : 500;
        $debugDetails = $ex->getDebugDetails();
        $debugMessage = $modelId === 'swim-128b' && !empty($debugDetails)
            ? 'Swim-128B debug failure: ' . aitfAiGroqDebugSummary($debugDetails)
            : aitfAiModelUnavailableMessage($modelId);
        aitfAiJsonResponse(array(
            'success' => false,
            'message' => $debugMessage,
            'error_type' => $ex->isRateLimited() ? 'rate_limited' : 'model_unavailable',
            'model' => $historyModelName,
            'debug' => $modelId === 'swim-128b' ? $debugDetails : null
        ), $statusCode);
    } catch (Exception $ex) {
        aitfAiLogProviderError($ex->getMessage());
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $ex->getMessage());
        aitfAiJsonResponse(array(
            'success' => false,
            'message' => aitfAiModelUnavailableMessage($modelId),
            'error_type' => 'model_unavailable',
            'model' => $historyModelName
        ), 500);
    }
} catch (Exception $ex) {
    aitfAiJsonResponse(array('success' => false, 'message' => $ex->getMessage()), 500);
}
