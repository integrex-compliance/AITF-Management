@echo off
title Install AITF USB DSC Signer to Windows Startup
set "TARGET_EXE=%~dp0AITF-Signer.exe"
set "SHORTCUT_PATH=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\AITF-Signer.lnk"

powershell -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%SHORTCUT_PATH%'); $s.TargetPath = '%TARGET_EXE%'; $s.WorkingDirectory = '%~dp0'; $s.Description = 'AITF USB DSC Signer Service'; $s.Save()"

echo.
echo =======================================================
echo  AITF USB DSC Signer successfully registered 
echo  It will now start automatically whenever your PC turns ON.
echo =======================================================
echo.
start "" "%TARGET_EXE%"
pause
