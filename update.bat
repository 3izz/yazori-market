@echo off
chcp 65001 >nul
title Al-Yazori Market - تحديث البرنامج
cd /d "%~dp0"

echo ===================================================
echo         تحديث برنامج اليازوري ماركت
echo ===================================================
echo.
echo تأكد إنو نافذة البرنامج (start.bat) مسكرة قبل ما تكمل.
echo اضغط أي زر للمتابعة، أو أغلق هذه النافذة للإلغاء.
echo.
pause

echo.
copy /y "%~dp0update.ps1" "%TEMP%\alyazori-update-runner.ps1" >nul
powershell -NoProfile -ExecutionPolicy Bypass -File "%TEMP%\alyazori-update-runner.ps1" -ProjectRoot "%~dp0"

echo.
pause
