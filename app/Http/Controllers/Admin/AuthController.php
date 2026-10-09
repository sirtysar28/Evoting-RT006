<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Halaman login admin */
    public function showLogin()
    {
        if (session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /** Proses login + validasi captcha 5 karakter */
    public function login(Request $request)
    {
        $request->validate([
            'username'  => ['required', 'string'],
            'password'  => ['required', 'string'],
            'captcha'   => ['required', 'string', 'size:5'],
        ], [
            'captcha.size' => 'Kode captcha harus 5 karakter.',
        ]);

        // ---- Validasi CAPTCHA ----
        if (strtoupper($request->captcha) !== strtoupper((string) session('captcha_code'))) {
            return back()->withErrors(['captcha' => 'Kode captcha salah!'])->withInput($request->only('username'));
        }
        session()->forget('captcha_code'); // sekali pakai

        // ---- Validasi kredensial ----
        $admin = Admin::where('username', $request->username)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['username' => 'Username atau password salah!'])->withInput($request->only('username'));
        }

        session([
            'admin_id'   => $admin->id,
            'admin_name' => $admin->name,
        ]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Selamat datang, ' . $admin->name . '!');
    }

    public function logout(Request $request)
    {
        session()->forget(['admin_id', 'admin_name']);
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Anda telah keluar dari sistem.');
    }
}
