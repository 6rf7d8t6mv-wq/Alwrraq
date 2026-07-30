package com.example.alwrraq_app

import android.app.DownloadManager
import android.content.Context
import android.net.Uri
import android.os.Environment
import android.webkit.CookieManager
import android.view.WindowManager
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.android.FlutterActivity
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val downloadsChannel = "alwrraq/downloads"
    private val securityChannel = "alwrraq/security"

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, downloadsChannel)
            .setMethodCallHandler { call, result ->
                if (call.method != "download") {
                    result.notImplemented()
                    return@setMethodCallHandler
                }

                val url = call.argument<String>("url")
                val fileName = call.argument<String>("fileName")
                    ?.takeIf { it.isNotBlank() }
                    ?.replace(Regex("[\\\\/:*?\"<>|]"), "_")
                    ?: "alwrraq-file"

                if (url.isNullOrBlank()) {
                    result.error("invalid_url", "رابط التحميل غير صالح", null)
                    return@setMethodCallHandler
                }

                try {
                    val request = DownloadManager.Request(Uri.parse(url))
                        .setTitle(fileName)
                        .setDescription("تحميل الملف المستلم من الورّاق")
                        .setNotificationVisibility(
                            DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED
                        )
                        .setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName)

                    CookieManager.getInstance().getCookie(url)?.let { cookie ->
                        request.addRequestHeader("Cookie", cookie)
                    }
                    request.addRequestHeader("User-Agent", "Alwrraq Android App")

                    val downloadManager = getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
                    result.success(downloadManager.enqueue(request))
                } catch (error: Exception) {
                    result.error("download_failed", error.message, null)
                }
            }

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, securityChannel)
            .setMethodCallHandler { call, result ->
                if (call.method != "setSecureScreen") {
                    result.notImplemented()
                    return@setMethodCallHandler
                }

                val secure = call.argument<Boolean>("secure") == true
                runOnUiThread {
                    if (secure) {
                        window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                    } else {
                        window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                    }
                    result.success(null)
                }
            }
    }
}
