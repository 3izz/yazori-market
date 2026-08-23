<#
    One-time setup for the SA-600 (and similar generic 80mm USB) receipt printer.

    These printers show up to Windows as a bare "USB Printing Support" device
    (the generic usbprint.sys class driver claims them at the USB level) but
    Windows never creates an actual printer queue for them on its own, because
    there is no matching vendor driver in Windows' driver database. We don't
    need a real driver anyway: the app renders receipts to an image and sends
    raw ESC/POS bytes directly, so any generic printer object bound to the
    right port is enough to give WritePrinter something to open.

    Safe to run multiple times: does nothing if a printer with this name
    already exists.
#>
param(
    [string]$PrinterName = "SA-600"
)

$existing = Get-Printer -Name $PrinterName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Output "OK:already-exists"
    exit 0
}

if (-not (Get-PrinterDriver -Name "Generic / Text Only" -ErrorAction SilentlyContinue)) {
    Add-PrinterDriver -Name "Generic / Text Only" -ErrorAction SilentlyContinue
}

$usedPorts = Get-Printer | Select-Object -ExpandProperty PortName

# Windows labels a fresh, unclaimed USB-class printer port "Virtual printer port
# for USB"; once a printer has been bound to it and later removed, it instead
# shows the device's own friendly name (e.g. "80Series2") as the description.
# Matching on the "USB###" name pattern instead of the description text catches
# both states and generalizes to other generic thermal printer models.
$candidatePort = Get-PrinterPort |
    Where-Object { $_.Name -match '^USB\d+$' -and $_.Name -notin $usedPorts } |
    Select-Object -First 1 -ExpandProperty Name

if (-not $candidatePort) {
    Write-Output "ERROR:no_free_usb_port"
    exit 1
}

try {
    Add-Printer -Name $PrinterName -DriverName "Generic / Text Only" -PortName $candidatePort
    Write-Output "OK:created-on-$candidatePort"
    exit 0
} catch {
    Write-Output "ERROR:$($_.Exception.Message)"
    exit 1
}
