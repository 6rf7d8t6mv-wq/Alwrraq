import 'package:flutter/material.dart';

import '../../services/api_client.dart';
import '../cart/cart_screen.dart';

class MyOrdersScreen extends StatefulWidget {
  const MyOrdersScreen({super.key});

  static const _background = Color(0xFFF3F4F6);
  static const _dark = Color(0xFF0F172A);
  static const _text = Color(0xFF111827);
  static const _muted = Color(0xFF64748B);
  static const _border = Color(0xFFE5E7EB);

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  var _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _orders = [];

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final response = await ApiClient.orders();
    if (!mounted) return;

    setState(() {
      _isLoading = false;
      if (response['success'] == true && response['orders'] is List) {
        _orders = (response['orders'] as List)
            .whereType<Map<String, dynamic>>()
            .toList();
      } else {
        _error = response['message']?.toString() ?? 'تعذر تحميل الطلبات';
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: MyOrdersScreen._background,
        appBar: AppBar(
          backgroundColor: MyOrdersScreen._dark,
          foregroundColor: Colors.white,
          title: const Text('الورّاق'),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1040),
              child: _Panel(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Expanded(
                          child: Text(
                            'طلباتي',
                            style: TextStyle(
                              color: MyOrdersScreen._text,
                              fontFamily: 'Arial',
                              fontSize: 30,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _loadOrders,
                          icon: const Icon(Icons.refresh),
                          tooltip: 'تحديث',
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'تابع حالة طلباتك، الدفع، وهل اكتمل الطلب أو ما زال قيد التنفيذ.',
                      style: TextStyle(
                        color: MyOrdersScreen._muted,
                        height: 1.7,
                      ),
                    ),
                    const SizedBox(height: 18),
                    if (_isLoading)
                      const Center(child: CircularProgressIndicator())
                    else if (_error != null)
                      Text(
                        _error!,
                        style: const TextStyle(
                          color: Color(0xFFB91C1C),
                          fontWeight: FontWeight.w800,
                        ),
                      )
                    else if (_orders.isEmpty)
                      const Text(
                        'لا توجد طلبات حتى الآن.',
                        style: TextStyle(
                          color: MyOrdersScreen._muted,
                          fontWeight: FontWeight.w800,
                        ),
                      )
                    else
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: DataTable(
                          headingRowColor: WidgetStateProperty.all(
                            const Color(0xFFF8FAFC),
                          ),
                          columns: const [
                            DataColumn(label: Text('رقم الطلب')),
                            DataColumn(label: Text('الخدمة')),
                            DataColumn(label: Text('الملفات')),
                            DataColumn(label: Text('حالة الدفع')),
                            DataColumn(label: Text('حالة الطلب')),
                            DataColumn(label: Text('الإجمالي')),
                            DataColumn(label: Text('التاريخ')),
                            DataColumn(label: Text('الإجراء')),
                          ],
                          rows: _orders.map((order) {
                            final id = _toInt(order['id']);
                            final isPaid = order['payment_status'] == 'paid';
                            return DataRow(
                              cells: [
                                DataCell(Text('#$id')),
                                DataCell(
                                  Text(_serviceLabel(order['service_type'])),
                                ),
                                DataCell(Text('${order['files_count'] ?? 0}')),
                                DataCell(
                                  _Badge(
                                    text: isPaid ? 'مدفوع' : 'غير مدفوع',
                                    color: isPaid
                                        ? const Color(0xFFDCFCE7)
                                        : const Color(0xFFFEF3C7),
                                    textColor: isPaid
                                        ? const Color(0xFF166534)
                                        : const Color(0xFF92400E),
                                  ),
                                ),
                                DataCell(
                                  _Badge(
                                    text: order['status']?.toString() ?? '-',
                                    color: const Color(0xFFF1F5F9),
                                    textColor: const Color(0xFF334155),
                                  ),
                                ),
                                DataCell(
                                  Text('${order['grand_total'] ?? 0} ريال'),
                                ),
                                DataCell(Text(_dateOnly(order['created_at']))),
                                DataCell(
                                  TextButton(
                                    onPressed: () {
                                      Navigator.of(context).push(
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              CartScreen(orderId: id),
                                        ),
                                      );
                                    },
                                    child: Text(
                                      isPaid ? 'عرض السلة' : 'إكمال الدفع',
                                    ),
                                  ),
                                ),
                              ],
                            );
                          }).toList(),
                        ),
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

  static int _toInt(Object? value) {
    if (value is int) return value;
    if (value is num) return value.round();
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static String _dateOnly(Object? value) {
    final text = value?.toString() ?? '';
    return text.length >= 10 ? text.substring(0, 10) : text;
  }

  static String _serviceLabel(Object? service) {
    return switch (service?.toString()) {
      'notes' => 'مذكرات',
      'thesis' => 'ماجستير',
      'phd' => 'دكتوراه',
      _ => service?.toString() ?? '-',
    };
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: MyOrdersScreen._border),
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

class _Badge extends StatelessWidget {
  const _Badge({
    required this.text,
    required this.color,
    required this.textColor,
  });

  final String text;
  final Color color;
  final Color textColor;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(text),
      backgroundColor: color,
      labelStyle: TextStyle(color: textColor, fontWeight: FontWeight.w900),
    );
  }
}
