<?php
// Loaded only after aitfLoadPHPMailer(). Keep agreement's transport settings,
// but record the DATA acknowledgement before PHPMailer waits for QUIT.
class AITFIntimationSMTP extends SMTP
{
    public $onDeliveryPhase;
    private $recipientRejected = false;

    public function mail($from)
    {
        $this->recipientRejected = false;
        return parent::mail($from);
    }

    public function recipient($toaddr)
    {
        $accepted = parent::recipient($toaddr);
        $this->recipientRejected = $this->recipientRejected || !$accepted;
        return $accepted;
    }

    public function data($msg_data)
    {
        // Do not deliver to only some recipients and then retry everyone.
        if ($this->recipientRejected) {
            throw new RuntimeException('A client recipient was rejected by SMTP. No letter was transmitted. Correct the client email and retry.');
        }
        call_user_func($this->onDeliveryPhase, 'transmitting');
        $accepted = parent::data($msg_data);
        if ($accepted) {
            call_user_func($this->onDeliveryPhase, 'accepted');
            return true;
        }
        $error = $this->getError();
        // An explicit SMTP rejection is retryable. A lost DATA acknowledgement
        // is ambiguous: stop fallback transports so they cannot duplicate it.
        if (!empty($error['smtp_code']) && (int) $error['smtp_code'] >= 400) {
            call_user_func($this->onDeliveryPhase, 'connecting');
            return false;
        }
        throw new RuntimeException('The mail server did not confirm delivery.');
    }
}

class AITFIntimationMailer extends PHPMailer
{
    public $onDeliveryPhase;
    public $attemptDeadline;

    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new AITFIntimationSMTP;
            $this->smtp->Timelimit = 15;
        }
        $this->smtp->onDeliveryPhase = $this->onDeliveryPhase;
        return $this->smtp;
    }

    public function smtpConnect($options = array())
    {
        if ($this->attemptDeadline && microtime(true) >= $this->attemptDeadline) {
            throw new RuntimeException('Mail connection attempts timed out. The letter remains pending for retry.');
        }
        return parent::smtpConnect($options);
    }
}
