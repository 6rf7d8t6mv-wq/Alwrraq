import 'package:flutter/material.dart';

class AuthColors {
  static const background = Color(0xFFF3F4F6);
  static const text = Color(0xFF111827);
  static const muted = Color(0xFF64748B);
  static const label = Color(0xFF334155);
  static const border = Color(0xFFE5E7EB);
  static const inputBorder = Color(0xFFCBD5E1);
  static const button = Color(0xFF0F172A);
}

class AuthPage extends StatelessWidget {
  const AuthPage({super.key, required this.children});

  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: AuthColors.background,
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
                    border: Border.all(color: AuthColors.border),
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
                      const AuthBrandHeader(),
                      const SizedBox(height: 22),
                      ...children,
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

class AuthBrandHeader extends StatelessWidget {
  const AuthBrandHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return const Column(
      children: [
        Text(
          'Mr-Student',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AuthColors.text,
            fontFamily: 'Arial',
            fontSize: 28,
            fontWeight: FontWeight.w700,
          ),
        ),
        SizedBox(height: 6),
        Text(
          'خدمات الطباعة والتجليد',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AuthColors.muted,
            fontFamily: 'Arial',
            fontSize: 15,
            height: 1.7,
          ),
        ),
      ],
    );
  }
}

class AuthTitle extends StatelessWidget {
  const AuthTitle(this.text, {super.key});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        color: AuthColors.text,
        fontFamily: 'Arial',
        fontSize: 22,
        fontWeight: FontWeight.w700,
      ),
    );
  }
}

class AuthDescription extends StatelessWidget {
  const AuthDescription(this.text, {super.key, this.textAlign});

  final String text;
  final TextAlign? textAlign;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      textAlign: textAlign,
      style: const TextStyle(
        color: AuthColors.muted,
        fontFamily: 'Arial',
        fontSize: 15,
        height: 1.7,
      ),
    );
  }
}

class AuthLabel extends StatelessWidget {
  const AuthLabel(this.text, {super.key});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        color: AuthColors.label,
        fontFamily: 'Arial',
        fontSize: 13,
        fontWeight: FontWeight.w700,
      ),
    );
  }
}

class AuthTextField extends StatelessWidget {
  const AuthTextField({
    super.key,
    this.controller,
    this.keyboardType,
    this.obscureText = false,
    this.enabled = true,
  });

  final TextEditingController? controller;
  final TextInputType? keyboardType;
  final bool obscureText;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      enabled: enabled,
      keyboardType: keyboardType,
      obscureText: obscureText,
      style: const TextStyle(
        color: AuthColors.text,
        fontFamily: 'Arial',
        fontSize: 15,
      ),
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.all(12),
        enabledBorder: OutlineInputBorder(
          borderSide: const BorderSide(color: AuthColors.inputBorder),
          borderRadius: BorderRadius.circular(9),
        ),
        focusedBorder: OutlineInputBorder(
          borderSide: const BorderSide(color: AuthColors.button, width: 1.2),
          borderRadius: BorderRadius.circular(9),
        ),
        border: OutlineInputBorder(
          borderSide: const BorderSide(color: AuthColors.inputBorder),
          borderRadius: BorderRadius.circular(9),
        ),
      ),
    );
  }
}

class AuthPrimaryButton extends StatelessWidget {
  const AuthPrimaryButton({super.key, required this.text, this.onPressed});

  final String text;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: FilledButton(
        onPressed: onPressed,
        style: FilledButton.styleFrom(
          backgroundColor: AuthColors.button,
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

class AuthSecondaryButton extends StatelessWidget {
  const AuthSecondaryButton({super.key, required this.text, this.onPressed});

  final String text;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: OutlinedButton(
        onPressed: onPressed,
        style: OutlinedButton.styleFrom(
          foregroundColor: AuthColors.button,
          side: const BorderSide(color: AuthColors.inputBorder),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 9),
          textStyle: const TextStyle(
            fontFamily: 'Arial',
            fontWeight: FontWeight.w700,
          ),
        ),
        child: Text(text),
      ),
    );
  }
}
