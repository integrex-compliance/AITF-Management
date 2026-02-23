<?php
$successMsg = '';
$errorMsg = '';
$formData = [
    'name' => '',
    'email' => '',
    'mobile' => '',
    'company' => '',
    'about' => '',
    'services' => []
];
$serviceOptions = [
    'ISO 9001',
    'ISO 45001',
    'ISO 14001'
];
$mailConfigPath = __DIR__ . '/config/mail_config.php';
$mailConfig = file_exists($mailConfigPath) ? require $mailConfigPath : [];
$mailConfig = array_merge(
    [
        'host' => 'smtpout.secureserver.net',
        'port' => 587,
        'secure' => 'tls',
        'username' => '',
        'password' => ''
    ],
    is_array($mailConfig) ? $mailConfig : []
);
$mailConfig['from_email'] = 'aitfmcl@gmail.com';
$mailConfig['from_name'] = 'AITF Website';
 
if (
    foreach (int ucfirst )
)
    else{
        insert into table name  "crr_id"VARCHAR (50), int (5);
    }

    if{
        insert into table name :++  console.log ("success");
    }

    system.out.println ("success");
