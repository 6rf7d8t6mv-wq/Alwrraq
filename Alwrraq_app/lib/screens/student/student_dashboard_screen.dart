import 'package:flutter/material.dart';

class OrdersDashboardScreen extends StatelessWidget {
  const OrdersDashboardScreen({super.key});

  static const _backgroundColor = Color(0xFFF3F4F6);
  static const _headerColor = Color(0xFF0F172A);
  static const _textColor = Color(0xFF111827);
  static const _mutedColor = Color(0xFF64748B);
  static const _labelColor = Color(0xFF334155);
  static const _borderColor = Color(0xFFE5E7EB);
  static const _softPanelColor = Color(0xFFF8FAFC);

  static const _orders = [
    _StudentOrder(
      id: '#1042',
      service: 'مذكرات',
      filesCount: 3,
      paymentStatus: 'غير مدفوع',
      orderStatus: 'بانتظار الدفع',
      total: '85 ريال',
      date: '2026-07-08',
      action: 'إكمال الدفع',
      isPaid: false,
      isCompleted: false,
      isCancelled: false,
    ),
    _StudentOrder(
      id: '#1038',
      service: 'ماجستير',
      filesCount: 1,
      paymentStatus: 'مدفوع',
      orderStatus: 'قيد التنفيذ',
      total: '140 ريال',
      date: '2026-07-03',
      action: 'عرض الطلب',
      isPaid: true,
      isCompleted: false,
      isCancelled: false,
    ),
    _StudentOrder(
      id: '#1029',
      service: 'دكتوراه',
      filesCount: 2,
      paymentStatus: 'مدفوع',
      orderStatus: 'مكتمل',
      total: '210 ريال',
      date: '2026-06-26',
      action: 'عرض الطلب',
      isPaid: true,
      isCompleted: true,
      isCancelled: false,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: _backgroundColor,
        body: SafeArea(
          child: Column(
            children: [
              const _DashboardHeader(),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 28, 20, 28),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 1040),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const _PageIntro(),
                          const SizedBox(height: 18),
                          const _StatsGrid(),
                          const SizedBox(height: 18),
                          const _ProfilePanel(),
                          const SizedBox(height: 18),
                          _OrdersPanel(orders: _orders),
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

class _DashboardHeader extends StatelessWidget {
  const _DashboardHeader();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: OrdersDashboardScreen._headerColor,
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 1040),
          child: Row(
            children: [
              const Expanded(
                child: Text(
                  'الورّاق',
                  style: TextStyle(
                    color: Colors.white,
                    fontFamily: 'Arial',
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              OutlinedButton(
                onPressed: () => Navigator.of(context).maybePop(),
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: OrdersDashboardScreen._headerColor,
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
                child: const Text('العودة للصفحة الرئيسية'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PageIntro extends StatelessWidget {
  const _PageIntro();

  @override
  Widget build(BuildContext context) {
    return const _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'لوحة الطلبات',
            style: TextStyle(
              color: OrdersDashboardScreen._textColor,
              fontFamily: 'Arial',
              fontSize: 30,
              fontWeight: FontWeight.w900,
            ),
          ),
          SizedBox(height: 8),
          Text(
            'تابع طلباتك، حالة الدفع، والملفات المرسلة للطباعة والتجليد.',
            style: TextStyle(
              color: OrdersDashboardScreen._mutedColor,
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

class _StatsGrid extends StatelessWidget {
  const _StatsGrid();

  @override
  Widget build(BuildContext context) {
    final stats = [
      const _StatItem(label: 'إجمالي الطلبات', value: '3'),
      const _StatItem(label: 'طلبات قيد التنفيذ', value: '1'),
      const _StatItem(label: 'طلبات غير مدفوعة', value: '1'),
      const _StatItem(label: 'إجمالي المدفوعات', value: '350 ريال'),
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final columns = constraints.maxWidth < 720 ? 2 : 4;

        return GridView.count(
          crossAxisCount: columns,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: constraints.maxWidth < 720 ? 1.7 : 1.55,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          children: stats,
        );
      },
    );
  }
}

class _StatItem extends StatelessWidget {
  const _StatItem({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: OrdersDashboardScreen._mutedColor,
              fontFamily: 'Arial',
              fontSize: 12,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              color: OrdersDashboardScreen._textColor,
              fontFamily: 'Arial',
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfilePanel extends StatelessWidget {
  const _ProfilePanel();

  @override
  Widget build(BuildContext context) {
    return const _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _SectionTitle('بياناتي'),
          SizedBox(height: 14),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              _DetailTile(label: 'اسم الطالب', value: 'أسامة محمد'),
              _DetailTile(label: 'رقم الجوال', value: '05XXXXXXXX'),
              _DetailTile(label: 'المدينة', value: 'الرياض'),
              _DetailTile(label: 'العنوان', value: 'حي النخيل - شارع الجامعة'),
            ],
          ),
        ],
      ),
    );
  }
}

class _OrdersPanel extends StatelessWidget {
  const _OrdersPanel({required this.orders});

  final List<_StudentOrder> orders;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const _SectionTitle('طلباتي'),
          const SizedBox(height: 6),
          const Text(
            'بيانات وهمية مؤقتة لعرض شكل لوحة الطلبات فقط.',
            style: TextStyle(
              color: OrdersDashboardScreen._mutedColor,
              fontFamily: 'Arial',
              fontSize: 14,
              height: 1.7,
            ),
          ),
          const SizedBox(height: 16),
          ...orders.map((order) => _OrderCard(order: order)),
        ],
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});

  final _StudentOrder order;

  @override
  Widget build(BuildContext context) {
    final paymentBadge = order.isPaid
        ? const _Badge(
            text: 'مدفوع',
            backgroundColor: Color(0xFFDCFCE7),
            textColor: Color(0xFF166534),
          )
        : const _Badge(
            text: 'غير مدفوع',
            backgroundColor: Color(0xFFFEF3C7),
            textColor: Color(0xFF92400E),
          );

    final statusBadge = order.isCancelled
        ? _Badge(
            text: order.orderStatus,
            backgroundColor: const Color(0xFFFEE2E2),
            textColor: const Color(0xFF991B1B),
          )
        : order.isCompleted
        ? _Badge(
            text: order.orderStatus,
            backgroundColor: const Color(0xFFE0F2FE),
            textColor: const Color(0xFF075985),
          )
        : _Badge(
            text: order.orderStatus,
            backgroundColor: const Color(0xFFF1F5F9),
            textColor: OrdersDashboardScreen._labelColor,
          );

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: OrdersDashboardScreen._softPanelColor,
        border: Border.all(color: OrdersDashboardScreen._borderColor),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${order.id} - ${order.service}',
                    style: const TextStyle(
                      color: OrdersDashboardScreen._textColor,
                      fontFamily: 'Arial',
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                Text(
                  order.date,
                  style: const TextStyle(
                    color: OrdersDashboardScreen._mutedColor,
                    fontFamily: 'Arial',
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                _InfoChip(label: 'الملفات', value: '${order.filesCount}'),
                _InfoChip(label: 'الإجمالي', value: order.total),
                paymentBadge,
                statusBadge,
              ],
            ),
            const SizedBox(height: 12),
            Align(
              alignment: AlignmentDirectional.centerStart,
              child: FilledButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('تصميم فقط، لا يوجد ربط API الآن'),
                    ),
                  );
                },
                style: FilledButton.styleFrom(
                  backgroundColor: order.isPaid
                      ? OrdersDashboardScreen._headerColor
                      : const Color(0xFF047857),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 9,
                  ),
                  textStyle: const TextStyle(
                    fontFamily: 'Arial',
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                child: Text(order.action),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.child, this.padding = const EdgeInsets.all(22)});

  final Widget child;
  final EdgeInsets padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: OrdersDashboardScreen._borderColor),
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

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        color: OrdersDashboardScreen._headerColor,
        fontFamily: 'Arial',
        fontSize: 20,
        fontWeight: FontWeight.w900,
      ),
    );
  }
}

class _DetailTile extends StatelessWidget {
  const _DetailTile({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 220,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
        decoration: BoxDecoration(
          color: OrdersDashboardScreen._softPanelColor,
          border: Border.all(color: const Color(0xFFE2E8F0)),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(
                color: OrdersDashboardScreen._mutedColor,
                fontFamily: 'Arial',
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              value,
              style: const TextStyle(
                color: OrdersDashboardScreen._textColor,
                fontFamily: 'Arial',
                fontSize: 14,
                fontWeight: FontWeight.w800,
                height: 1.7,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: OrdersDashboardScreen._borderColor),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        '$label: $value',
        style: const TextStyle(
          color: OrdersDashboardScreen._labelColor,
          fontFamily: 'Arial',
          fontSize: 12,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  const _Badge({
    required this.text,
    required this.backgroundColor,
    required this.textColor,
  });

  final String text;
  final Color backgroundColor;
  final Color textColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontFamily: 'Arial',
          fontSize: 12,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}

class _StudentOrder {
  const _StudentOrder({
    required this.id,
    required this.service,
    required this.filesCount,
    required this.paymentStatus,
    required this.orderStatus,
    required this.total,
    required this.date,
    required this.action,
    required this.isPaid,
    required this.isCompleted,
    required this.isCancelled,
  });

  final String id;
  final String service;
  final int filesCount;
  final String paymentStatus;
  final String orderStatus;
  final String total;
  final String date;
  final String action;
  final bool isPaid;
  final bool isCompleted;
  final bool isCancelled;
}
