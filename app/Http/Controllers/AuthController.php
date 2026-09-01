<?php

namespace App\Http\Controllers;

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

        $posPin = Setting::get('pos_pin', '1234');

        if ($data['pin'] !== $posPin) {
            return back()->withErrors(['pin' => 'الرقم السري غير صحيح']);
        }

        $request->session()->regenerate();
        $request->session()->put('pos_unlocked', true);

        return redirect()->route('pos.index');
    }
}
