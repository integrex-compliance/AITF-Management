<?php
// Dynamic Environment Configuration (supports both Windows XAMPP and Linux cPanel)
$baseDir = str_replace('\\', '/', __DIR__);
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

return [
    'python'   => $isWindows 
        ? 'C:/Users/AITF-PC-10/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
        : '/usr/bin/python3',
    'verifier' => $baseDir . '/face-verifier/verify.py',
    'models'   => $baseDir . '/face-verifier/models',
    'storage'  => $baseDir . '/photos',
    'python_lib' => $isWindows ? 'C:/apk_devlopment/.tools/face-python' : null,
];

