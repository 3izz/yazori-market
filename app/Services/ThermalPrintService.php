<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class ThermalPrintService
{
    private const DEFAULT_PRINTER = 'SA-600';

    public function printSale(Sale $sale): array
    {
        $sale->loadMissing('items');

        $html = view('sales.print-raw', compact('sale'))->render();

        return $this->printHtml($html);
    }

    public function printBarcodeLabel(Product $product): array
    {
        $html = view('products.barcode-label', compact('product'))->render();

        return $this->printHtml($html);
    }

    public function printTest(): array
    {
        $html = view('sales.print-test')->render();

        return $this->printHtml($html, wait: true);
    }

    public function printerName(): string
    {
        return Setting::get('printer_name', self::DEFAULT_PRINTER);
    }

    public function setPrinterName(string $name): void
    {
        Setting::set('printer_name', $name);
    }

    /**
     * PHP's built-in development server (php -S, used by start.bat) blocks its
     * single request-handling thread while reading a child process's output, and
     * this particular child (PowerShell -> headless Edge) has proven unreliable
     * to wait on from inside that SAPI - it can take far longer here than the
     * same command run from the CLI or an interactive shell. Waiting for it would
     * risk freezing the whole POS for every other page while one receipt prints.
     *
     * So the default ($wait = false, used for every sale) launches the print job
     * fully detached via `start` and returns immediately without knowing whether
     * it actually printed - the shop will notice quickly if paper stops coming
     * out, and the manual test print below stays synchronous because that's a
     * deliberate, occasional setup check where waiting a few seconds is fine.
     */
    private function printHtml(string $html, bool $wait = false): array
    {
        $directory = storage_path('app/private/receipts');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $htmlPath = $directory.DIRECTORY_SEPARATOR.'receipt-'.uniqid().'.html';
        File::put($htmlPath, $html);

        $script = base_path('resources/printer/PrintReceipt.ps1');

        $psArgs = [
            '-NoProfile',
            '-ExecutionPolicy', 'Bypass',
            '-File', $script,
            '-HtmlPath', $htmlPath,
            '-PrinterName', $this->printerName(),
        ];

        if ($wait) {
            $command = array_merge(['powershell.exe'], $psArgs);

            // stderr is discarded to the null device rather than piped: draining
            // two separate pipes sequentially can deadlock if one fills its OS
            // buffer while the other is being read.
            $descriptors = [1 => ['pipe', 'w'], 2 => ['file', 'NUL', 'w']];
            $process = proc_open($command, $descriptors, $pipes);

            $output = '';

            if (is_resource($process)) {
                $output = trim(stream_get_contents($pipes[1]));
                fclose($pipes[1]);
                $exitCode = proc_close($process);
            } else {
                $exitCode = -1;
            }

            File::delete($htmlPath);

            $success = $exitCode === 0 && str_starts_with($output, 'OK:');

            return [
                'success' => $success,
                'message' => $success
                    ? 'تمت الطباعة بنجاح'
                    : 'تعذرت الطباعة: '.($output ?: "exit code {$exitCode}"),
            ];
        }

        // `start "" /B` detaches the process from this request entirely; the
        // outer cmd.exe (which proc_open on Windows always wraps a string
        // command in) returns as soon as it has launched the child, so this
        // does not block. The script deletes its own HTML input when it's done.
        $commandString = 'start "" /B powershell.exe '.implode(' ', array_map('escapeshellarg', $psArgs));

        $process = proc_open($commandString, [], $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }

        return [
            'success' => true,
            'message' => 'تم إرسال الفاتورة للطباعة',
        ];
    }
}
