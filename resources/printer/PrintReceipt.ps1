<#
    Renders an already-generated invoice HTML file to an image using headless Edge
    (so the browser's own text engine handles Arabic shaping/RTL correctly), then
    converts that image into an ESC/POS raster print job and sends it directly to
    the given Windows printer via the raw WritePrinter API. This bypasses the
    printer's own font/codepage entirely, which is necessary because most cheap
    80mm thermal printers do not shape or reorder Arabic text correctly on their own.

    Usage: PrintReceipt.ps1 -HtmlPath "C:\...\receipt.html" -PrinterName "SA-600"
#>
param(
    [Parameter(Mandatory=$true)][string]$HtmlPath,
    [Parameter(Mandatory=$true)][string]$PrinterName,
    [int]$WidthDots = 576,
    [string]$LogPath = $null,
    [switch]$OpenDrawer
)

function Write-Result([string]$text) {
    Write-Output $text
    if ($LogPath) {
        try { Set-Content -Path $LogPath -Value $text -Encoding UTF8 } catch {}
    }
}

function Find-Edge {
    $candidates = @(
        "$env:ProgramFiles\Microsoft\Edge\Application\msedge.exe",
        "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
    )
    foreach ($path in $candidates) {
        if (Test-Path $path) { return $path }
    }
    throw "Microsoft Edge not found"
}

Add-Type -AssemblyName System.Drawing

if (-not ("POS.RawPrinter" -as [type])) {
Add-Type -Name RawPrinter -Namespace POS -MemberDefinition @"
[StructLayout(LayoutKind.Sequential, CharSet=CharSet.Ansi)]
public struct DOCINFOA {
    [MarshalAs(UnmanagedType.LPStr)] public string pDocName;
    [MarshalAs(UnmanagedType.LPStr)] public string pOutputFile;
    [MarshalAs(UnmanagedType.LPStr)] public string pDataType;
}
[DllImport("winspool.Drv", EntryPoint="OpenPrinterA", SetLastError=true, CharSet=CharSet.Ansi, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool OpenPrinter(string src, out IntPtr hPrinter, IntPtr pd);
[DllImport("winspool.Drv", EntryPoint="ClosePrinter", SetLastError=true, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool ClosePrinter(IntPtr hPrinter);
[DllImport("winspool.Drv", EntryPoint="StartDocPrinterA", SetLastError=true, CharSet=CharSet.Ansi, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool StartDocPrinter(IntPtr hPrinter, int level, [In] ref DOCINFOA pDI);
[DllImport("winspool.Drv", EntryPoint="EndDocPrinter", SetLastError=true, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool EndDocPrinter(IntPtr hPrinter);
[DllImport("winspool.Drv", EntryPoint="StartPagePrinter", SetLastError=true, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool StartPagePrinter(IntPtr hPrinter);
[DllImport("winspool.Drv", EntryPoint="EndPagePrinter", SetLastError=true, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool EndPagePrinter(IntPtr hPrinter);
[DllImport("winspool.Drv", EntryPoint="WritePrinter", SetLastError=true, ExactSpelling=true, CallingConvention=CallingConvention.StdCall)]
public static extern bool WritePrinter(IntPtr hPrinter, IntPtr pBytes, int dwCount, out int dwWritten);
public static string SendBytes(string printerName, byte[] data) {
    IntPtr hPrinter;
    DOCINFOA di = new DOCINFOA();
    di.pDocName = "Al-Yazori Market Receipt";
    di.pDataType = "RAW";
    if (!OpenPrinter(printerName, out hPrinter, IntPtr.Zero)) return "ERROR:OpenPrinter:" + Marshal.GetLastWin32Error();
    if (!StartDocPrinter(hPrinter, 1, ref di)) { ClosePrinter(hPrinter); return "ERROR:StartDocPrinter:" + Marshal.GetLastWin32Error(); }
    if (!StartPagePrinter(hPrinter)) { EndDocPrinter(hPrinter); ClosePrinter(hPrinter); return "ERROR:StartPagePrinter:" + Marshal.GetLastWin32Error(); }
    IntPtr pUnmanaged = Marshal.AllocCoTaskMem(data.Length);
    Marshal.Copy(data, 0, pUnmanaged, data.Length);
    int written;
    bool ok = WritePrinter(hPrinter, pUnmanaged, data.Length, out written);
    Marshal.FreeCoTaskMem(pUnmanaged);
    EndPagePrinter(hPrinter);
    EndDocPrinter(hPrinter);
    ClosePrinter(hPrinter);
    return ok ? ("OK:" + written) : ("ERROR:WritePrinter:" + Marshal.GetLastWin32Error());
}

// PowerShell interpretes script-block loops instead of JIT-compiling them like
// a normal .NET method, and a fresh (cold) PowerShell process pays that cost in
// full every time - a few hundred thousand iterations here took upwards of ten
// seconds in a cold process despite running in a fraction of a second inside an
// already-warmed-up session. Doing the actual pixel work in compiled C# instead
// removes that gap entirely, regardless of how "cold" the host process is.
public static byte[] ConvertToRaster(byte[] pixels, int width, int height, int stride, out int croppedHeight) {
    int lastContentRow = 0;
    for (int y = height - 1; y >= 0; y--) {
        int rowStart = y * stride;
        bool rowHasContent = false;
        for (int x = 0; x < width; x += 4) {
            int offset = rowStart + (x * 4);
            int sum = pixels[offset] + pixels[offset + 1] + pixels[offset + 2];
            if (sum < 740) { rowHasContent = true; break; }
        }
        if (rowHasContent) { lastContentRow = y; break; }
    }

    croppedHeight = Math.Min(lastContentRow + 24, height);
    if (croppedHeight < 10) { croppedHeight = height; }

    int widthBytes = (width + 7) / 8;
    byte[] imageData = new byte[widthBytes * croppedHeight];

    for (int y = 0; y < croppedHeight; y++) {
        int rowStart = y * stride;
        int outRowStart = y * widthBytes;
        for (int x = 0; x < width; x++) {
            int offset = rowStart + (x * 4);
            double lum = 0.299 * pixels[offset + 2] + 0.587 * pixels[offset + 1] + 0.114 * pixels[offset];
            if (lum < 200) {
                int byteIndex = outRowStart + (x / 8);
                int bitIndex = 7 - (x % 8);
                imageData[byteIndex] |= (byte)(1 << bitIndex);
            }
        }
    }

    return imageData;
}
"@
}

$renderHeight = 4000
$runId = [Guid]::NewGuid().ToString("N")
$tempPng = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), "receipt_$runId.png")
$userDataDir = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), "edge-print-profile-$runId")

try {
    $edge = Find-Edge
    $htmlUri = "file:///" + ($HtmlPath -replace '\\', '/')

    # Headless Edge can leave its child (renderer/GPU) processes running after the
    # top-level process would otherwise exit, which piles up and eventually hangs
    # every later invocation. A dedicated --user-data-dir avoids profile-lock
    # contention between overlapping runs, and we forcefully kill the whole
    # process tree afterward instead of trusting Edge to clean up on its own.
    # Start-Process -ArgumentList naively space-joins array elements without
    # re-quoting them, so any path containing a space (our project folder does)
    # would otherwise be split into two arguments. Build one pre-quoted string instead.
    $edgeArgString = '--headless=new --disable-gpu --no-sandbox --hide-scrollbars' `
        + " --user-data-dir=`"$userDataDir`"" `
        + " --screenshot=`"$tempPng`"" `
        + " --window-size=$WidthDots,$renderHeight" `
        + ' --default-background-color=FFFFFFFF' `
        + " `"$htmlUri`""

    $proc = Start-Process -FilePath $edge -ArgumentList $edgeArgString -PassThru -WindowStyle Hidden
    $proc.WaitForExit(60000) | Out-Null

    taskkill /F /T /PID $proc.Id 2>$null | Out-Null

    if (-not (Test-Path $tempPng)) {
        Write-Result "ERROR:screenshot_failed"
        exit 1
    }

    $srcBmp = New-Object System.Drawing.Bitmap($tempPng)
    $width = $srcBmp.Width
    $rawHeight = $srcBmp.Height

    # Force a known 32bpp BGRA layout so LockBits gives us predictable byte offsets,
    # then read the whole buffer once with Marshal.Copy instead of per-pixel GetPixel
    # calls (which are slow enough on a few-thousand-row image to make the cashier wait).
    $bmp = New-Object System.Drawing.Bitmap($width, $rawHeight, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.DrawImage($srcBmp, 0, 0, $width, $rawHeight)
    $g.Dispose()
    $srcBmp.Dispose()

    $rect = New-Object System.Drawing.Rectangle(0, 0, $width, $rawHeight)
    $bmpData = $bmp.LockBits($rect, [System.Drawing.Imaging.ImageLockMode]::ReadOnly, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $stride = $bmpData.Stride
    $bufferSize = $stride * $rawHeight
    $pixels = New-Object byte[] $bufferSize
    [System.Runtime.InteropServices.Marshal]::Copy($bmpData.Scan0, $pixels, 0, $bufferSize)
    $bmp.UnlockBits($bmpData)
    $bmp.Dispose()

    $height = 0
    $imageData = [POS.RawPrinter]::ConvertToRaster($pixels, $width, $rawHeight, $stride, [ref]$height)
    $widthBytes = [Math]::Ceiling($width / 8)

    $esc = [byte]27
    $gs = [byte]29
    $init = @($esc, [byte][char]'@')
    $xL = $widthBytes -band 0xFF
    $xH = ($widthBytes -shr 8) -band 0xFF
    $yL = $height -band 0xFF
    $yH = ($height -shr 8) -band 0xFF
    $rasterHeader = @($gs, [byte][char]'v', [byte][char]'0', [byte]0, [byte]$xL, [byte]$xH, [byte]$yL, [byte]$yH)
    $feed = @([byte]10, [byte]10, [byte]10, [byte]10)
    $cut = @($gs, [byte][char]'V', [byte]1)

    # Standard ESC/POS cash-drawer kick (ESC p m t1 t2), sent through the same
    # printer cable/RJ11 pass-through - only for actual sale receipts, never for
    # barcode labels or the settings test print.
    $drawerKick = @($esc, [byte][char]'p', [byte]0, [byte]25, [byte]250)

    $payload = New-Object System.Collections.Generic.List[byte]
    $payload.AddRange([byte[]]$init)
    if ($OpenDrawer) { $payload.AddRange([byte[]]$drawerKick) }
    $payload.AddRange([byte[]]$rasterHeader)
    $payload.AddRange([byte[]]$imageData)
    $payload.AddRange([byte[]]$feed)
    $payload.AddRange([byte[]]$cut)

    $result = [POS.RawPrinter]::SendBytes($PrinterName, $payload.ToArray())
    Write-Result $result

    if ($result -like "OK:*") { exit 0 } else { exit 1 }
} finally {
    if (Test-Path $tempPng) { Remove-Item $tempPng -Force -ErrorAction SilentlyContinue }
    if (Test-Path $userDataDir) { Remove-Item $userDataDir -Recurse -Force -ErrorAction SilentlyContinue }
    if (Test-Path $HtmlPath) { Remove-Item $HtmlPath -Force -ErrorAction SilentlyContinue }
}
