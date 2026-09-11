@echo off
chcp 65001 >nul
title Al-Yazori Market - اليازوري ماركت
cd /d "%~dp0"

set "PHP_EXE=%~dp0php-runtime\php.exe"
set "PHP_INI=%~dp0php-runtime\php.ini"

echo ===================================================
echo             اليازوري ماركت - Al-Yazori Market
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
    echo خطأ: تعذر إيجاد برنامج PHP المرفق مع النظام "php-runtime".
    echo هاد المجلد كبير الحجم ومو موجود على GitHub عن قصد، فمجلد "php-runtime" لازم يوصل
    echo بس عن طريق نسخة كاملة من الجهاز الأصلي "usb أو نسخ مباشر" - ليس عن طريق update.bat.
    echo.
    pause
    exit /b 1
)

if not exist "%~dp0vendor\autoload.php" (
    echo خطأ: مجلد "vendor" غير موجود أو ناقص، والبرنامج ما رح يشتغل بدونه.
    echo هاد المجلد كبير الحجم ومو موجود على GitHub عن قصد، فمجلد "vendor" لازم يوصل
    echo بس عن طريق نسخة كاملة من الجهاز الأصلي "usb أو نسخ مباشر" - ليس عن طريق update.bat.
    echo.
    pause
    exit /b 1
)

for /f "usebackq delims=" %%D in (`powershell -NoProfile -Command "[Environment]::GetFolderPath('Desktop')"`) do set "DESKTOP_DIR=%%D"

if defined DESKTOP_DIR (
    if not exist "%DESKTOP_DIR%\Al-Yazori Market.lnk" (
        echo جارٍ إنشاء اختصار على سطح المكتب لأول مرة...
        powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%DESKTOP_DIR%\Al-Yazori Market.lnk'); $s.TargetPath = '%~dp0start.bat'; $s.WorkingDirectory = '%~dp0'; $s.Description = 'Al-Yazori Market POS'; $s.IconLocation = '%~dp0public\images\logo.ico'; $s.Save()" >nul 2>nul
        echo تم. من الآن فصاعداً استخدم اختصار "Al-Yazori Market" من سطح المكتب.
        echo.
    )
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0resources\printer\SetupPrinter.ps1" >nul 2>nul

if not exist "%~dp0database\database.sqlite" (
    echo إعداد قاعدة البيانات لأول مرة، الرجاء الانتظار...
    type nul > "%~dp0database\database.sqlite"
    "%PHP_EXE%" -c "%PHP_INI%" "%~dp0artisan" migrate --force --seed
    echo.
) else (
    REM Safe to run every launch: does nothing if there's nothing pending.
    REM This means a code update applied while the program was closed
    REM (e.g. via update.bat) takes effect automatically next launch,
    REM with no separate manual migration step required.
    "%PHP_EXE%" -c "%PHP_INI%" "%~dp0artisan" migrate --force >nul 2>nul
)

start "" wscript.exe "%~dp0open_browser.vbs"

cd /d "%~dp0public"
"%PHP_EXE%" -c "%PHP_INI%" -S 127.0.0.1:8000 "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

echo.
echo تم إيقاف برنامج اليازوري ماركت.
pause
