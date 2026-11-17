<?php
/** Keep this mail endpoint's JSON separate from legacy library warnings/output. */
class AITFPaymentReminderResponse
{
    public $stage = 'initialization';
    public $mailSent = false;
    private $completed = false;
    private $bufferLevel;
    private $reference;

    public function __construct()
    {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        $this->reference = uniqid('PR-');
        $this->bufferLevel = ob_get_level();
        ob_start();
        register_shutdown_function(array($this, 'onShutdown'));
    }

    public function send(array $payload, $status = 200)
    {
        while (ob_get_level() > $this->bufferLevel) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header_remove('Location');
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code($status);
        }
        $options = defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0;
        $json = json_encode($payload, $options);
        $this->completed = true;
        echo $json !== false ? $json : '{"success":false,"message":"Unable to encode the mail response. Check reminder history before retrying."}';
    }

    public function fail($error)
    {
        error_log('PAYMENT REMINDER [' . $this->reference . '] stage=' . $this->stage
            . ' PHP=' . PHP_VERSION . ': ' . (string)$error);
        if ($this->mailSent) {
            $this->send(array('success' => true, 'message' => 'Email sent, but reminder history could not be updated. Do not resend. Reference: ' . $this->reference));
            return;
        }
        $messages = array(
            'initialization' => 'Unable to initialize the payment reminder. Ask the administrator to check the server error log.',
            'payment details' => 'Unable to load payment details. Ask the administrator to check the server error log.',
            'attachments' => 'Unable to prepare the selected attachments. Email was not sent. Ask the administrator to check the server error log.',
            'mail delivery' => 'Email delivery could not be confirmed. Check reminder history and the mail account before retrying.'
        );
        $message = isset($messages[$this->stage]) ? $messages[$this->stage] : $messages['initialization'];
        $this->send(array('success' => false, 'message' => $message . ' Reference: ' . $this->reference), 500);
    }

    public function onShutdown()
    {
        if (!$this->completed) {
            $error = error_get_last();
            $this->fail($error ? $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] : 'Request ended before a JSON response was returned.');
        }
    }
}
