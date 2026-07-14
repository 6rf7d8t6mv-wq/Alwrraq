<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'second_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email:rfc', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', 'unique:users,email'],
            'institution_name' => ['nullable', 'string', 'max:255', 'required_unless:institution_not_interested,1'],
            'institution_not_interested' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(6), 'regex:/^[A-Za-z0-9]+$/'],
        ]);

        $user = User::query()->create([
            'name' => trim($data['first_name'] . ' ' . $data['second_name']),
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'institution_name' => $request->boolean('institution_not_interested') ? null : $data['institution_name'],
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login_identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['login_identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (!Auth::attempt([$field => $data['login_identifier'], 'password' => $data['password']], true)) {
            return back()->withErrors([
                'login_identifier' => 'رقم الجوال أو البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ])->onlyInput('login_identifier');
        }

        if (! Auth::user()->canLogin()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'login_identifier' => 'هذا الحساب موقوف أو ممنوع من تسجيل الدخول.',
            ])->onlyInput('login_identifier');
        }

        $request->session()->regenerate();

        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
