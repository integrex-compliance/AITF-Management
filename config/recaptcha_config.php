<?php

return [
    // Paste your Google reCAPTCHA site key here.
    'site_key' => '6LfwOKssAAAAAIG6iKITHC9NcHJEunaUMTm6-dAU',

    // Paste your Google reCAPTCHA secret key here.
    'secret_key' => '6LfwOKssAAAAAG9AKAY8V3jxxGy_b6F6INUPwWYi',

    // Change to false only if you want to temporarily disable captcha.
    'enabled' => true,

    // Supported values: v2_checkbox, v3
    'version' => 'v2_checkbox',

    // Used only with v3.
    'action' => 'submit',

    // Used only with v3.
    'minimum_score' => 0.5,


    'bypass_on_localhost' => false,


    'localhost_hosts' => ['localhost', '127.0.0.1', '::1'],

    // Supported values: light, dark
    'theme' => 'light',

    // Development-only option: set true only if you explicitly want to use Google test keys on localhost.
    'use_test_keys_on_localhost' => true,
    'local_test_site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    'local_test_secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    'local_test_version' => 'v2_checkbox'
];
