<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\BackupService;
use App\Services\ThermalPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(BackupService $backupService, ThermalPrintService $printer): View
    {
        $backups = collect(File::exists($backupService->backupsDirectory()) ? File::files($backupService->backupsDirectory()) : [])
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->take(10)
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'date' => date('Y-m-d H:i', $file->getMTime()),
                'size' => round($file->getSize() / 1024, 1),
            ]);

        return view('settings.index', [
            'lastBackupAt' => $backupService->lastBackupAt(),
            'backups' => $backups,
            'printerName' => $printer->printerName(),
            'posPin' => Setting::get('pos_pin', '1234'),
            'showPaidChange' => Setting::get('show_paid_change', '1') === '1',
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'current_password' => 'كلمة السر الحالية',
            'password' => 'كلمة السر الجديدة',
        ]);

        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة السر الحالية غير صحيحة']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'تم تغيير كلمة السر بنجاح');
    }

    public function backupNow(BackupService $backupService): RedirectResponse
    {
        $backupService->runNow();

        return back()->with('status', 'تم إنشاء نسخة احتياطية بنجاح');
    }

    public function updatePrinter(Request $request, ThermalPrintService $printer): RedirectResponse
    {
        $data = $request->validate([
            'printer_name' => ['required', 'string', 'max:100'],
        ], [], ['printer_name' => 'اسم الطابعة']);

        $printer->setPrinterName($data['printer_name']);

        return back()->with('status', 'تم حفظ اسم الطابعة');
    }

    public function printTest(ThermalPrintService $printer): JsonResponse
    {
        $result = $printer->printTest();

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updatePosPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pos_pin' => ['required', 'string', 'min:4', 'max:8', 'regex:/^[0-9]+$/'],
        ], [
            'pos_pin.regex' => 'الرقم السري يجب أن يتكون من أرقام فقط',
        ], ['pos_pin' => 'الرقم السري لنقطة البيع']);

        Setting::set('pos_pin', $data['pos_pin']);

        return back()->with('status', 'تم حفظ الرقم السري لنقطة البيع');
    }

    public function updateInvoiceOptions(Request $request): RedirectResponse
    {
        Setting::set('show_paid_change', $request->boolean('show_paid_change') ? '1' : '0');

        return back()->with('status', 'تم حفظ إعدادات الفاتورة');
    }
}
