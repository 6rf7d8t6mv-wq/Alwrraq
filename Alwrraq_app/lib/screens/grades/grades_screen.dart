import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';

import '../../services/api_client.dart';
import '../account/account_settings_screen.dart';
import '../auth/login_screen.dart';
import '../cart/cart_screen.dart';
import '../orders/my_orders_screen.dart';
import '../../widgets/support_chat_widget.dart';

class GradesScreen extends StatefulWidget {
  const GradesScreen({super.key});

  @override
  State<GradesScreen> createState() => _GradesScreenState();
}

class _GradesScreenState extends State<GradesScreen> {
  String? _selectedService;
  final Map<String, Map<String, List<_UploadedFile>>> _files = {
    'notes': {'word': <_UploadedFile>[], 'pdf': <_UploadedFile>[]},
    'thesis': {'word': <_UploadedFile>[], 'pdf': <_UploadedFile>[]},
    'phd': {'word': <_UploadedFile>[], 'pdf': <_UploadedFile>[]},
  };
  final Map<String, int> _orderIds = {};

  static const _background = Color(0xFFF3F4F6);
  static const _dark = Color(0xFF0F172A);
  static const _text = Color(0xFF111827);
  static const _muted = Color(0xFF64748B);
  static const _border = Color(0xFFE5E7EB);
  static const _success = Color(0xFF047857);

  Future<void> _pickAndUpload({
    required String service,
    required String fileType,
  }) async {
    final isPdf = fileType == 'pdf';
    final result = await FilePicker.pickFiles(
      type: FileType.custom,
      allowedExtensions: isPdf ? ['pdf'] : ['doc', 'docx'],
      allowMultiple: true,
      withData: true,
    );

    if (!mounted || result == null || result.files.isEmpty) {
      return;
    }

    var uploadedCount = 0;
    String? lastError;

    for (final file in result.files) {
      final bytes = file.bytes;
      if (bytes == null) {
        lastError = 'تعذر قراءة الملف: ${file.name}';
        continue;
      }

      try {
        final response = await ApiClient.uploadFile(
          bytes: bytes,
          filename: file.name,
          type: fileType,
          service: service,
        );

        if (response['success'] == true) {
          final uploadedFile = _UploadedFile.fromUploadResponse(
            response,
            fileType: fileType,
            service: service,
          );
          if (service != 'notes') {
            final priceResponse = await ApiClient.updateFile(
              fileId: uploadedFile.id,
              copies: uploadedFile.copies,
            );
            if (priceResponse['success'] == true) {
              uploadedFile.applyUpdate(
                priceResponse,
                copies: uploadedFile.copies,
              );
            }
          }
          setState(() {
            _orderIds[service] = uploadedFile.orderId;
            _files[service]![fileType]!.add(uploadedFile);
          });
          uploadedCount++;
        } else {
          lastError = response['message']?.toString() ?? 'فشل رفع الملف';
        }
      } catch (error) {
        lastError =
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000';
      }
    }

    if (!mounted) return;

    final message = uploadedCount > 0
        ? 'تم رفع $uploadedCount ملف بنجاح'
        : lastError ?? 'لم يتم رفع أي ملف';

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: _background,
        body: Stack(
          children: [
            Column(
              children: [
                _Header(onNavigate: _openPage),
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(20, 32, 20, 0),
                    child: Column(
                      children: [
                        Center(
                          child: ConstrainedBox(
                            constraints: const BoxConstraints(maxWidth: 1000),
                            child: _selectedService == null
                                ? _ServicesPanel(onSelect: _selectService)
                                : _UploadPanel(
                                    service: _selectedService!,
                                    onBack: () =>
                                        setState(() => _selectedService = null),
                                    onUpload: _pickAndUpload,
                                    wordFiles:
                                        _files[_selectedService!]!['word']!,
                                    pdfFiles:
                                        _files[_selectedService!]!['pdf']!,
                                    orderId: _orderIds[_selectedService!],
                                    onBindingChanged: _updateBinding,
                                    onCopiesChanged: _updateCopies,
                                    onDelete: _deleteFile,
                                  ),
                          ),
                        ),
                        const SizedBox(height: 28),
                        const _PageFooter(),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SupportChatWidget(),
          ],
        ),
      ),
    );
  }

  void _selectService(String service) {
    setState(() => _selectedService = service);
  }

  void _openPage(Widget page) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => page));
  }

  Future<void> _updateBinding(_UploadedFile file, String binding) async {
    final response = await ApiClient.updateFile(
      fileId: file.id,
      bindingType: binding,
    );

    if (!mounted) return;

    if (response['success'] == true) {
      setState(() => file.applyUpdate(response, bindingType: binding));
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(response['message']?.toString() ?? 'فشل التحديث')),
    );
  }

  Future<void> _updateCopies(_UploadedFile file, int copies) async {
    final response = await ApiClient.updateFile(
      fileId: file.id,
      copies: copies,
    );

    if (!mounted) return;

    if (response['success'] == true) {
      setState(() => file.applyUpdate(response, copies: copies));
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(response['message']?.toString() ?? 'فشل التحديث')),
    );
  }

  Future<void> _deleteFile({
    required String service,
    required String fileType,
    required _UploadedFile file,
  }) async {
    final response = await ApiClient.deleteFile(fileId: file.id);

    if (!mounted) return;

    if (response['success'] == true) {
      setState(() => _files[service]![fileType]!.remove(file));
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(response['message']?.toString() ?? 'فشل الحذف')),
    );
  }
}

class _UploadedFile {
  _UploadedFile({
    required this.id,
    required this.orderId,
    required this.filename,
    required this.fileType,
    required this.size,
    required this.pages,
    required this.printPrice,
    required this.bindingPrice,
    required this.totalPrice,
  });

  final int id;
  final int orderId;
  final String filename;
  final String fileType;
  final int size;
  int pages;
  int copies = 1;
  String? bindingType;
  String? thesisProjectType;
  int printPrice;
  int bindingPrice;
  int totalPrice;

  factory _UploadedFile.fromUploadResponse(
    Map<String, dynamic> data, {
    required String fileType,
    required String service,
  }) {
    final pages = _toInt(data['pages'], fallback: 1);
    final printPrice = _printPrice(pages, 1);
    final bindingPrice = service == 'phd'
        ? 90
        : service == 'thesis'
        ? 70
        : 0;

    return _UploadedFile(
      id: _toInt(data['file_id']),
      orderId: _toInt(data['order_id']),
      filename: data['filename']?.toString() ?? 'file',
      fileType: fileType,
      size: _toInt(data['size']),
      pages: pages,
      printPrice: printPrice,
      bindingPrice: bindingPrice,
      totalPrice: printPrice + bindingPrice,
    );
  }

  void applyUpdate(
    Map<String, dynamic> data, {
    String? bindingType,
    int? copies,
  }) {
    this.bindingType = bindingType ?? this.bindingType;
    this.copies = copies ?? this.copies;
    printPrice = _toInt(data['print_price'], fallback: printPrice);
    bindingPrice = _toInt(data['binding_price'], fallback: bindingPrice);
    totalPrice = _toInt(data['total_price'], fallback: totalPrice);
  }

  static int _toInt(Object? value, {int fallback = 0}) {
    if (value is int) return value;
    if (value is num) return value.round();
    return int.tryParse(value?.toString() ?? '') ?? fallback;
  }
}

int _printPrice(int pages, int copies) {
  return (pages / 15).ceil() * copies.clamp(1, 999);
}

String _formatSize(int bytes) {
  if (bytes <= 0) return '0 Bytes';
  const units = ['Bytes', 'KB', 'MB', 'GB'];
  var size = bytes.toDouble();
  var index = 0;
  while (size >= 1024 && index < units.length - 1) {
    size /= 1024;
    index++;
  }
  return '${size.toStringAsFixed(size >= 10 ? 0 : 1)} ${units[index]}';
}

class _Header extends StatelessWidget {
  const _Header({required this.onNavigate});

  final void Function(Widget page) onNavigate;

  String get _currentUserName {
    final name = ApiClient.user?['name']?.toString().trim();
    return name == null || name.isEmpty ? 'المستخدم' : name;
  }

  @override
  Widget build(BuildContext context) {
    final topPadding = MediaQuery.paddingOf(context).top;

    return Container(
      color: _GradesScreenState._dark,
      padding: EdgeInsets.fromLTRB(24, topPadding + 20, 24, 20),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 1000),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'الورّاق',
                      style: TextStyle(
                        color: Colors.white,
                        fontFamily: 'Arial',
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'خدمات الطباعة والتجليد',
                      style: TextStyle(
                        color: Color(0xFFCBD5E1),
                        fontFamily: 'Arial',
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 16),
              Flexible(
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  reverse: true,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _HeaderPill(text: _currentUserName),
                      const SizedBox(width: 12),
                      _HeaderButton(
                        text: '🧾 طلباتي',
                        onPressed: () => onNavigate(const MyOrdersScreen()),
                      ),
                      const SizedBox(width: 12),
                      _HeaderButton(
                        text: '⚙️ إعداداتي',
                        onPressed: () =>
                            onNavigate(const AccountSettingsScreen()),
                      ),
                      const SizedBox(width: 12),
                      _HeaderButton(
                        text: 'خروج',
                        onPressed: () {
                          ApiClient.logout();
                          Navigator.of(context).pushAndRemoveUntil(
                            MaterialPageRoute(
                              builder: (_) => const LoginScreen(),
                            ),
                            (route) => false,
                          );
                        },
                        outlined: true,
                      ),
                    ],
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

class _ServicesPanel extends StatelessWidget {
  const _ServicesPanel({required this.onSelect});

  final void Function(String service) onSelect;

  @override
  Widget build(BuildContext context) {
    return _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const _BladeSectionTitle('اختر الخدمة المطلوبة'),
          const SizedBox(height: 22),
          _ServiceButton(
            text: 'طباعة المذكرات والكتب وملفات PDF',
            onPressed: () => onSelect('notes'),
          ),
          const SizedBox(height: 20),
          _ServiceButton(
            text: 'طباعة وتجليد رسائل الماجستير',
            onPressed: () => onSelect('thesis'),
          ),
          const SizedBox(height: 20),
          _ServiceButton(
            text: 'طباعة وتجليد رسائل الدكتوراه',
            onPressed: () => onSelect('phd'),
          ),
          const SizedBox(height: 20),
          const _InformationalService(
            title: 'تنسيق رسائل الدكتوراه والماجستير',
            description: 'خدمة تنسيق أكاديمي متاحة في الموقع بنفس بياناتك.',
          ),
          const SizedBox(height: 20),
          const _InformationalService(
            title: 'تدقيق لغوي للرسائل العلمية',
            description: 'مراجعة لغوية احترافية للرسائل والبحوث الأكاديمية.',
          ),
          const SizedBox(height: 20),
          const _InformationalService(
            title: 'إنشاء البحوث',
            description: 'إرسال عنوان البحث وعدد الصفحات ومتابعة التسليم.',
          ),
          const SizedBox(height: 20),
          const _InformationalService(
            title: 'تجليد الكتب كعب جلد طبيعي',
            description: 'تجليد كتب وملازم بكعب جلد طبيعي كما في الموقع.',
          ),
          const SizedBox(height: 20),
          const _InformationalService(
            title: 'طباعة الملفات بالألوان',
            description: 'طباعة ملونة وخيارات تغليف حسب نوع الملف.',
          ),
        ],
      ),
    );
  }
}

class _InformationalService extends StatelessWidget {
  const _InformationalService({required this.title, required this.description});

  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        border: Border.all(color: _GradesScreenState._border),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _GradesScreenState._text,
              fontFamily: 'Arial',
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            description,
            style: const TextStyle(
              color: _GradesScreenState._muted,
              fontFamily: 'Arial',
              fontSize: 13,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }
}

class _BladeSectionTitle extends StatelessWidget {
  const _BladeSectionTitle(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(bottom: 12),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 2)),
      ),
      child: Text(
        text,
        textAlign: TextAlign.start,
        style: const TextStyle(
          color: Color(0xFF1F2937),
          fontFamily: 'Arial',
          fontSize: 24,
          fontWeight: FontWeight.w900,
          height: 1.25,
        ),
      ),
    );
  }
}

class _PageFooter extends StatelessWidget {
  const _PageFooter();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: _GradesScreenState._dark,
      padding: EdgeInsets.fromLTRB(
        24,
        22,
        24,
        MediaQuery.paddingOf(context).bottom + 22,
      ),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 1000),
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'منصة متخصصة في خدمات الطباعة والتجليد للمذكرات والأبحاث والرسائل العلمية.',
                style: TextStyle(
                  color: Color(0xFFCBD5E1),
                  fontFamily: 'Arial',
                  fontSize: 14,
                  height: 1.6,
                ),
              ),
              SizedBox(height: 8),
              Text(
                '© 2026 خدمات الطباعة والتجليد. جميع الحقوق محفوظة.',
                style: TextStyle(
                  color: Color(0xFFCBD5E1),
                  fontFamily: 'Arial',
                  fontSize: 14,
                  height: 1.6,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _UploadPanel extends StatelessWidget {
  const _UploadPanel({
    required this.service,
    required this.onBack,
    required this.onUpload,
    required this.wordFiles,
    required this.pdfFiles,
    required this.orderId,
    required this.onBindingChanged,
    required this.onCopiesChanged,
    required this.onDelete,
  });

  final String service;
  final VoidCallback onBack;
  final Future<void> Function({
    required String service,
    required String fileType,
  })
  onUpload;
  final List<_UploadedFile> wordFiles;
  final List<_UploadedFile> pdfFiles;
  final int? orderId;
  final Future<void> Function(_UploadedFile file, String binding)
  onBindingChanged;
  final Future<void> Function(_UploadedFile file, int copies) onCopiesChanged;
  final Future<void> Function({
    required String service,
    required String fileType,
    required _UploadedFile file,
  })
  onDelete;

  String get title {
    return switch (service) {
      'notes' => 'تحميل ملفات مذكرات',
      'thesis' => 'تحميل ملفات رسالة ماجستير أو بحث',
      'phd' => 'تحميل ملفات رسالة دكتوراه',
      _ => 'تحميل الملفات',
    };
  }

  String get summary {
    final files = [...wordFiles, ...pdfFiles];
    if (files.isEmpty) return 'ارفع الملفات لعرض الإجمالي.';
    if (service == 'notes' && files.any((file) => file.bindingType == null)) {
      return 'اختر نوع التغليف لكل ملف قبل إتمام الطلب.';
    }

    final printTotal = _printPrice(
      files.fold(0, (sum, file) => sum + (file.pages * file.copies)),
      1,
    );
    final bindingTotal = files.fold(0, (sum, file) => sum + file.bindingPrice);
    final total = printTotal + bindingTotal;

    return 'سعر الطباعة: $printTotal ريال | سعر التغليف: $bindingTotal ريال | الإجمالي: $total ريال';
  }

  bool get canCheckout {
    final files = [...wordFiles, ...pdfFiles];
    return orderId != null &&
        files.isNotEmpty &&
        (service != 'notes' || files.every((file) => file.bindingType != null));
  }

  bool get needsBinding {
    final files = [...wordFiles, ...pdfFiles];
    return service == 'notes' &&
        files.isNotEmpty &&
        files.any((file) => file.bindingType == null);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Align(
          alignment: AlignmentDirectional.centerStart,
          child: OutlinedButton(
            onPressed: onBack,
            child: const Text('العودة للخدمات'),
          ),
        ),
        const SizedBox(height: 12),
        _Panel(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  color: _GradesScreenState._text,
                  fontFamily: 'Arial',
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 18),
              Wrap(
                spacing: 14,
                runSpacing: 14,
                children: [
                  _UploadBox(
                    title: 'تحميل ملفات Word',
                    icon: Icons.article_outlined,
                    info: 'صيغ مدعومة: .doc, .docx',
                    onPressed: () =>
                        onUpload(service: service, fileType: 'word'),
                  ),
                  _UploadBox(
                    title: 'تحميل ملفات PDF',
                    icon: Icons.picture_as_pdf_outlined,
                    info: 'صيغ مدعومة: .pdf',
                    onPressed: () =>
                        onUpload(service: service, fileType: 'pdf'),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        _FilesSection(
          title: 'ملفات Word المحملة',
          service: service,
          fileType: 'word',
          files: wordFiles,
          allFiles: [...wordFiles, ...pdfFiles],
          showBinding: service == 'notes',
          onBindingChanged: onBindingChanged,
          onCopiesChanged: onCopiesChanged,
          onDelete: onDelete,
        ),
        const SizedBox(height: 18),
        _FilesSection(
          title: 'ملفات PDF المحملة',
          service: service,
          fileType: 'pdf',
          files: pdfFiles,
          allFiles: [...wordFiles, ...pdfFiles],
          showBinding: service == 'notes',
          onBindingChanged: onBindingChanged,
          onCopiesChanged: onCopiesChanged,
          onDelete: onDelete,
        ),
        const SizedBox(height: 18),
        _SummaryPanel(
          message: summary,
          needsBinding: needsBinding,
          canCheckout: canCheckout,
          onCheckout: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => CartScreen(orderId: orderId!)),
            );
          },
        ),
      ],
    );
  }
}

class _SummaryPanel extends StatelessWidget {
  const _SummaryPanel({
    required this.message,
    required this.needsBinding,
    required this.canCheckout,
    required this.onCheckout,
  });

  final String message;
  final bool needsBinding;
  final bool canCheckout;
  final VoidCallback onCheckout;

  @override
  Widget build(BuildContext context) {
    final accent = needsBinding
        ? const Color(0xFFB91C1C)
        : _GradesScreenState._success;
    final background = needsBinding
        ? const Color(0xFFFEF2F2)
        : const Color(0xFFF0FDF4);

    return _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'الإجمالي',
            style: TextStyle(
              color: _GradesScreenState._text,
              fontFamily: 'Arial',
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: background,
              border: Border.all(color: accent.withValues(alpha: 0.24)),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: [
                Icon(
                  needsBinding
                      ? Icons.error_outline
                      : Icons.check_circle_outline,
                  color: accent,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    message,
                    style: TextStyle(
                      color: accent,
                      fontFamily: 'Arial',
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      height: 1.6,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Align(
            alignment: AlignmentDirectional.centerEnd,
            child: FilledButton(
              onPressed: canCheckout ? onCheckout : null,
              style: FilledButton.styleFrom(
                backgroundColor: _GradesScreenState._success,
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 14,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(9),
                ),
              ),
              child: Text(needsBinding ? 'اختر التغليف أولًا' : 'إتمام الطلب'),
            ),
          ),
        ],
      ),
    );
  }
}

class _FilesSection extends StatelessWidget {
  const _FilesSection({
    required this.title,
    required this.service,
    required this.fileType,
    required this.files,
    required this.allFiles,
    required this.showBinding,
    required this.onBindingChanged,
    required this.onCopiesChanged,
    required this.onDelete,
  });

  final String title;
  final String service;
  final String fileType;
  final List<_UploadedFile> files;
  final List<_UploadedFile> allFiles;
  final bool showBinding;
  final Future<void> Function(_UploadedFile file, String binding)
  onBindingChanged;
  final Future<void> Function(_UploadedFile file, int copies) onCopiesChanged;
  final Future<void> Function({
    required String service,
    required String fileType,
    required _UploadedFile file,
  })
  onDelete;

  @override
  Widget build(BuildContext context) {
    final showThesisProject = service == 'thesis' && fileType == 'pdf';
    final headers = [
      'اسم الملف',
      'الصفحات',
      'الحجم',
      showBinding ? 'نوع التغليف' : 'النسخ',
      if (showThesisProject) 'مشروع الرسالة',
      'سعر الطباعة',
      'سعر التغليف',
      'الإجمالي',
      'الحالة',
      '',
    ];

    return _Panel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _GradesScreenState._text,
              fontFamily: 'Arial',
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 14),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Container(
              decoration: BoxDecoration(
                border: Border.all(color: const Color(0xFFE2E8F0)),
                borderRadius: BorderRadius.circular(10),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: DataTable(
                  headingRowColor: WidgetStateProperty.all(
                    const Color(0xFFF8FAFC),
                  ),
                  dataRowMinHeight: 58,
                  dataRowMaxHeight: 72,
                  columnSpacing: 22,
                  columns: headers
                      .map(
                        (h) => DataColumn(
                          label: Text(
                            h,
                            style: const TextStyle(
                              color: Color(0xFF334155),
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                      )
                      .toList(),
                  rows: files.map((file) {
                    final waitingForBinding =
                        showBinding && file.bindingType == null;
                    final displayPrintPrice = _allocatedPrintPrice(file);
                    final displayTotalPrice =
                        displayPrintPrice + file.bindingPrice;
                    final cells = [
                      DataCell(Text(file.filename)),
                      DataCell(Text('${file.pages} صفحة')),
                      DataCell(Text(_formatSize(file.size))),
                      DataCell(
                        showBinding
                            ? _BindingSelect(
                                value: file.bindingType,
                                onChanged: (value) {
                                  if (value != null) {
                                    onBindingChanged(file, value);
                                  }
                                },
                              )
                            : _CopiesSelect(
                                value: file.copies,
                                onChanged: (value) {
                                  if (value != null) {
                                    onCopiesChanged(file, value);
                                  }
                                },
                              ),
                      ),
                      if (showThesisProject)
                        DataCell(_ThesisProjectSelect(file: file)),
                      DataCell(Text('$displayPrintPrice ريال')),
                      DataCell(
                        Text(
                          waitingForBinding ? '-' : '${file.bindingPrice} ريال',
                        ),
                      ),
                      DataCell(
                        Text(
                          waitingForBinding
                              ? 'اختر التغليف'
                              : '$displayTotalPrice ريال',
                          style: TextStyle(
                            color: waitingForBinding
                                ? const Color(0xFFB91C1C)
                                : _GradesScreenState._text,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      const DataCell(
                        Text(
                          'مرفوع',
                          style: TextStyle(
                            color: _GradesScreenState._success,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      DataCell(
                        TextButton.icon(
                          onPressed: () => onDelete(
                            service: service,
                            fileType: fileType,
                            file: file,
                          ),
                          icon: const Icon(Icons.delete_outline, size: 18),
                          label: const Text('حذف'),
                        ),
                      ),
                    ];

                    return DataRow(cells: cells);
                  }).toList(),
                ),
              ),
            ),
          ),
          if (files.isEmpty) ...const [
            SizedBox(height: 8),
            Text(
              'لم يتم تحميل أي ملفات',
              style: TextStyle(
                color: Color(0xFF94A3B8),
                fontFamily: 'Arial',
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ],
      ),
    );
  }

  int _allocatedPrintPrice(_UploadedFile file) {
    final index = allFiles.indexOf(file);
    if (index < 0) return file.printPrice;

    final previousPages = allFiles
        .take(index)
        .fold(0, (sum, item) => sum + (item.pages * item.copies));
    final currentPages = file.pages * file.copies;

    return _printPrice(previousPages + currentPages, 1) -
        _printPrice(previousPages, 1);
  }
}

class _BindingSelect extends StatelessWidget {
  const _BindingSelect({required this.value, required this.onChanged});

  final String? value;
  final ValueChanged<String?> onChanged;

  @override
  Widget build(BuildContext context) {
    return DropdownButton<String>(
      value: value,
      hint: const Text('اختر التغليف *'),
      underline: const SizedBox.shrink(),
      borderRadius: BorderRadius.circular(10),
      items: const [
        DropdownMenuItem(value: 'tape', child: Text('تغليف دبوس')),
        DropdownMenuItem(value: 'wire', child: Text('تغليف سلك')),
        DropdownMenuItem(value: 'normal', child: Text('تغليف عادي')),
        DropdownMenuItem(value: 'none', child: Text('بدون تغليف')),
      ],
      onChanged: onChanged,
    );
  }
}

class _CopiesSelect extends StatelessWidget {
  const _CopiesSelect({required this.value, required this.onChanged});

  final int value;
  final ValueChanged<int?> onChanged;

  @override
  Widget build(BuildContext context) {
    return DropdownButton<int>(
      value: value.clamp(1, 10),
      underline: const SizedBox.shrink(),
      borderRadius: BorderRadius.circular(10),
      items: List.generate(10, (index) {
        final copies = index + 1;
        return DropdownMenuItem(value: copies, child: Text('$copies'));
      }),
      onChanged: onChanged,
    );
  }
}

class _ThesisProjectSelect extends StatefulWidget {
  const _ThesisProjectSelect({required this.file});

  final _UploadedFile file;

  @override
  State<_ThesisProjectSelect> createState() => _ThesisProjectSelectState();
}

class _ThesisProjectSelectState extends State<_ThesisProjectSelect> {
  static const _options = [
    DropdownMenuItem(value: 'thesis', child: Text('رسالة ماجستير')),
    DropdownMenuItem(value: 'supplementary', child: Text('بحث تكميلي')),
    DropdownMenuItem(value: 'graduation', child: Text('بحث تخرج')),
  ];

  @override
  Widget build(BuildContext context) {
    return DropdownButton<String>(
      value: widget.file.thesisProjectType,
      hint: const Text('اختر المشروع'),
      underline: const SizedBox.shrink(),
      borderRadius: BorderRadius.circular(10),
      items: _options,
      onChanged: (value) {
        setState(() => widget.file.thesisProjectType = value);
      },
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: _GradesScreenState._border),
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(
            color: Color(0x140F172A),
            blurRadius: 60,
            offset: Offset(0, 24),
          ),
        ],
      ),
      child: child,
    );
  }
}

class _ServiceButton extends StatelessWidget {
  const _ServiceButton({required this.text, required this.onPressed});

  final String text;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
            color: Color(0x260F172A),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            width: double.infinity,
            constraints: const BoxConstraints(minHeight: 74),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
            alignment: Alignment.center,
            child: Text(
              text,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontFamily: 'Arial',
                fontSize: 18,
                fontWeight: FontWeight.w900,
                height: 1.35,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _UploadBox extends StatelessWidget {
  const _UploadBox({
    required this.title,
    required this.icon,
    required this.info,
    required this.onPressed,
  });

  final String title;
  final IconData icon;
  final String info;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 300,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFC),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Icon(icon, size: 44, color: _GradesScreenState._dark),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(
                color: _GradesScreenState._text,
                fontFamily: 'Arial',
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              info,
              style: const TextStyle(color: _GradesScreenState._muted),
            ),
            const Text(
              'حجم الملف: بدون حد أقصى',
              style: TextStyle(color: _GradesScreenState._muted),
            ),
            const SizedBox(height: 12),
            OutlinedButton(
              onPressed: onPressed,
              child: const Text('اختر ملفات'),
            ),
          ],
        ),
      ),
    );
  }
}

class _HeaderPill extends StatelessWidget {
  const _HeaderPill({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 40,
      padding: const EdgeInsets.symmetric(horizontal: 14),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: const Color(0xFF334155)),
      ),
      alignment: Alignment.center,
      child: Text(
        text,
        style: const TextStyle(
          color: Colors.white,
          fontFamily: 'Arial',
          fontSize: 14,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _HeaderButton extends StatelessWidget {
  const _HeaderButton({
    required this.text,
    required this.onPressed,
    this.outlined = false,
  });

  final String text;
  final VoidCallback onPressed;
  final bool outlined;

  @override
  Widget build(BuildContext context) {
    return outlined
        ? OutlinedButton(onPressed: onPressed, child: Text(text))
        : FilledButton.tonal(onPressed: onPressed, child: Text(text));
  }
}
