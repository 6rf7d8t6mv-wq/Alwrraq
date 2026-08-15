import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:local_auth/local_auth.dart';
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
  static const _biometricTokenKey = 'alwrraq_biometric_token';
  static const _biometricDeviceKey = 'alwrraq_biometric_device_id';
  static const _biometricPromptKey = 'alwrraq_biometric_prompt_dismissed';
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
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();

  late final WebViewController _controller;
  final LocalAuthentication _localAuth = LocalAuthentication();
  final WebViewCookieManager _cookieManager = WebViewCookieManager();
  var _isEnglish = false;
  var _biometricSupported = false;
  var _biometricEnabled = false;
  var _biometricGateLocked = false;
  var _biometricBusy = false;
  var _biometricOfferShown = false;
  var _biometricLoginPending = false;
  var _biometricLabel = 'بصمة الجهاز';
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
      ..addJavaScriptChannel(
        'AlwrraqBiometric',
        onMessageReceived: (message) {
          unawaited(_handleBiometricMessage(message.message));
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
            if (_biometricLoginPending) {
              _biometricLoginPending = false;
              if (currentUrl?.path == '/app/login') {
                await _secureStorage.delete(key: _biometricTokenKey);
                if (mounted) setState(() => _biometricEnabled = false);
              }
            }
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
            await _syncBiometricStateToWeb();
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

    var hasInitialShare = false;
    try {
      final initialShare = await _shareChannel.invokeMethod<Object?>(
        'getInitialShare',
      );
      hasInitialShare = await _acceptSharedFiles(initialShare, loadPage: false);
    } on PlatformException {
      // The normal web experience remains available on unsupported platforms.
    }

    if (await _prepareBiometricEntry()) return;
    await _loadSite(hasInitialShare ? _sharedServicesUri() : null);
  }

  Future<bool> _acceptSharedFiles(
    Object? payload, {
    bool loadPage = true,
  }) async {
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
    if (loadPage) await _loadSite(shareUri);
    return true;
  }

  Future<bool> _prepareBiometricEntry() async {
    if (!const {
      TargetPlatform.android,
      TargetPlatform.iOS,
    }.contains(defaultTargetPlatform)) {
      return false;
    }

    await _refreshBiometricSupport();
    final token = await _secureStorage.read(key: _biometricTokenKey);
    final deviceId = await _secureStorage.read(key: _biometricDeviceKey);
    _biometricEnabled = token != null && deviceId != null;
    if (!_biometricEnabled) return false;

    if (mounted) {
      setState(() {
        _biometricGateLocked = true;
        _biometricBusy = true;
      });
    }

    final authenticated = await _authenticateBiometrically();
    if (!mounted) return true;
    setState(() => _biometricBusy = false);
    if (!authenticated) return true;

    await _submitBiometricLogin(token: token!, deviceId: deviceId!);
    setState(() => _biometricGateLocked = false);
    return true;
  }

  Future<void> _refreshBiometricSupport() async {
    try {
      final supported = await _localAuth.isDeviceSupported();
      final canCheck = await _localAuth.canCheckBiometrics;
      final available = await _localAuth.getAvailableBiometrics();
      final label = available.contains(BiometricType.face)
          ? 'Face ID'
          : available.contains(BiometricType.fingerprint)
          ? 'بصمة الإصبع'
          : 'بصمة الجهاز';
      if (!mounted) return;
      setState(() {
        _biometricSupported = supported && canCheck && available.isNotEmpty;
        _biometricLabel = label;
      });
    } on Object {
      if (mounted) setState(() => _biometricSupported = false);
    }
  }

  Future<bool> _authenticateBiometrically() async {
    if (!_biometricSupported) return false;
    try {
      return await _localAuth.authenticate(
        localizedReason: _isEnglish
            ? 'Authenticate to open Alwrraq'
            : 'تحقق من هويتك لفتح تطبيق الورّاق',
        biometricOnly: true,
        persistAcrossBackgrounding: true,
      );
    } on Object {
      return false;
    }
  }

  Future<void> _submitBiometricLogin({
    required String token,
    required String deviceId,
  }) async {
    _biometricLoginPending = true;
    final body = Uri(
      queryParameters: {'token': token, 'device_id': deviceId},
    ).query;
    await _controller.loadRequest(
      Uri.parse('${_siteUri.origin}/app/biometric/login'),
      method: LoadRequestMethod.post,
      headers: const {'Content-Type': 'application/x-www-form-urlencoded'},
      body: Uint8List.fromList(utf8.encode(body)),
    );
  }

  Future<void> _handleBiometricMessage(String message) async {
    final currentUrl = Uri.tryParse(await _controller.currentUrl() ?? '');
    if (currentUrl?.scheme != _siteUri.scheme ||
        currentUrl?.host != _siteUri.host) {
      return;
    }

    Map<String, dynamic> data;
    try {
      data = (jsonDecode(message) as Map).cast<String, dynamic>();
    } on Object {
      return;
    }

    switch (data['action']) {
      case 'session':
        await _syncBiometricStateToWeb();
        final dismissed = await _secureStorage.read(key: _biometricPromptKey);
        if (!_biometricEnabled &&
            _biometricSupported &&
            dismissed != 'true' &&
            !_biometricOfferShown) {
          _biometricOfferShown = true;
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) unawaited(_offerBiometricEnrollment());
          });
        }
        return;
      case 'enable':
        await _enableBiometricLogin();
        return;
      case 'disable':
        await _disableBiometricLogin();
        return;
      case 'issued':
        final token = data['token']?.toString();
        if (token == null || token.isEmpty) return;
        await _secureStorage.write(key: _biometricTokenKey, value: token);
        await _secureStorage.write(key: _biometricPromptKey, value: 'true');
        if (mounted) setState(() => _biometricEnabled = true);
        await _syncBiometricStateToWeb();
        _showBiometricNotice('تم تفعيل الدخول بـ $_biometricLabel بنجاح.');
        return;
      case 'revoked':
        await _secureStorage.delete(key: _biometricTokenKey);
        if (mounted) setState(() => _biometricEnabled = false);
        await _syncBiometricStateToWeb();
        _showBiometricNotice('تم إيقاف الدخول بالبصمة على هذا الجهاز.');
        return;
      case 'error':
        _showBiometricNotice(
          data['message']?.toString() ?? 'تعذر إكمال العملية.',
        );
        return;
    }
  }

  Future<void> _offerBiometricEnrollment() async {
    final accepted = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        icon: const Icon(Icons.fingerprint_rounded, size: 46),
        title: Text('تفعيل $_biometricLabel؟'),
        content: const Text(
          'ادخل إلى تطبيق الورّاق بسرعة وأمان دون حفظ كلمة المرور على جهازك.',
          textAlign: TextAlign.center,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('لاحقًا'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('تفعيل الآن'),
          ),
        ],
      ),
    );
    if (accepted == true) {
      await _enableBiometricLogin();
    } else {
      await _secureStorage.write(key: _biometricPromptKey, value: 'true');
    }
  }

  Future<void> _enableBiometricLogin() async {
    await _refreshBiometricSupport();
    if (!_biometricSupported) {
      _showBiometricNotice('فعّل بصمة الوجه أو الإصبع من إعدادات جهازك أولًا.');
      return;
    }
    if (!await _authenticateBiometrically()) return;

    final deviceId = await _getOrCreateDeviceId();
    final platform = defaultTargetPlatform == TargetPlatform.iOS
        ? 'ios'
        : 'android';
    final deviceName = platform == 'ios' ? 'جهاز Apple' : 'جهاز Android';
    await _controller.runJavaScript(
      'window.alwrraqIssueBiometricToken?.(${jsonEncode({'device_id': deviceId, 'device_name': deviceName, 'platform': platform})});',
    );
  }

  Future<void> _disableBiometricLogin() async {
    final deviceId = await _secureStorage.read(key: _biometricDeviceKey);
    if (deviceId == null) return;
    await _controller.runJavaScript(
      'window.alwrraqRevokeBiometricToken?.(${jsonEncode(deviceId)});',
    );
  }

  Future<String> _getOrCreateDeviceId() async {
    final existing = await _secureStorage.read(key: _biometricDeviceKey);
    if (existing != null) return existing;
    final random = Random.secure();
    final bytes = List<int>.generate(32, (_) => random.nextInt(256));
    final value = base64UrlEncode(bytes).replaceAll('=', '');
    await _secureStorage.write(key: _biometricDeviceKey, value: value);
    return value;
  }

  Future<void> _syncBiometricStateToWeb() async {
    try {
      await _controller.runJavaScript(
        'window.alwrraqSetBiometricState?.(${jsonEncode({'supported': _biometricSupported, 'enabled': _biometricEnabled, 'label': _biometricLabel})});',
      );
    } on Object {
      // Public and login pages do not expose the authenticated app bridge.
    }
  }

  void _showBiometricNotice(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _retryBiometricGate() async {
    setState(() => _biometricBusy = true);
    final authenticated = await _authenticateBiometrically();
    if (!mounted) return;
    setState(() => _biometricBusy = false);
    if (!authenticated) return;
    final token = await _secureStorage.read(key: _biometricTokenKey);
    final deviceId = await _secureStorage.read(key: _biometricDeviceKey);
    if (token == null || deviceId == null) return;
    await _submitBiometricLogin(token: token, deviceId: deviceId);
    if (mounted) setState(() => _biometricGateLocked = false);
  }

  Future<void> _usePasswordInstead() async {
    await _cookieManager.clearCookies();
    if (!mounted) return;
    setState(() => _biometricGateLocked = false);
    await _loadSite(Uri.parse('${_siteUri.origin}/app/login'));
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
              if (_biometricGateLocked)
                _BiometricGate(
                  busy: _biometricBusy,
                  label: _biometricLabel,
                  isEnglish: _isEnglish,
                  onAuthenticate: _retryBiometricGate,
                  onUsePassword: _usePasswordInstead,
                ),
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

class _BiometricGate extends StatelessWidget {
  const _BiometricGate({
    required this.busy,
    required this.label,
    required this.isEnglish,
    required this.onAuthenticate,
    required this.onUsePassword,
  });

  final bool busy;
  final String label;
  final bool isEnglish;
  final VoidCallback onAuthenticate;
  final VoidCallback onUsePassword;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: const Color(0xFFF4F7FA),
      child: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 390),
            child: Container(
              padding: const EdgeInsets.fromLTRB(26, 30, 26, 24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(28),
                border: Border.all(color: const Color(0xFFE2E8F0)),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x1F0F172A),
                    blurRadius: 38,
                    offset: Offset(0, 18),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 82,
                    height: 82,
                    decoration: const BoxDecoration(
                      color: Color(0xFFE9F2FA),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.fingerprint_rounded,
                      color: Color(0xFF0F4C81),
                      size: 52,
                    ),
                  ),
                  const SizedBox(height: 22),
                  Text(
                    isEnglish ? 'Welcome back' : 'مرحبًا بعودتك',
                    style: const TextStyle(
                      color: Color(0xFF0F172A),
                      fontSize: 23,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    isEnglish
                        ? 'Use biometrics to securely open Alwrraq.'
                        : 'استخدم $label لفتح تطبيق الورّاق بأمان.',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Color(0xFF64748B),
                      height: 1.6,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: busy ? null : onAuthenticate,
                      icon: busy
                          ? const SizedBox.square(
                              dimension: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.fingerprint_rounded),
                      label: Text(isEnglish ? 'Unlock' : 'الدخول بـ $label'),
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF0F4C81),
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: busy ? null : onUsePassword,
                    child: Text(
                      isEnglish
                          ? 'Use password instead'
                          : 'الدخول بكلمة المرور',
                    ),
                  ),
                ],
              ),
            ),
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
