<#
    Sends just the ESC/POS cash-drawer kick command with no receipt image, for
    the "complete sale without printing an invoice" option - the sale still
    happened and the drawer should still open, it just skips the Edge/screenshot
    pipeline entirely since there is nothing to render.

    Usage: OpenDrawer.ps1 -PrinterName "SA-600"
#>
param(
    [Parameter(Mandatory=$true)][string]$PrinterName
)

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
    di.pDocName = "Al-Yazori Market Drawer Kick";
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
"@
}

$esc = [byte]27
$drawerKick = @($esc, [byte][char]'p', [byte]0, [byte]25, [byte]250)
$result = [POS.RawPrinter]::SendBytes($PrinterName, $drawerKick)
Write-Output $result
if ($result -like "OK:*") { exit 0 } else { exit 1 }
