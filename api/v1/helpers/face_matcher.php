<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\face_matcher.php

/**
 * Calculates Cosine Similarity between two float vectors.
 * Formula: (A . B) / (||A|| * ||B||)
 *
 * @param array $vecA Baseline vector
 * @param array $vecB Live selfie vector
 * @return float Similarity score between -1.0 and 1.0 (typically 0.0 to 1.0 for normalized face vectors)
 */
function aitf_cosine_similarity(array $vecA, array $vecB): float {
    $count = count($vecA);
    if ($count === 0 || $count !== count($vecB)) {
        return 0.0;
    }

    $dotProduct = 0.0;
    $normA = 0.0;
    $normB = 0.0;

    for ($i = 0; $i < $count; $i++) {
        $a = (float)$vecA[$i];
        $b = (float)$vecB[$i];

        $dotProduct += $a * $b;
        $normA += $a * $a;
        $normB += $b * $b;
    }

    $denominator = sqrt($normA) * sqrt($normB);

    if ($denominator <= 0.0000001) {
        return 0.0;
    }

    return (float)($dotProduct / $denominator);
}

/**
 * Calculates Euclidean Distance between two float vectors.
 * Formula: sqrt(sum((a_i - b_i)^2))
 */
function aitf_euclidean_distance(array $vecA, array $vecB): float {
    $count = count($vecA);
    if ($count === 0 || $count !== count($vecB)) {
        return 999.0;
    }

    $sumSquares = 0.0;
    for ($i = 0; $i < $count; $i++) {
        $diff = (float)$vecA[$i] - (float)$vecB[$i];
        $sumSquares += $diff * $diff;
    }

    return (float)sqrt($sumSquares);
}

/**
 * Validates face match based on blueprint threshold >= 0.75
 */
function aitf_verify_face_match(array $baselineVector, array $liveVector, float $threshold = 0.75): array {
    $similarity = aitf_cosine_similarity($baselineVector, $liveVector);
    $distance = aitf_euclidean_distance($baselineVector, $liveVector);

    $isMatch = ($similarity >= $threshold);

    return [
        'is_match' => $isMatch,
        'similarity_score' => round($similarity, 4),
        'euclidean_distance' => round($distance, 4),
        'threshold' => $threshold,
        'confidence' => ($similarity >= 0.85) ? 'high' : (($similarity >= 0.75) ? 'standard' : 'low')
    ];
}
