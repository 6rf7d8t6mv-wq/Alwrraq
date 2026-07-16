import 'package:flutter/material.dart';

class CustomerHomeScreen extends StatelessWidget {
  const CustomerHomeScreen({super.key});

  static const _backgroundColor = Color(0xFFF3F4F6);
  static const _headerColor = Color(0xFF0F172A);
  static const _textColor = Color(0xFF111827);
  static const _mutedColor = Color(0xFF64748B);
  static const _borderColor = Color(0xFFE5E7EB);

  void _showDesignOnlyMessage(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تصميم مؤقت فقط، لا يوجد ربط API الآن')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: _backgroundColor,
        body: SafeArea(
          child: Column(
            children: [
              Container(
                color: _headerColor,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 18,
                ),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 1040),
                    child: Row(
                      children: [
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Mr-Student',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontFamily: 'Arial',
                                  fontSize: 22,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                              SizedBox(height: 4),
                              Text(
                                'صفحة العميل',
                                style: TextStyle(
                                  color: Color(0xFFCBD5E1),
                                  fontFamily: 'Arial',
                                  fontSize: 13,
                                  height: 1.6,
                                ),
                              ),
                            ],
                          ),
                        ),
                        OutlinedButton(
                          onPressed: () => Navigator.of(context).maybePop(),
                          style: OutlinedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: _headerColor,
                            side: const BorderSide(color: Colors.white),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(9),
                            ),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 10,
                            ),
                            textStyle: const TextStyle(
                              fontFamily: 'Arial',
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          child: const Text('تسجيل الخروج'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 28, 20, 28),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 1040),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const _CustomerIntroPanel(),
                          const SizedBox(height: 18),
                          Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: [
                              _CustomerActionCard(
                                title: 'لوحة الطلبات',
                                description:
                                    'ملخص سريع لحالة طلبات الطباعة والتجليد.',
                                onTap: () => _showDesignOnlyMessage(context),
                              ),
                              _CustomerActionCard(
                                title: 'طلب جديد',
                                description:
                                    'ابدأ طلب طباعة أو تجليد جديد بخطوات بسيطة.',
                                onTap: () => _showDesignOnlyMessage(context),
                              ),
                              _CustomerActionCard(
                                title: 'الطلبات السابقة',
                                description:
                                    'راجع الطلبات القديمة والفواتير والحالات.',
                                onTap: () => _showDesignOnlyMessage(context),
                              ),
                              _CustomerActionCard(
                                title: 'حالة الطلب',
                                description:
                                    'تابع الدفع، التسعير، التنفيذ، والاستلام.',
                                onTap: () => _showDesignOnlyMessage(context),
                              ),
                              _CustomerActionCard(
                                title: 'الملف الشخصي',
                                description:
                                    'راجع بياناتك ورقم الجوال والعنوان.',
                                onTap: () => _showDesignOnlyMessage(context),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CustomerIntroPanel extends StatelessWidget {
  const _CustomerIntroPanel();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: CustomerHomeScreen._borderColor),
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
            color: Color(0x140F172A),
            blurRadius: 45,
            offset: Offset(0, 18),
          ),
        ],
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'مرحبًا بك',
            style: TextStyle(
              color: CustomerHomeScreen._textColor,
              fontFamily: 'Arial',
              fontSize: 30,
              fontWeight: FontWeight.w900,
            ),
          ),
          SizedBox(height: 8),
          Text(
            'اختر الخدمة التي تريدها من صفحة العميل. البيانات مؤقتة حتى يتم ربط API لاحقًا.',
            style: TextStyle(
              color: CustomerHomeScreen._mutedColor,
              fontFamily: 'Arial',
              fontSize: 15,
              height: 1.7,
            ),
          ),
        ],
      ),
    );
  }
}

class _CustomerActionCard extends StatelessWidget {
  const _CustomerActionCard({
    required this.title,
    required this.description,
    required this.onTap,
  });

  final String title;
  final String description;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 330,
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              border: Border.all(color: CustomerHomeScreen._borderColor),
              borderRadius: BorderRadius.circular(12),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x0F0F172A),
                  blurRadius: 28,
                  offset: Offset(0, 12),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: CustomerHomeScreen._headerColor,
                    fontFamily: 'Arial',
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  description,
                  style: const TextStyle(
                    color: CustomerHomeScreen._mutedColor,
                    fontFamily: 'Arial',
                    fontSize: 14,
                    height: 1.7,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
