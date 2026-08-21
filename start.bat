@echo off
chcp 65001 >nul
title Al-Yazori Market - اليزوري ماركت
cd /d "%~dp0"

set "PHP_EXE=%~dp0php-runtime\php.exe"
set "PHP_INI=%~dp0php-runtime\php.ini"

echo ===================================================
echo             اليزوري ماركت - Al-Yazori Market
echo ===================================================
echo.
echo جارٍ تشغيل البرنامج، الرجاء الانتظار قليلاً...
echo سيفتح البرنامج تلقائياً في المتصفح خلال ثوانٍ.
echo.
echo ملاحظة مهمة:
echo   - لا تغلق هذه النافذة أثناء استخدام البرنامج.
echo   - لإيقاف البرنامج بالكامل: أغلق هذه النافذة فقط (زر X).
echo.
echo ===================================================
echo.

if not exist "%PHP_EXE%" (
    echo خطأ: تعذر إيجاد برنامج PHP المرفق مع النظام.
    echo تأكد إنو مجلد "php-runtime" موجود داخل مجلد البرنامج ولم يتم حذفه أو نقله.
    echo.
    pause
    exit /b 1
)

if not exist "%~dp0database\database.sqlite" (
    echo إعداد قاعدة البيانات لأول مرة، الرجاء الانتظار...
    type nul > "%~dp0database\database.sqlite"
    "%PHP_EXE%" -c "%PHP_INI%" "%~dp0artisan" migrate --force --seed
    echo.
)

start "" wscript.exe "%~dp0open_browser.vbs"

cd /d "%~dp0public"
"%PHP_EXE%" -c "%PHP_INI%" -S 127.0.0.1:8000 "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

echo.
echo تم إيقاف برنامج اليزوري ماركت.
pause
