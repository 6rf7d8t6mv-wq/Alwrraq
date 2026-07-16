import 'package:flutter/material.dart';

import '../../services/api_client.dart';
import 'auth_styles.dart';
import '../grades/grades_screen.dart';
import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmationController = TextEditingController();
  var _isLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _passwordConfirmationController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);
    final error = await ApiClient.register(
      name: _nameController.text.trim(),
      phone: _phoneController.text.trim(),
      password: _passwordController.text,
      passwordConfirmation: _passwordConfirmationController.text,
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
        const AuthTitle('إنشاء حساب'),
        const SizedBox(height: 6),
        const AuthDescription('أنشئ حسابك لإرسال ملفات الطباعة ومتابعة طلبك.'),
        const SizedBox(height: 16),
        const AuthLabel('الاسم'),
        const SizedBox(height: 6),
        AuthTextField(controller: _nameController, enabled: !_isLoading),
        const SizedBox(height: 14),
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
        const SizedBox(height: 14),
        const AuthLabel('تأكيد كلمة المرور'),
        const SizedBox(height: 6),
        AuthTextField(
          controller: _passwordConfirmationController,
          obscureText: true,
          enabled: !_isLoading,
        ),
        const SizedBox(height: 18),
        AuthPrimaryButton(
          text: _isLoading ? 'جاري إنشاء الحساب...' : 'إنشاء الحساب',
          onPressed: _isLoading ? null : _register,
        ),
        const SizedBox(height: 16),
        const Divider(color: AuthColors.border, height: 1),
        const SizedBox(height: 16),
        const AuthDescription('لديك حساب بالفعل؟', textAlign: TextAlign.center),
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
