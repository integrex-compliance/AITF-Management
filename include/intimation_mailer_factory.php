<?php
if (!function_exists('aitfCreateIntimationMailer')) {
    function aitfCreateIntimationMailer($onDeliveryPhase)
    {
        aitfLoadPHPMailer();
        require_once __DIR__ . '/intimation_smtp.php';
        $mail = new AITFIntimationMailer;
        $mail->onDeliveryPhase = $onDeliveryPhase;
        $mail->attemptDeadline = microtime(true) + 90;
        return $mail;
    }
}
