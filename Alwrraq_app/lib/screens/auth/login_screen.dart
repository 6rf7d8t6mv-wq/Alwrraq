import 'package:flutter/material.dart';

import '../../services/api_client.dart';
import 'auth_styles.dart';
import 'register_screen.dart';
import '../grades/grades_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  var _isLoading = false;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);
    final error = await ApiClient.login(
      phone: _phoneController.text.trim(),
      password: _passwordController.text,
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (error == null) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const GradesScreen()),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error)));
  }

  @override
  Widget build(BuildContext context) {
    return AuthPage(
      children: [
        const AuthTitle('تسجيل الدخول'),
        const SizedBox(height: 6),
        const AuthDescription('ادخل رقم جوالك وكلمة المرور للمتابعة.'),
        const SizedBox(height: 16),
        const AuthLabel('رقم الجوال'),
        const SizedBox(height: 6),
        AuthTextField(
          controller: _phoneController,
          keyboardType: TextInputType.phone,
          enabled: !_isLoading,
        ),
        const SizedBox(height: 14),
        const AuthLabel('كلمة المرور'),
        const SizedBox(height: 6),
        AuthTextField(
          controller: _passwordController,
          obscureText: true,
          enabled: !_isLoading,
        ),
        const SizedBox(height: 18),
        AuthPrimaryButton(
          text: _isLoading ? 'جاري الدخول...' : 'دخول',
          onPressed: _isLoading ? null : _login,
        ),
        const SizedBox(height: 16),
        const Divider(color: AuthColors.border, height: 1),
        const SizedBox(height: 16),
        const AuthDescription('ليس لديك حساب؟', textAlign: TextAlign.center),
        const SizedBox(height: 10),
        AuthSecondaryButton(
          text: 'إنشاء حساب جديد',
          onPressed: () {
            Navigator.of(
              context,
            ).push(MaterialPageRoute(builder: (_) => const RegisterScreen()));
          },
        ),
      ],
    );
  }
}
