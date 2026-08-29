@echo off
title AITF USB DSC Signer
echo Starting AITF USB DSC Signer in background...
taskkill /F /IM AITF-Signer.exe >nul 2>&1
ping 127.0.0.1 -n 2 >nul
start "" "%~dp0AITF-Signer.exe"
echo AITF USB DSC Signer is now running in your System Tray.
ping 127.0.0.1 -n 2 >nul
exit
