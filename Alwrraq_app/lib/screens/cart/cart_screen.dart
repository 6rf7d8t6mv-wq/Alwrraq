import 'package:flutter/material.dart';

import '../../services/api_client.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key, this.orderId});

  final int? orderId;

  static const _background = Color(0xFFF3F4F6);
  static const _dark = Color(0xFF0F172A);
  static const _text = Color(0xFF111827);
  static const _muted = Color(0xFF64748B);
  static const _border = Color(0xFFE5E7EB);

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  Map<String, dynamic>? _order;
  String? _error;
  var _isLoading = false;
  var _isPaying = false;

  @override
  void initState() {
    super.initState();
    _loadCart();
  }

  Future<void> _loadCart() async {
    if (widget.orderId == null) {
      setState(() => _error = 'لا يوجد طلب مفتوح حاليًا.');
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    final response = await ApiClient.cart(orderId: widget.orderId!);

    if (!mounted) return;

    setState(() {
      _isLoading = false;
      if (response['success'] == true &&
          response['order'] is Map<String, dynamic>) {
        _order = response['order'] as Map<String, dynamic>;
      } else {
        _error = response['message']?.toString() ?? 'تعذر تحميل السلة';
      }
    });
  }

  Future<void> _pay({
    required String paymentMethod,
    String? cardName,
    String? cardNumber,
    String? cardExpiry,
    String? cardCvc,
  }) async {
    final orderId = widget.orderId;
    if (orderId == null || _isPaying) return;

    setState(() => _isPaying = true);
    final response = await ApiClient.pay(
      orderId: orderId,
      paymentMethod: paymentMethod,
      cardName: cardName,
      cardNumber: cardNumber,
      cardExpiry: cardExpiry,
      cardCvc: cardCvc,
    );

    if (!mounted) return;
    setState(() => _isPaying = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(response['message']?.toString() ?? 'تم تنفيذ العملية'),
      ),
    );

    if (response['success'] == true) {
      await _loadCart();
    }
  }

  @override
  Widget build(BuildContext context) {
    return _LaravelPage(
      title: 'السلة والدفع',
      subtitle: 'راجع الطلب قبل الدفع. لا يوجد دفع عند الاستلام أو كاش.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: _isLoading
            ? const [
                _Panel(
                  title: 'تحميل',
                  child: Center(child: CircularProgressIndicator()),
                ),
              ]
            : _error != null
            ? [
                _Panel(
                  title: 'تعذر تحميل السلة',
                  subtitle: _error,
                  child: const SizedBox.shrink(),
                ),
              ]
            : [
                _MetaGrid(order: _order!),
                const SizedBox(height: 18),
                _FilesPanel(files: _files),
                const SizedBox(height: 18),
                _TotalsPanel(order: _order!),
                const SizedBox(height: 18),
                _PaymentPanel(
                  isPaid: _order!['payment_status'] == 'paid',
                  isPaying: _isPaying,
                  onPay: _pay,
                ),
              ],
      ),
    );
  }

  List<Map<String, dynamic>> get _files {
    final files = _order?['files'];
    if (files is List) {
      return files.whereType<Map<String, dynamic>>().toList();
    }
    return [];
  }
}

class _MetaGrid extends StatelessWidget {
  const _MetaGrid({required this.order});

  final Map<String, dynamic> order;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: [
        _InfoCard(label: 'رقم الطلب', value: '#${order['id']}'),
        _InfoCard(label: 'الخدمة', value: _serviceLabel(order['service_type'])),
        _InfoCard(
          label: 'حالة الطلب',
          value: order['status']?.toString() ?? '-',
        ),
        _InfoCard(
          label: 'الدفع',
          value: order['payment_status'] == 'paid' ? 'مدفوع' : 'غير مدفوع',
        ),
      ],
    );
  }

  String _serviceLabel(Object? service) {
    return switch (service?.toString()) {
      'notes' => 'مذكرات',
      'thesis' => 'ماجستير',
      'phd' => 'دكتوراه',
      _ => service?.toString() ?? '-',
    };
  }
}

class _FilesPanel extends StatelessWidget {
  const _FilesPanel({required this.files});

  final List<Map<String, dynamic>> files;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      title: 'الملفات',
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: DataTable(
          columns: [
            DataColumn(label: Text('الملف')),
            DataColumn(label: Text('النوع')),
            DataColumn(label: Text('الصفحات')),
            DataColumn(label: Text('النسخ')),
            DataColumn(label: Text('التغليف')),
            DataColumn(label: Text('الطباعة')),
            DataColumn(label: Text('التغليف')),
            DataColumn(label: Text('الإجمالي')),
          ],
          rows: files.map((file) {
            return DataRow(
              cells: [
                DataCell(Text(file['original_name']?.toString() ?? '-')),
                DataCell(
                  Text((file['file_type']?.toString() ?? '-').toUpperCase()),
                ),
                DataCell(Text(file['pages']?.toString() ?? '-')),
                DataCell(Text(file['copies']?.toString() ?? '1')),
                DataCell(Text(_bindingLabel(file['binding_type']))),
                DataCell(Text('${file['print_price'] ?? 0} ريال')),
                DataCell(Text('${file['binding_price'] ?? 0} ريال')),
                DataCell(Text('${file['total_price'] ?? 0} ريال')),
              ],
            );
          }).toList(),
        ),
      ),
    );
  }

  String _bindingLabel(Object? binding) {
    return switch (binding?.toString()) {
      'tape' => 'تغليف دبوس',
      'wire' => 'تغليف سلك',
      'normal' => 'تغليف عادي',
      'none' => 'بدون تغليف',
      _ => '-',
    };
  }
}

class _TotalsPanel extends StatelessWidget {
  const _TotalsPanel({required this.order});

  final Map<String, dynamic> order;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      title: 'الإجمالي',
      child: Wrap(
        spacing: 12,
        runSpacing: 12,
        children: [
          _InfoCard(
            label: 'سعر الطباعة',
            value: '${order['print_total'] ?? 0} ريال',
          ),
          _InfoCard(
            label: 'سعر التغليف',
            value: '${order['binding_total'] ?? 0} ريال',
          ),
          _InfoCard(
            label: 'الإجمالي',
            value: '${order['grand_total'] ?? 0} ريال',
          ),
        ],
      ),
    );
  }
}

class _PaymentPanel extends StatelessWidget {
  const _PaymentPanel({
    required this.isPaid,
    required this.isPaying,
    required this.onPay,
  });

  final bool isPaid;
  final bool isPaying;
  final Future<void> Function({
    required String paymentMethod,
    String? cardName,
    String? cardNumber,
    String? cardExpiry,
    String? cardCvc,
  })
  onPay;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      title: 'الدفع',
      child: isPaid
          ? const _PaidNotice()
          : Wrap(
              spacing: 14,
              runSpacing: 14,
              children: [
                _ApplePayCard(
                  isPaying: isPaying,
                  onPay: () => onPay(paymentMethod: 'apple_pay'),
                ),
                _CardPayCard(isPaying: isPaying, onPay: onPay),
              ],
            ),
    );
  }
}

class _PaidNotice extends StatelessWidget {
  const _PaidNotice();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFECFDF5),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFA7F3D0)),
      ),
      child: const Text(
        'تم الدفع بنجاح.',
        style: TextStyle(color: Color(0xFF047857), fontWeight: FontWeight.w900),
      ),
    );
  }
}

class _ApplePayCard extends StatelessWidget {
  const _ApplePayCard({required this.isPaying, required this.onPay});

  final bool isPaying;
  final VoidCallback onPay;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 320,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFC),
          border: Border.all(color: CartScreen._border),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Apple Pay',
              style: const TextStyle(
                color: CartScreen._text,
                fontFamily: 'Arial',
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'اعتماد الطلب والدفع عبر Apple Pay.',
              style: TextStyle(color: CartScreen._muted),
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: isPaying ? null : onPay,
              style: FilledButton.styleFrom(backgroundColor: Colors.black),
              child: Text(isPaying ? 'جاري الدفع...' : 'Apple Pay'),
            ),
          ],
        ),
      ),
    );
  }
}

class _CardPayCard extends StatefulWidget {
  const _CardPayCard({required this.isPaying, required this.onPay});

  final bool isPaying;
  final Future<void> Function({
    required String paymentMethod,
    String? cardName,
    String? cardNumber,
    String? cardExpiry,
    String? cardCvc,
  })
  onPay;

  @override
  State<_CardPayCard> createState() => _CardPayCardState();
}

class _CardPayCardState extends State<_CardPayCard> {
  final _cardName = TextEditingController();
  final _cardNumber = TextEditingController();
  final _cardExpiry = TextEditingController();
  final _cardCvc = TextEditingController();

  @override
  void dispose() {
    _cardName.dispose();
    _cardNumber.dispose();
    _cardExpiry.dispose();
    _cardCvc.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 320,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFC),
          border: Border.all(color: CartScreen._border),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'بطاقة بنكية',
              style: TextStyle(
                color: CartScreen._text,
                fontFamily: 'Arial',
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 12),
            _PaymentField(label: 'اسم حامل البطاقة', controller: _cardName),
            _PaymentField(
              label: 'رقم البطاقة',
              controller: _cardNumber,
              keyboardType: TextInputType.number,
            ),
            _PaymentField(
              label: 'تاريخ الانتهاء MM/YY',
              controller: _cardExpiry,
            ),
            _PaymentField(
              label: 'CVV',
              controller: _cardCvc,
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: widget.isPaying
                  ? null
                  : () => widget.onPay(
                      paymentMethod: 'card',
                      cardName: _cardName.text.trim(),
                      cardNumber: _cardNumber.text.trim(),
                      cardExpiry: _cardExpiry.text.trim(),
                      cardCvc: _cardCvc.text.trim(),
                    ),
              child: Text(
                widget.isPaying ? 'جاري الدفع...' : 'دفع واعتماد الطلب',
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaymentField extends StatelessWidget {
  const _PaymentField({
    required this.label,
    required this.controller,
    this.keyboardType,
  });

  final String label;
  final TextEditingController controller;
  final TextInputType? keyboardType;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        decoration: InputDecoration(
          labelText: label,
          border: const OutlineInputBorder(),
        ),
      ),
    );
  }
}

class _LaravelPage extends StatelessWidget {
  const _LaravelPage({
    required this.title,
    required this.subtitle,
    required this.child,
  });

  final String title;
  final String subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: CartScreen._background,
        appBar: AppBar(
          backgroundColor: CartScreen._dark,
          foregroundColor: Colors.white,
          title: const Text('الورّاق'),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1040),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _Panel(
                    title: title,
                    subtitle: subtitle,
                    child: const SizedBox.shrink(),
                  ),
                  const SizedBox(height: 18),
                  child,
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.title, required this.child, this.subtitle});

  final String title;
  final String? subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: CartScreen._border),
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
            color: Color(0x140F172A),
            blurRadius: 45,
            offset: Offset(0, 18),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: CartScreen._text,
              fontFamily: 'Arial',
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 8),
            Text(subtitle!, style: const TextStyle(color: CartScreen._muted)),
          ],
          if (child is! SizedBox) ...[const SizedBox(height: 14), child],
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 210,
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
            Text(label, style: const TextStyle(color: CartScreen._muted)),
            const SizedBox(height: 6),
            Text(
              value,
              style: const TextStyle(
                color: CartScreen._text,
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
