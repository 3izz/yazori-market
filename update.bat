@echo off
chcp 65001 >nul
title Al-Yazori Market - تحديث البرنامج
cd /d "%~dp0"

set "PHP_EXE=%~dp0php-runtime\php.exe"
set "PHP_INI=%~dp0php-runtime\php.ini"

echo ===================================================
echo         تحديث برنامج اليازوري ماركت
echo ===================================================
echo.
echo تأكد إنو نافذة البرنامج (start.bat) مسكرة قبل ما تكمل.
echo اضغط أي زر للمتابعة، أو أغلق هذه النافذة للإلغاء.
echo.
pause

echo.
echo جارٍ تحميل آخر تحديث...
git pull origin main
if errorlevel 1 (
    echo.
    echo تعذر تحميل التحديث. تأكد من الاتصال بالإنترنت وحاول مرة أخرى.
    echo.
    pause
    exit /b 1
)

echo.
echo جارٍ تحديث قاعدة البيانات...
"%PHP_EXE%" -c "%PHP_INI%" "%~dp0artisan" migrate --force

echo.
echo ===================================================
echo تم التحديث بنجاح.
echo فيك تشغل البرنامج عادي من اختصار سطح المكتب.
echo ===================================================
echo.
pause
