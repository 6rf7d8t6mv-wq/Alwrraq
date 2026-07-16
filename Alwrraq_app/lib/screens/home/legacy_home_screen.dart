import 'package:flutter/material.dart';

class LegacyHomeScreen extends StatelessWidget {
  const LegacyHomeScreen({super.key});

  void _showMessage(BuildContext context, String text) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(text)));
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: const Color(0xFFF3F4F6),
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 430),
                child: Container(
                  padding: const EdgeInsets.all(26),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: const [
                      BoxShadow(
                        color: Color(0x1A0F172A),
                        blurRadius: 55,
                        offset: Offset(0, 22),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text(
                        'الورّاق',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Color(0xFF111827),
                          fontFamily: 'Arial',
                          fontSize: 30,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'خدمات الطباعة والتجليد',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Color(0xFF64748B),
                          fontFamily: 'Arial',
                          fontSize: 15,
                          height: 1.7,
                        ),
                      ),
                      const SizedBox(height: 28),
                      _HomeButton(
                        text: 'تسجيل الدخول',
                        onPressed: () =>
                            _showMessage(context, 'تسجيل الدخول قريبًا'),
                      ),
                      const SizedBox(height: 12),
                      _HomeButton(
                        text: 'إنشاء حساب',
                        onPressed: () =>
                            _showMessage(context, 'إنشاء حساب قريبًا'),
                      ),
                      const SizedBox(height: 12),
                      _HomeButton(
                        text: 'لوحة الطالب',
                        onPressed: () =>
                            _showMessage(context, 'لوحة الطالب قريبًا'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _HomeButton extends StatelessWidget {
  const _HomeButton({required this.text, required this.onPressed});

  final String text;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 50,
      child: FilledButton(
        onPressed: onPressed,
        style: FilledButton.styleFrom(
          backgroundColor: const Color(0xFF0F172A),
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          textStyle: const TextStyle(
            fontFamily: 'Arial',
            fontSize: 15,
            fontWeight: FontWeight.w800,
          ),
        ),
        child: Text(text),
      ),
    );
  }
}
