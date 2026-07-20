<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth');
    }

    public function showAppLogin()
    {
        if (Auth::check()) {
            session(['auth_surface' => 'app']);

            return $this->redirectAfterLogin(Auth::user());
        }

        return view('auth', ['appMode' => true]);
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return app(AdminController::class)->dashboard();
            }

            return redirect()->route('home');
        }

        return view('admin.login');
    }

    public function register(Request $request)
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'second_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email:rfc', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', 'unique:users,email'],
            'institution_name' => ['nullable', 'string', 'max:255', 'required_unless:institution_not_interested,1'],
            'institution_not_interested' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(6), 'regex:/^[\x21-\x7E]+$/'],
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

        if ($request->routeIs('app.register.store')) {
            $request->session()->put('auth_surface', 'app');
        }

        return redirect()->route('home');
    }

    public function appLogin(Request $request)
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            // Keep legacy accounts usable in the unified app login. New customer
            // registration still requires a Saudi mobile number starting with 05.
            'login_identifier' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'regex:/^[\x21-\x7E]+$/'],
        ]);

        if (! Auth::attempt(['phone' => $data['login_identifier'], 'password' => $data['password']], true)) {
            return back()->withErrors([
                'login_identifier' => 'رقم الجوال أو كلمة المرور غير صحيحة',
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
        $request->session()->put('auth_surface', 'app');

        return $this->redirectAfterLogin(Auth::user());
    }

    public function login(Request $request)
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'login_identifier' => ['required', 'string', 'regex:/^[\x21-\x7E]+$/'],
            'password' => ['required', 'string', 'regex:/^[\x21-\x7E]+$/'],
        ]);

        $field = filter_var($data['login_identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (!Auth::attempt([$field => $data['login_identifier'], 'password' => $data['password']], true)) {
            return back()->withErrors([
                'login_identifier' => 'رقم الجوال أو البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ])->onlyInput('login_identifier');
        }

        if (Auth::user()->role === 'admin') {
            Auth::logout();

            return back()->withErrors([
                'login_identifier' => 'حسابات الإدارة تسجل الدخول من صفحة المدير فقط.',
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

    public function adminLogin(Request $request)
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'login_identifier' => ['required', 'string', 'regex:/^[\x21-\x7E]+$/'],
            'password' => ['required', 'string', 'regex:/^[\x21-\x7E]+$/'],
        ]);

        $field = filter_var($data['login_identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (!Auth::attempt([$field => $data['login_identifier'], 'password' => $data['password']], true)) {
            return back()->withErrors([
                'login_identifier' => 'بيانات مدير النظام غير صحيحة',
            ])->onlyInput('login_identifier');
        }

        if (Auth::user()->role !== 'admin') {
            Auth::logout();

            return back()->withErrors([
                'login_identifier' => 'هذه الصفحة مخصصة لدخول الإدارة فقط.',
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

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $isAppSession = $request->session()->get('auth_surface') === 'app';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAppSession) {
            return redirect()->route('app.entry');
        }

        return $request->is('admin/*') || str_starts_with((string) url()->previous(), url('/admin'))
            ? redirect()->route('admin.dashboard')
            : redirect()->route('login');
    }

    private function redirectAfterLogin(User $user)
    {
        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }

    private function normalizeAuthInput(Request $request): void
    {
        $fields = ['login_identifier', 'phone', 'email', 'password', 'password_confirmation'];
        $normalized = [];

        foreach ($fields as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $value = (string) $request->input($field);
            $value = $this->convertArabicDigits($value);

            if ($field === 'phone') {
                $value = preg_replace('/[^0-9]/', '', $value) ?? '';
            } elseif ($field === 'email') {
                $value = preg_replace('/[^A-Za-z0-9._%+\-@]/', '', $value) ?? '';
            } else {
                $value = preg_replace('/[^\x21-\x7E]/', '', $value) ?? '';
            }

            $normalized[$field] = $value;
        }

        if ($normalized !== []) {
            $request->merge($normalized);
        }
    }

    private function convertArabicDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);
    }
}
