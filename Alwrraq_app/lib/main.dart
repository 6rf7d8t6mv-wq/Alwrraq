import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';

import 'android_file_selector.dart';
import 'shared_import.dart';

void main() {
  runApp(const AlwrraqApp());
}

class AlwrraqApp extends StatelessWidget {
  const AlwrraqApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Alwrraq',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0F4C81)),
        useMaterial3: true,
      ),
      home: const AlwrraqWebApp(),
    );
  }
}

class AlwrraqWebApp extends StatefulWidget {
  const AlwrraqWebApp({super.key});

  @override
  State<AlwrraqWebApp> createState() => _AlwrraqWebAppState();
}

class _AlwrraqWebAppState extends State<AlwrraqWebApp> {
  static const String _configuredSiteUrl = String.fromEnvironment(
    'ALWRRAQ_SITE_URL',
    defaultValue: 'https://alwrraq.com',
  );
  static final Uri _siteUri = Uri.parse(
    '${_configuredSiteUrl.replaceFirst(RegExp(r'/$'), '')}/app',
  );
  static const MethodChannel _downloadsChannel = MethodChannel(
    'alwrraq/downloads',
  );
  static const MethodChannel _securityChannel = MethodChannel(
    'alwrraq/security',
  );
  static const MethodChannel _shareChannel = MethodChannel('alwrraq/share');

  late final WebViewController _controller;
  var _isEnglish = false;
  var _isImportingSharedFiles = false;
  var _sharedFilesCompleted = 0;
  var _sharedFilesTotal = 0;
  List<SharedImportFile> _pendingSharedFiles = const [];
  String? _errorMessage;
  String? _shareErrorMessage;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFFF3F4F6))
      ..addJavaScriptChannel(
        'AlwrraqLocale',
        onMessageReceived: (message) {
          if (!mounted) return;
          final isEnglish = message.message == 'en';
          if (_isEnglish == isEnglish) return;

          setState(() => _isEnglish = isEnglish);
        },
      )
      ..addJavaScriptChannel(
        'ResumeSecurity',
        onMessageReceived: (message) async {
          final secure = message.message == 'secure';
          try {
            await _securityChannel.invokeMethod<void>('setSecureScreen', {
              'secure': secure,
            });
          } on PlatformException {
            // Web and unsupported platforms still keep the protected watermark.
          }
        },
      )
      ..addJavaScriptChannel(
        'AlwrraqShareService',
        onMessageReceived: (message) {
          unawaited(_handleSharedServiceSelection(message.message));
        },
      )
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) async {
            final uri = Uri.tryParse(request.url);
            final isDeliveredFileDownload =
                uri != null &&
                uri.path.contains('/delivered-files/') &&
                !uri.path.endsWith('/view') &&
                !uri.path.endsWith('/raw');
            if (!const {
                  TargetPlatform.android,
                  TargetPlatform.iOS,
                }.contains(defaultTargetPlatform) ||
                (uri?.queryParameters['download'] != '1' &&
                    !isDeliveredFileDownload)) {
              return NavigationDecision.navigate;
            }

            final fileName = uri?.queryParameters['filename'] ?? 'alwrraq-file';

            try {
              await _downloadsChannel.invokeMethod<Object>('download', {
                'url': request.url,
                'fileName': fileName,
              });

              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      _isEnglish
                          ? 'Downloading $fileName has started'
                          : 'بدأ تحميل $fileName',
                    ),
                  ),
                );
              }
            } on PlatformException {
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      _isEnglish
                          ? 'The file download could not be started'
                          : 'تعذر بدء تحميل الملف',
                    ),
                  ),
                );
              }
            }

            return NavigationDecision.prevent;
          },
          onPageStarted: (_) {
            if (!mounted) return;
            setState(() {
              _errorMessage = null;
            });
          },
          onPageFinished: (_) async {
            if (!mounted) return;
            final currentUrl = Uri.tryParse(
              await _controller.currentUrl() ?? '',
            );
            if (_pendingSharedFiles.isNotEmpty &&
                currentUrl?.host == _siteUri.host &&
                const {'/', '/app', '/home'}.contains(currentUrl?.path) &&
                currentUrl?.queryParameters['share_import'] != '1') {
              await _controller.loadRequest(_sharedServicesUri());
              return;
            }
            final pageLanguage = await _controller.runJavaScriptReturningResult(
              'document.documentElement.lang || "ar"',
            );
            if (!mounted) return;
            final isEnglish =
                pageLanguage.toString().replaceAll('"', '') == 'en';
            if (_isEnglish != isEnglish) {
              setState(() => _isEnglish = isEnglish);
            }
          },
          onWebResourceError: (error) {
            if (!mounted || error.isForMainFrame != true) return;
            setState(() {
              _errorMessage = _isEnglish
                  ? 'Alwrraq could not be opened. Check your internet connection and try again.'
                  : 'تعذر فتح تطبيق الورّاق. تحقق من اتصال الإنترنت ثم حاول مرة أخرى.';
            });
          },
        ),
      );
    unawaited(_initializeWebView());
  }

  Future<void> _initializeWebView() async {
    if (defaultTargetPlatform == TargetPlatform.android &&
        _controller.platform is AndroidWebViewController) {
      final androidController =
          _controller.platform as AndroidWebViewController;
      await androidController.setOnShowFileSelector((params) async {
        try {
          return await selectAndroidWebViewFiles(params);
        } catch (_) {
          return const <String>[];
        }
      });
    }

    _shareChannel.setMethodCallHandler((call) async {
      if (call.method == 'sharedFiles') {
        await _acceptSharedFiles(call.arguments);
      }
    });

    try {
      final initialShare = await _shareChannel.invokeMethod<Object?>(
        'getInitialShare',
      );
      if (await _acceptSharedFiles(initialShare)) return;
    } on PlatformException {
      // The normal web experience remains available on unsupported platforms.
    }

    await _loadSite();
  }

  Future<bool> _acceptSharedFiles(Object? payload) async {
    if (payload is! List || payload.isEmpty) return false;

    final files = <SharedImportFile>[];
    var hasUnsupportedFile = false;
    for (final item in payload) {
      try {
        if (item is Map) {
          files.add(SharedImportFile.fromMap(item.cast<Object?, Object?>()));
        }
      } on FormatException {
        hasUnsupportedFile = true;
      }
    }

    if (files.isEmpty || hasUnsupportedFile) {
      if (mounted) {
        setState(() {
          _shareErrorMessage = _isEnglish
              ? 'One or more shared files are not supported.'
              : 'يوجد ملف أو أكثر بصيغة غير مدعومة.';
        });
      }
      return false;
    }

    final allFiles = [..._pendingSharedFiles, ...files];
    final kinds = allFiles.map((file) => file.kind.name).toSet().toList()
      ..sort();
    final shareUri = _siteUri.replace(
      queryParameters: {'share_import': '1', 'share_types': kinds.join(',')},
    );

    if (mounted) {
      setState(() {
        _pendingSharedFiles = allFiles;
        _shareErrorMessage = null;
        _sharedFilesCompleted = 0;
      });
    }
    await _loadSite(shareUri);
    return true;
  }

  Uri _sharedServicesUri() {
    final kinds =
        _pendingSharedFiles.map((file) => file.kind.name).toSet().toList()
          ..sort();
    return _siteUri.replace(
      path: '/home',
      queryParameters: {'share_import': '1', 'share_types': kinds.join(',')},
    );
  }

  Future<void> _handleSharedServiceSelection(String message) async {
    if (_pendingSharedFiles.isEmpty || _isImportingSharedFiles) return;

    Map<String, dynamic> selection;
    try {
      selection = (jsonDecode(message) as Map).cast<String, dynamic>();
    } on Object {
      return;
    }

    final service = selection['service']?.toString() ?? '';
    final definitionId = int.tryParse(
      selection['serviceDefinitionId']?.toString() ?? '',
    );
    final kinds = _pendingSharedFiles.map((file) => file.kind).toSet();
    if (!serviceAcceptsSharedKinds(service, kinds)) return;

    setState(() {
      _isImportingSharedFiles = true;
      _shareErrorMessage = null;
      _sharedFilesCompleted = 0;
      _sharedFilesTotal = _pendingSharedFiles.length;
    });

    try {
      final cookie = await _shareChannel.invokeMethod<String>('getCookies', {
        'url': _siteUri.toString(),
      });
      final csrfToken = await _readJavaScriptString(
        'document.querySelector(\'meta[name="csrf-token"]\')?.content || ""',
      );
      if (cookie == null || cookie.isEmpty || csrfToken.isEmpty) {
        throw const FormatException('Missing web session');
      }

      final failed = <SharedImportFile>[];
      for (final file in _pendingSharedFiles) {
        try {
          final response = await _uploadSharedFile(
            file: file,
            service: service,
            serviceDefinitionId: definitionId,
            cookie: cookie,
            csrfToken: csrfToken,
          );
          await _controller.runJavaScript(
            'window.alwrraqReceiveSharedUpload?.(${jsonEncode({'service': service, 'type': file.uploadType, 'response': response})});',
          );
          await _deleteTemporarySharedFile(file.path);
        } on Object {
          failed.add(file);
        }

        if (mounted) {
          setState(() => _sharedFilesCompleted++);
        }
      }

      if (!mounted) return;
      setState(() {
        _pendingSharedFiles = failed;
        _shareErrorMessage = failed.isEmpty
            ? null
            : (_isEnglish
                  ? 'Some files could not be uploaded. Try again.'
                  : 'تعذر تحميل بعض الملفات. حاول مرة أخرى.');
      });
      if (failed.isEmpty) {
        await _controller.runJavaScript(
          'window.alwrraqFinishSharedImport?.();',
        );
      }
    } on Object {
      if (mounted) {
        setState(() {
          _shareErrorMessage = _isEnglish
              ? 'The shared files could not be imported. Try again.'
              : 'تعذر استيراد الملفات المشتركة. حاول مرة أخرى.';
        });
      }
    } finally {
      if (mounted) setState(() => _isImportingSharedFiles = false);
    }
  }

  Future<Map<String, dynamic>> _uploadSharedFile({
    required SharedImportFile file,
    required String service,
    required int? serviceDefinitionId,
    required String cookie,
    required String csrfToken,
  }) async {
    final request =
        http.MultipartRequest(
            'POST',
            Uri.parse('${_siteUri.origin}/upload-file'),
          )
          ..headers.addAll({
            'Accept': 'application/json',
            'Cookie': cookie,
            'X-CSRF-TOKEN': csrfToken,
          })
          ..fields['service'] = service
          ..fields['type'] = file.uploadType;

    if (serviceDefinitionId != null) {
      request.fields['service_definition_id'] = serviceDefinitionId.toString();
    }
    if (file.kind == SharedFileKind.image) {
      request.fields['relative_path'] = file.name;
    }

    final path = file.path.startsWith('file://')
        ? Uri.parse(file.path).toFilePath()
        : file.path;
    request.files.add(
      await http.MultipartFile.fromPath('file', path, filename: file.name),
    );

    final response = await http.Response.fromStream(
      await request.send().timeout(const Duration(minutes: 5)),
    );
    final payload = (jsonDecode(response.body) as Map).cast<String, dynamic>();
    if (response.statusCode != 200 || payload['success'] != true) {
      throw HttpException(payload['message']?.toString() ?? 'Upload failed');
    }
    return payload;
  }

  Future<String> _readJavaScriptString(String expression) async {
    final value = await _controller.runJavaScriptReturningResult(expression);
    if (value is String) {
      try {
        final decoded = jsonDecode(value);
        if (decoded is String) return decoded;
      } on FormatException {
        return value;
      }
      return value;
    }
    return value.toString().replaceAll('"', '');
  }

  Future<void> _deleteTemporarySharedFile(String path) async {
    try {
      final file = File(
        path.startsWith('file://') ? Uri.parse(path).toFilePath() : path,
      );
      if (await file.exists()) await file.delete();
    } on FileSystemException {
      // The OS also clears its temporary sharing directory automatically.
    }
  }

  Future<void> _loadSite([Uri? uri]) async {
    // Keep the WebView cache between launches. Dynamic pages still revalidate
    // normally, while logos, previews, fonts, and scripts open immediately.
    await _controller.loadRequest(uri ?? _siteUri);
  }

  Future<void> _reload() async {
    setState(() {
      _errorMessage = null;
    });
    await _loadSite();
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: _isEnglish ? TextDirection.ltr : TextDirection.rtl,
      child: Scaffold(
        backgroundColor: const Color(0xFFF3F4F6),
        body: SafeArea(
          child: Stack(
            children: [
              WebViewWidget(controller: _controller),
              if (_isImportingSharedFiles)
                _SharedImportProgress(
                  completed: _sharedFilesCompleted,
                  total: _sharedFilesTotal,
                  isEnglish: _isEnglish,
                ),
              if (_shareErrorMessage != null && !_isImportingSharedFiles)
                _SharedImportError(
                  message: _shareErrorMessage!,
                  onDismiss: () => setState(() => _shareErrorMessage = null),
                ),
              if (_errorMessage != null)
                _ConnectionError(
                  message: _errorMessage!,
                  onRetry: _reload,
                  isEnglish: _isEnglish,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SharedImportProgress extends StatelessWidget {
  const _SharedImportProgress({
    required this.completed,
    required this.total,
    required this.isEnglish,
  });

  final int completed;
  final int total;
  final bool isEnglish;

  @override
  Widget build(BuildContext context) {
    final progress = total == 0 ? null : completed / total;
    return ColoredBox(
      color: const Color(0x660F172A),
      child: Center(
        child: Container(
          width: 310,
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(22),
            boxShadow: const [
              BoxShadow(
                color: Color(0x330F172A),
                blurRadius: 35,
                offset: Offset(0, 16),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.cloud_upload_rounded,
                color: Color(0xFF0F4C81),
                size: 42,
              ),
              const SizedBox(height: 14),
              Text(
                isEnglish
                    ? 'Adding shared files…'
                    : 'جارٍ إضافة الملفات المشتركة…',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Color(0xFF0F172A),
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 14),
              LinearProgressIndicator(value: progress, minHeight: 7),
              const SizedBox(height: 10),
              Text(
                '$completed / $total',
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SharedImportError extends StatelessWidget {
  const _SharedImportError({required this.message, required this.onDismiss});

  final String message;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    return Positioned(
      left: 16,
      right: 16,
      bottom: 18,
      child: Material(
        color: const Color(0xFF991B1B),
        borderRadius: BorderRadius.circular(14),
        elevation: 8,
        child: Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(16, 12, 8, 12),
          child: Row(
            children: [
              const Icon(Icons.error_outline_rounded, color: Colors.white),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  message,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              IconButton(
                onPressed: onDismiss,
                icon: const Icon(Icons.close_rounded, color: Colors.white),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ConnectionError extends StatelessWidget {
  const _ConnectionError({
    required this.message,
    required this.onRetry,
    required this.isEnglish,
  });

  final String message;
  final VoidCallback onRetry;
  final bool isEnglish;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF3F4F6),
      padding: const EdgeInsets.all(20),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE5E7EB)),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x1A0F172A),
                  blurRadius: 28,
                  offset: Offset(0, 14),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.wifi_off_rounded,
                  color: Color(0xFFB91C1C),
                  size: 42,
                ),
                const SizedBox(height: 14),
                Text(
                  message,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Color(0xFF111827),
                    fontFamily: 'Arial',
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    height: 1.7,
                  ),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: onRetry,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F4C81),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 22,
                      vertical: 12,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: Text(isEnglish ? 'Retry' : 'إعادة المحاولة'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
