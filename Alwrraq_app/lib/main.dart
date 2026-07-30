import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';

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

  late final WebViewController _controller;
  var _isLoading = true;
  var _isEnglish = false;
  String? _errorMessage;

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
              _isLoading = true;
              _errorMessage = null;
            });
          },
          onPageFinished: (_) async {
            if (!mounted) return;
            setState(() => _isLoading = false);
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
              _isLoading = false;
              _errorMessage = _isEnglish
                  ? 'Alwrraq could not be opened. Check your internet connection and try again.'
                  : 'تعذر فتح تطبيق الورّاق. تحقق من اتصال الإنترنت ثم حاول مرة أخرى.';
            });
          },
        ),
      );
    _loadFreshSite();
  }

  Future<void> _loadFreshSite() async {
    await _controller.clearCache();
    await _controller.loadRequest(
      _siteUri,
      headers: const {'Cache-Control': 'no-cache'},
    );
  }

  Future<void> _reload() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    await _loadFreshSite();
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
              if (_isLoading) const LinearProgressIndicator(minHeight: 3),
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
