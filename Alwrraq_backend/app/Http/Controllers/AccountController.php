<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit()
    {
        return view('account.settings', [
            'saudiCities' => $this->saudiCities(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'second_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'email:rfc', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', Rule::unique('users', 'email')->ignore($user->id)],
            'institution_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => trim($data['first_name'] . ' ' . $data['second_name']),
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'institution_name' => filled($data['institution_name'] ?? null)
                ? trim($data['institution_name'])
                : null,
        ]);

        return redirect()->route('account.settings')->with('status', 'تم تحديث بياناتك بنجاح.');
    }

    public function updateAddress(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'city' => ['required', 'string', 'max:120', Rule::in($this->saudiCities())],
            'district' => ['required', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:180'],
            'postal_code' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ]);

        $data['address'] = implode(' - ', [
            'المملكة العربية السعودية',
            $data['city'],
            $data['district'],
            $data['street'],
            $data['postal_code'],
        ]);

        $user->update($data);

        return redirect()->route('account.settings')->with('status', 'تم تحديث عنوانك بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6), 'regex:/^[A-Za-z0-9]+$/'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'كلمة المرور القديمة غير صحيحة.',
            ]);
        }

        $user->update([
            'password' => $data['password'],
        ]);

        return redirect()->route('account.settings')->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin' && $user->admin_permissions === null) {
            return back()->withErrors([
                'account' => 'لا يمكن حذف حساب مدير النظام الأساسي.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('login')->with('status', 'تم حذف حسابك بنجاح.');
    }

    private function saudiCities(): array
    {
        return [
            'الرياض',
            'الدرعية',
            'الخرج',
            'الدوادمي',
            'المجمعة',
            'القويعية',
            'وادي الدواسر',
            'الأفلاج',
            'الزلفي',
            'شقراء',
            'حوطة بني تميم',
            'عفيف',
            'الغاط',
            'السليل',
            'ضرما',
            'المزاحمية',
            'رماح',
            'ثادق',
            'حريملاء',
            'الحريق',
            'مرات',
            'الدلم',
            'الرين',
            'جدة',
            'مكة المكرمة',
            'الطائف',
            'القنفذة',
            'الليث',
            'رابغ',
            'خليص',
            'الخرمة',
            'رنية',
            'تربة',
            'الجموم',
            'الكامل',
            'المويه',
            'ميسان',
            'أضم',
            'العرضيات',
            'بحرة',
            'المدينة المنورة',
            'ينبع',
            'العلا',
            'المهد',
            'الحناكية',
            'بدر',
            'خيبر',
            'العيص',
            'وادي الفرع',
            'الدمام',
            'الخبر',
            'الظهران',
            'الأحساء',
            'حفر الباطن',
            'الجبيل',
            'القطيف',
            'الخفجي',
            'رأس تنورة',
            'بقيق',
            'النعيرية',
            'قرية العليا',
            'العديد',
            'القيصومة',
            'سيهات',
            'صفوى',
            'تاروت',
            'عنك',
            'حائل',
            'بقعاء',
            'الغزالة',
            'الشنان',
            'الحائط',
            'السليمي',
            'الشملي',
            'موقق',
            'سميراء',
            'عرعر',
            'رفحاء',
            'طريف',
            'العويقيلة',
            'بريدة',
            'عنيزة',
            'الرس',
            'المذنب',
            'البكيرية',
            'البدائع',
            'الأسياح',
            'النبهانية',
            'الشماسية',
            'عيون الجواء',
            'رياض الخبراء',
            'عقلة الصقور',
            'ضرية',
            'الخبراء',
            'نجران',
            'شرورة',
            'حبونا',
            'بدر الجنوب',
            'يدمة',
            'ثار',
            'خباش',
            'الخرخير',
            'جازان',
            'صبيا',
            'أبو عريش',
            'صامطة',
            'بيش',
            'الدرب',
            'الحرث',
            'ضمد',
            'الريث',
            'فرسان',
            'الدائر',
            'العارضة',
            'أحد المسارحة',
            'العيدابي',
            'فيفاء',
            'الطوال',
            'هروب',
            'الشقيري',
            'أبها',
            'خميس مشيط',
            'بيشة',
            'النماص',
            'محايل عسير',
            'ظهران الجنوب',
            'تثليث',
            'سراة عبيدة',
            'رجال ألمع',
            'بلقرن',
            'أحد رفيدة',
            'المجاردة',
            'البرك',
            'بارق',
            'تنومة',
            'طريب',
            'الحرجة',
            'تبوك',
            'تيماء',
            'أملج',
            'الوجه',
            'ضباء',
            'حقل',
            'البدع',
            'الباحة',
            'بلجرشي',
            'المندق',
            'المخواة',
            'قلوة',
            'العقيق',
            'القرى',
            'غامد الزناد',
            'الحجرة',
            'بني حسن',
            'سكاكا',
            'القريات',
            'دومة الجندل',
            'طبرجل',
            'ميقوع',
        ];
    }
}
