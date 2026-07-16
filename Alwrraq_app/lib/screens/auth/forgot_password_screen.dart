import 'package:flutter/material.dart';

import 'auth_styles.dart';
import 'login_screen.dart';

class ForgotPasswordScreen extends StatelessWidget {
  const ForgotPasswordScreen({super.key});

  void _showMessage(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('تصميم فقط، لم يتم ربط استعادة كلمة المرور بعد'),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AuthPage(
      children: [
        const AuthTitle('استعادة كلمة المرور'),
        const SizedBox(height: 6),
        const AuthDescription(
          'ادخل رقم جوالك وسنجهز لاحقًا خطوات استعادة كلمة المرور.',
        ),
        const SizedBox(height: 16),
        const AuthLabel('رقم الجوال'),
        const SizedBox(height: 6),
        const AuthTextField(keyboardType: TextInputType.phone),
        const SizedBox(height: 18),
        AuthPrimaryButton(
          text: 'استعادة كلمة المرور',
          onPressed: () => _showMessage(context),
        ),
        const SizedBox(height: 16),
        const Divider(color: AuthColors.border, height: 1),
        const SizedBox(height: 16),
        const AuthDescription(
          'تذكرت كلمة المرور؟',
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 10),
        AuthSecondaryButton(
          text: 'العودة لتسجيل الدخول',
          onPressed: () {
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const LoginScreen()),
            );
          },
        ),
      ],
    );
  }
}
