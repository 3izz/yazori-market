param(
    [Parameter(Mandatory = $true)]
    [string]$ProjectRoot
)

# Runs from a copy in %TEMP%, not from inside $ProjectRoot itself - update.bat
# makes that copy before launching this script, so this file can safely
# overwrite itself as part of the update without Windows complaining about a
# script trying to replace the very file it's currently executing from.

$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$root = $ProjectRoot.TrimEnd('\')
$tempZip = Join-Path $env:TEMP 'alyazori-update.zip'
$tempExtract = Join-Path $env:TEMP 'alyazori-update-extract'
$phpExe = Join-Path $root 'php-runtime\php.exe'
$phpIni = Join-Path $root 'php-runtime\php.ini'

try {
    Write-Host "جارٍ إنشاء نسخة احتياطية سريعة من قاعدة البيانات قبل التحديث..."
    $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    $dbBackupDir = Join-Path $env:TEMP "alyazori-pre-update-backup-$stamp"
    New-Item -ItemType Directory -Path $dbBackupDir -Force | Out-Null
    foreach ($suffix in @('', '-wal', '-shm')) {
        $file = Join-Path $root "database\database.sqlite$suffix"
        if (Test-Path $file) {
            Copy-Item $file $dbBackupDir -Force
        }
    }
    Write-Host "نسخة احتياطية إضافية محفوظة في: $dbBackupDir"

    Write-Host ""
    Write-Host "جارٍ تحميل آخر تحديث من GitHub..."
    Invoke-WebRequest -Uri 'https://github.com/3izz/yazori-market/archive/refs/heads/main.zip' -OutFile $tempZip -UseBasicParsing

    if (Test-Path $tempExtract) {
        Remove-Item $tempExtract -Recurse -Force
    }

    Write-Host "جارٍ فك الضغط..."
    Expand-Archive -Path $tempZip -DestinationPath $tempExtract -Force
    $extractedRoot = (Get-ChildItem $tempExtract -Directory | Select-Object -First 1).FullName

    if (-not $extractedRoot) {
        throw "لم يتم العثور على محتوى داخل الملف المضغوط بعد التحميل."
    }

    # database.sqlite, .env, storage/app/backups, vendor/, php-runtime/ and
    # node_modules are all gitignored, so none of them exist in this
    # downloaded tree at all - a plain (non-mirroring) copy can never
    # overwrite or delete any of them, by construction, no exclusion list
    # needed.
    Write-Host "جارٍ نسخ الملفات الجديدة فوق القديمة..."
    robocopy $extractedRoot $root /E | Out-Null
    if ($LASTEXITCODE -ge 8) {
        throw "فشل نسخ الملفات الجديدة (رمز robocopy: $LASTEXITCODE)"
    }

    Write-Host "جارٍ تحديث قاعدة البيانات..."
    & $phpExe -c $phpIni (Join-Path $root 'artisan') migrate --force

    Remove-Item $tempZip -Force -ErrorAction SilentlyContinue
    Remove-Item $tempExtract -Recurse -Force -ErrorAction SilentlyContinue

    Write-Host ""
    Write-Host "==================================================="
    Write-Host "تم التحديث بنجاح. فيك تشغل البرنامج عادي من اختصار سطح المكتب."
    Write-Host "==================================================="
}
catch {
    Write-Host ""
    Write-Host "==================================================="
    Write-Host "حدث خطأ أثناء التحديث ولم يتم تغيير أي شيء بقاعدة البيانات:"
    Write-Host $_.Exception.Message
    Write-Host "تأكد من الاتصال بالإنترنت وحاول مرة أخرى."
    Write-Host "==================================================="
    exit 1
}
