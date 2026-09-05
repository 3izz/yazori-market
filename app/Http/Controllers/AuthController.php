<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'username' => 'اسم المستخدم',
            'password' => 'كلمة السر',
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()
                ->withErrors(['username' => 'اسم المستخدم أو كلمة السر غير صحيحة'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $wasPosOnly = ! Auth::check() && $request->session()->get('pos_unlocked');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasPosOnly ? 'pos.unlock' : 'login');
    }

    public function showPosUnlock(): View|RedirectResponse
    {
        if (Auth::check() || session('pos_unlocked')) {
            return redirect()->route('pos.index');
        }

        return view('auth.pos-unlock');
    }

    public function posUnlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string'],
        ], [], ['pin' => 'الرقم السري']);

        // Named cashier PINs are checked first so each sale can be attributed
        // to a specific person; the single master PIN from Settings is kept
        // as a fallback so a shop that never bothers adding cashiers keeps
        // working exactly as before.
        $cashier = Cashier::query()->where('pin', $data['pin'])->where('active', true)->first();

        if (! $cashier && $data['pin'] !== Setting::get('pos_pin', '1234')) {
            return back()->withErrors(['pin' => 'الرقم السري غير صحيح']);
        }

        $request->session()->regenerate();
        $request->session()->put('pos_unlocked', true);
        $request->session()->put('pos_cashier_name', $cashier?->name);

        return redirect()->route('pos.index');
    }

    public function showAdminPinChallenge(): View
    {
        return view('auth.admin-pin');
    }

    public function verifyAdminPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string'],
        ], [], ['pin' => 'الرقم السري']);

        if ($data['pin'] !== Setting::get('admin_pin', '0000')) {
            return back()->withErrors(['pin' => 'الرقم السري غير صحيح']);
        }

        $intended = $request->session()->pull('admin_nav_intended', route('dashboard'));

        $request->session()->put('admin_nav_unlocked', true);

        return redirect()->to($intended);
    }
}
