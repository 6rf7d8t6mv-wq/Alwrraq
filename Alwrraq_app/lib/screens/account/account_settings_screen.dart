import 'package:flutter/material.dart';

import '../../services/api_client.dart';

class AccountSettingsScreen extends StatefulWidget {
  const AccountSettingsScreen({super.key});

  static const _background = Color(0xFFF3F4F6);
  static const _dark = Color(0xFF0F172A);
  static const _text = Color(0xFF111827);
  static const _muted = Color(0xFF64748B);
  static const _border = Color(0xFFE5E7EB);

  @override
  State<AccountSettingsScreen> createState() => _AccountSettingsScreenState();
}

class _AccountSettingsScreenState extends State<AccountSettingsScreen> {
  Map<String, dynamic>? _user = ApiClient.user;
  var _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    setState(() => _isLoading = true);
    final response = await ApiClient.me();
    if (!mounted) return;
    setState(() {
      _isLoading = false;
      if (response['success'] == true &&
          response['user'] is Map<String, dynamic>) {
        _user = response['user'] as Map<String, dynamic>;
      }
    });
  }

  Future<void> _editProfile() async {
    final name = TextEditingController(text: _value('name'));
    final phone = TextEditingController(text: _value('phone'));
    final saved = await _showFormDialog(
      title: 'تعديل بياناتي',
      fields: [
        _DialogField(label: 'اسم المستخدم', controller: name),
        _DialogField(label: 'رقم الجوال', controller: phone),
      ],
      onSave: () => ApiClient.updateProfile(
        name: name.text.trim(),
        phone: phone.text.trim(),
      ),
    );
    if (saved) await _loadUser();
  }

  Future<void> _editAddress() async {
    final city = TextEditingController(text: _value('city'));
    final district = TextEditingController(text: _value('district'));
    final street = TextEditingController(text: _value('street'));
    final postalCode = TextEditingController(text: _value('postal_code'));
    final saved = await _showFormDialog(
      title: 'تعديل العنوان',
      fields: [
        _DialogField(label: 'المدينة أو المحافظة', controller: city),
        _DialogField(label: 'الحي', controller: district),
        _DialogField(label: 'الشارع', controller: street),
        _DialogField(label: 'الرمز البريدي', controller: postalCode),
      ],
      onSave: () => ApiClient.updateAddress(
        city: city.text.trim(),
        district: district.text.trim(),
        street: street.text.trim(),
        postalCode: postalCode.text.trim(),
      ),
    );
    if (saved) await _loadUser();
  }

  Future<void> _changePassword() async {
    final current = TextEditingController();
    final password = TextEditingController();
    final confirmation = TextEditingController();
    await _showFormDialog(
      title: 'تغيير كلمة المرور',
      fields: [
        _DialogField(
          label: 'كلمة المرور الحالية',
          controller: current,
          obscureText: true,
        ),
        _DialogField(
          label: 'كلمة المرور الجديدة',
          controller: password,
          obscureText: true,
        ),
        _DialogField(
          label: 'تأكيد كلمة المرور',
          controller: confirmation,
          obscureText: true,
        ),
      ],
      onSave: () => ApiClient.updatePassword(
        currentPassword: current.text,
        password: password.text,
        passwordConfirmation: confirmation.text,
      ),
    );
  }

  Future<bool> _showFormDialog({
    required String title,
    required List<_DialogField> fields,
    required Future<Map<String, dynamic>> Function() onSave,
  }) async {
    var isSaving = false;
    final result = await showDialog<bool>(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return Directionality(
              textDirection: TextDirection.rtl,
              child: AlertDialog(
                title: Text(title),
                content: SizedBox(
                  width: 420,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: fields
                          .map(
                            (field) => Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: TextField(
                                controller: field.controller,
                                obscureText: field.obscureText,
                                decoration: InputDecoration(
                                  labelText: field.label,
                                  border: const OutlineInputBorder(),
                                ),
                              ),
                            ),
                          )
                          .toList(),
                    ),
                  ),
                ),
                actions: [
                  TextButton(
                    onPressed: isSaving
                        ? null
                        : () => Navigator.pop(context, false),
                    child: const Text('إلغاء'),
                  ),
                  FilledButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            setDialogState(() => isSaving = true);
                            final response = await onSave();
                            if (!context.mounted) return;
                            setDialogState(() => isSaving = false);
                            if (response['success'] == true) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    response['message']?.toString() ??
                                        'تم الحفظ بنجاح',
                                  ),
                                ),
                              );
                              Navigator.pop(context, true);
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    response['message']?.toString() ??
                                        'تعذر الحفظ',
                                  ),
                                ),
                              );
                            }
                          },
                    child: Text(isSaving ? 'جاري الحفظ...' : 'حفظ'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
    return result == true;
  }

  String _value(String key, {String fallback = 'لم تتم الإضافة بعد'}) {
    final value = _user?[key]?.toString();
    return value == null || value.isEmpty ? fallback : value;
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: AccountSettingsScreen._background,
        appBar: AppBar(
          backgroundColor: AccountSettingsScreen._dark,
          foregroundColor: Colors.white,
          title: const Text('الورّاق'),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 900),
              child: _Panel(
                child: _isLoading && _user == null
                    ? const Center(child: CircularProgressIndicator())
                    : Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'إعدادات الحساب',
                            style: TextStyle(
                              color: AccountSettingsScreen._text,
                              fontFamily: 'Arial',
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'راجع بياناتك وعدّلها عند الحاجة.',
                            style: TextStyle(
                              color: AccountSettingsScreen._muted,
                            ),
                          ),
                          const SizedBox(height: 22),
                          _Section(
                            title: 'بياناتي',
                            button: 'تعديل بياناتي',
                            onPressed: _editProfile,
                            children: [
                              _Detail(
                                label: 'اسم المستخدم',
                                value: _value('name', fallback: '-'),
                              ),
                              _Detail(
                                label: 'رقم الجوال',
                                value: _value('phone', fallback: '-'),
                              ),
                            ],
                          ),
                          _Section(
                            title: 'عنواني',
                            button: 'تعديل عنواني',
                            onPressed: _editAddress,
                            children: [
                              const _Detail(
                                label: 'الدولة',
                                value: 'المملكة العربية السعودية',
                              ),
                              _Detail(
                                label: 'المدينة أو المحافظة',
                                value: _value('city'),
                              ),
                              _Detail(label: 'الحي', value: _value('district')),
                              _Detail(label: 'الشارع', value: _value('street')),
                              _Detail(
                                label: 'الرمز البريدي',
                                value: _value('postal_code'),
                              ),
                            ],
                          ),
                          const SizedBox(height: 22),
                          OutlinedButton(
                            onPressed: _changePassword,
                            child: const Text('تغيير كلمة المرور'),
                          ),
                        ],
                      ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _DialogField {
  const _DialogField({
    required this.label,
    required this.controller,
    this.obscureText = false,
  });

  final String label;
  final TextEditingController controller;
  final bool obscureText;
}

class _Panel extends StatelessWidget {
  const _Panel({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: AccountSettingsScreen._border),
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
            color: Color(0x140F172A),
            blurRadius: 45,
            offset: Offset(0, 18),
          ),
        ],
      ),
      child: child,
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({
    required this.title,
    required this.button,
    required this.onPressed,
    required this.children,
  });

  final String title;
  final String button;
  final VoidCallback onPressed;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(top: 22, bottom: 22),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: AccountSettingsScreen._border)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    color: AccountSettingsScreen._dark,
                    fontFamily: 'Arial',
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              OutlinedButton(onPressed: onPressed, child: Text(button)),
            ],
          ),
          const SizedBox(height: 14),
          Wrap(spacing: 12, runSpacing: 12, children: children),
        ],
      ),
    );
  }
}

class _Detail extends StatelessWidget {
  const _Detail({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 250,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFC),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(color: AccountSettingsScreen._muted),
            ),
            const SizedBox(height: 6),
            Text(
              value,
              style: const TextStyle(
                color: AccountSettingsScreen._text,
                fontFamily: 'Arial',
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
