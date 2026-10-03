<?php
if (!function_exists('normalizePaymentServiceType')) {
    function normalizePaymentServiceType($svcRaw) {
        $raw = trim((string)$svcRaw);
        if ($raw === '' || strcasecmp($raw, 'payment') === 0) return 'CA';
        $lower = strtolower($raw);

        // Clean spaces and underscores/dashes for uniform matching
        $clean = preg_replace('/[\s_-]+/', ' ', $lower);

        // 1. Contract Review (CR)
        if (preg_match('/\b(contract\s*review|cr)\b/i', $raw) || $lower === 'cr' || strpos($lower, 'contract review') !== false) {
            return 'Contract Review';
        }

        // 2. Transition Audit
        if (strpos($lower, 'transition') !== false) {
            return 'Transition Audit';
        }

        // 3. PTR
        if (preg_match('/\bptr\b/i', $raw) || strpos($lower, 'ptr') !== false) {
            return 'PTR';
        }

        // 4. Special Audit (SP)
        if (preg_match('/\b(special\s*audit|sp)\b/i', $raw) || $lower === 'sp' || strpos($lower, 'sp-') !== false) {
            return 'SP';
        }

        // 5. 1st Surveillance (SA-I) - check before RC if it's explicitly SA
        if (
            preg_match('/\b(1st|1\s*st|first|3rd|3\s*rd|third|5th|5\s*th)\s*surveillance/i', $clean) ||
            preg_match('/surveillance\s*(1|i|01|3|03|5|05)\b/i', $clean) ||
            preg_match('/\bsa\s*[-_]?\s*(0?1|i|0?3|0?5)\b/i', $clean) ||
            strpos($lower, 'sa-01') !== false ||
            strpos($lower, 'sa-1') !== false ||
            strpos($lower, 'sa-i') !== false ||
            strpos($lower, 'sa 1') !== false ||
            strpos($lower, 'sa1') !== false ||
            strpos($lower, 'sa-03') !== false ||
            strpos($lower, 'sa-3') !== false ||
            strpos($lower, 'sa3') !== false ||
            strpos($lower, 'sa-05') !== false ||
            strpos($lower, 'sa-5') !== false ||
            strpos($lower, 'sa5') !== false
        ) {
            return 'SA-I';
        }

        // 6. 2nd Surveillance (SA-II)
        if (
            preg_match('/\b(2nd|2\s*nd|second|4th|4\s*th|fourth|6th|6\s*th)\s*surveillance/i', $clean) ||
            preg_match('/surveillance\s*(2|ii|02|4|04|6|06)\b/i', $clean) ||
            preg_match('/\bsa\s*[-_]?\s*(0?2|ii|0?4|0?6)\b/i', $clean) ||
            strpos($lower, 'sa-02') !== false ||
            strpos($lower, 'sa-2') !== false ||
            strpos($lower, 'sa-ii') !== false ||
            strpos($lower, 'sa 2') !== false ||
            strpos($lower, 'sa2') !== false ||
            strpos($lower, 'sa-04') !== false ||
            strpos($lower, 'sa-4') !== false ||
            strpos($lower, 'sa4') !== false ||
            strpos($lower, 'sa-06') !== false ||
            strpos($lower, 'sa-6') !== false ||
            strpos($lower, 'sa6') !== false
        ) {
            return 'SA-II';
        }

        // 7. Re-Certification (RC) - MUST BE CHECKED BEFORE general CA/certification
        if (
            preg_match('/\b(re\s*cert|recert|re\s*certification|recertification|rc)\b/i', $raw) ||
            strpos($clean, 're cert') !== false ||
            strpos($clean, 'recert') !== false ||
            strpos($lower, 'rc-') !== false ||
            strpos($lower, 'rc -') !== false ||
            $lower === 'rc'
        ) {
            return 'RC';
        }

        // 8. General Surveillance (if unspecified 1 or 2, default to SA-I)
        if (strpos($lower, 'surveillance') !== false) {
            return 'SA-I';
        }

        // 9. Generic Training
        if (strpos($lower, 'generic') !== false || strpos($lower, 'training') !== false) {
            return 'Generic Training';
        }

        // 10. Initial Certification (CA)
        if (
            preg_match('/\b(ca|stage\s*1|stage\s*2|stage1|stage2|initial|payment)\b/i', $raw) ||
            strpos($lower, 'ca-') !== false ||
            strpos($lower, 'stage 1') !== false ||
            strpos($lower, 'stage 2') !== false ||
            strpos($lower, 'application') !== false ||
            strpos($lower, 'certification') !== false ||
            $lower === 'ca'
        ) {
            return 'CA';
        }

        // 11. Auxiliary / Expense line descriptions (Travelling, Hotel, Conveyance, Expense, etc.) - merge into CA instead of creating an orphan section
        if (
            preg_match('/\b(travel|travelling|traveling|flight|train|air\s*ticket|fare|conveyance|hotel|lodging|boarding|stay|accommodation|expense|expenses|reimbursement|out\s*of\s*pocket|ta\s*\/?\s*da|da\s*\/?\s*ta|misc|miscellaneous|discount|tds|short|admin|charges|fee|fees)\b/i', $raw)
        ) {
            return 'CA';
        }

        return $raw;
    }
}

