<?php

session_start();
include_once './dbConnect.php';
include_once './utility.php';
include_once './include/env_helper.php';
include_once './include/ai_document_review_lib.php';

date_default_timezone_set('Asia/Kolkata');

if (!function_exists('aitfAiReasoningTextHasAny')) {
    function aitfAiReasoningTextHasAny($text, $terms)
    {
        $text = strtolower((string) $text);
        foreach ($terms as $term) {
            if ($term !== '' && strpos($text, $term) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('aitfAiBuildReasoningMetrics')) {
    function aitfAiBuildReasoningMetrics($documents, $rules, $result = null, $reasoningTimeMs = null, $scannedOnly = false)
    {
        $documentCount = 0;
        foreach ((array) $documents as $doc) {
            if (!$scannedOnly || (isset($doc['scan_status']) && $doc['scan_status'] === 'Scanned')) {
                $documentCount++;
            }
        }

        $dateTerms = array('date', 'valid', 'expiry', 'registration', 'chronology', 'certificate issue', 'before date', 'audit before');
        $crossTerms = array('cross', 'consistent', 'consistency', 'mismatch', 'contradiction', 'client name', 'gstin', 'address', 'contact', 'scope', 'standard');
        $datesVerified = 0;
        $crossChecks = 0;
        foreach ((array) $rules as $rule) {
            $ruleText = implode(' ', array(
                isset($rule['rule_code']) ? $rule['rule_code'] : '',
                isset($rule['title']) ? $rule['title'] : '',
                isset($rule['category']) ? $rule['category'] : '',
                isset($rule['rule_text']) ? $rule['rule_text'] : '',
                isset($rule['expected_value']) ? $rule['expected_value'] : ''
            ));
            if (aitfAiReasoningTextHasAny($ruleText, $dateTerms)) {
                $datesVerified++;
            }
            if (aitfAiReasoningTextHasAny($ruleText, $crossTerms)) {
                $crossChecks++;
            }
        }

        $issues = array();
        $manual = array();
        if (is_array($result)) {
            if (!empty($result['findings']) && is_array($result['findings'])) {
                $issues = array_merge($issues, $result['findings']);
            }
            if (!empty($result['issues']) && is_array($result['issues'])) {
                $issues = array_merge($issues, $result['issues']);
            }
            if (!empty($result['manual_verifications']) && is_array($result['manual_verifications'])) {
                $manual = $result['manual_verifications'];
            }
        }
        $uniqueIssues = array();
        $dedupedIssues = array();
        foreach ($issues as $issue) {
            $signature = is_array($issue)
                ? md5(strtolower(implode('|', array(
                    isset($issue['check_area']) ? $issue['check_area'] : '',
                    isset($issue['finding']) ? $issue['finding'] : '',
                    isset($issue['reason']) ? $issue['reason'] : '',
                    isset($issue['evidence']) ? $issue['evidence'] : ''
                ))))
                : md5(strtolower((string) $issue));
            if (!isset($uniqueIssues[$signature])) {
                $uniqueIssues[$signature] = true;
                $dedupedIssues[] = $issue;
            }
        }
        $issues = $dedupedIssues;

        $confidence = null;
        if (is_array($result) && isset($result['confidence']) && is_numeric($result['confidence'])) {
            $confidence = (float) $result['confidence'];
        } else {
            $confidenceTotal = 0;
            $confidenceCount = 0;
            foreach ($issues as $issue) {
                if (isset($issue['confidence']) && is_numeric($issue['confidence'])) {
                    $confidenceTotal += (float) $issue['confidence'];
                    $confidenceCount++;
                }
            }
            if ($confidenceCount > 0) {
                $confidence = $confidenceTotal / $confidenceCount;
            }
        }
        if ($confidence !== null && $confidence <= 1) {
            $confidence = $confidence * 100;
        }

        return array(
            'documents_scanned' => $documentCount,
            'rules_applied' => count((array) $rules),
            'dates_verified' => $datesVerified,
            'cross_checks' => $crossChecks,
            'potential_issues' => count($issues) + count($manual),
            'confidence_score' => $confidence === null ? null : round($confidence),
            'reasoning_time_ms' => $reasoningTimeMs === null ? null : (int) $reasoningTimeMs
        );
    }
}

try {
    $requestStartedAt = microtime(true);

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
        $latestResult = null;
        if ($latest && !empty($latest['result_json'])) {
            $latestResult = json_decode($latest['result_json'], true);
            if (!is_array($latestResult)) {
                $latestResult = null;
            }
        }

        aitfAiJsonResponse(array(
            'success' => true,
            'client' => array(
                'id' => $context['client']['id'],
                'name' => $context['client']['customer_name']
            ),
            'documents' => $publicDocs,
            'active_rules_count' => count($rules),
            'latest_history' => $latest,
            'reasoning_metrics' => aitfAiBuildReasoningMetrics($publicDocs, $rules, $latestResult, null, true)
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

        $reviewMetrics = aitfAiBuildReasoningMetrics($reviewDocs, $rules, $result, null, false);
        $reviewMetrics['documents_scanned'] = (int) $review['document_count'];
        $reviewMetrics['rules_applied'] = (int) $review['rules_count'];

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
            'error_message' => $review['error_message'],
            'reasoning_metrics' => $reviewMetrics
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

    $scanScope = isset($_REQUEST['scan_scope']) ? trim((string) $_REQUEST['scan_scope']) : '';
    if ($scanScope !== 'software_fill') {
        $scanScope = '';
    }

    if ($scanScope === 'software_fill' && empty($documentKeys)) {
        $documents = array();
    } elseif (!empty($documentKeys)) {
        $documents = aitfAiSelectDocumentsByKeys($documents, $documentKeys);
    }
    $userPrompt = isset($_REQUEST['user_prompt']) ? trim((string) $_REQUEST['user_prompt']) : '';
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
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, 0, count($rules), 'failed', '', '', '', 'No prompt or scan target supplied.', $scanScope);
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
                aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $message, $scanScope);
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
                aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $message, $scanScope);
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
            '',
            $scanScope
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

        $reasoningTimeMs = round((microtime(true) - $requestStartedAt) * 1000);

        aitfAiJsonResponse(array(
            'success' => true,
            'history_id' => $historyId,
            'result' => $result,
            'documents' => array_map('aitfAiPublicDocument', aitfAiAttachDocumentReviewStatus($db, $customerId, $documents)),
            'documents_count' => count($documents),
            'rules_count' => count($rules),
            'model' => $historyModelName,
            'scan_scope' => $scanScope,
            'reasoning_time_ms' => $reasoningTimeMs,
            'reasoning_metrics' => aitfAiBuildReasoningMetrics($documents, $rules, $result, $reasoningTimeMs, false)
        ));
    } catch (AitfAiProviderException $ex) {
        aitfAiLogProviderError($ex->getMessage());
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $ex->getMessage(), $scanScope);
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
        aitfAiInsertHistory($db, $customerId, isset($_SESSION['username']) ? $_SESSION['username'] : '', $historyModelName, count($documents), count($rules), 'failed', '', '', '', $ex->getMessage(), $scanScope);
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
