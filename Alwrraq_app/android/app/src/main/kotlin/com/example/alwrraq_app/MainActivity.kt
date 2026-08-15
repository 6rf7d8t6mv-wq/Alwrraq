package com.alwrraq.app

import android.app.DownloadManager
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Environment
import android.provider.OpenableColumns
import android.webkit.MimeTypeMap
import android.webkit.CookieManager
import android.view.WindowManager
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.android.FlutterFragmentActivity
import io.flutter.plugin.common.MethodChannel
import java.io.File
import java.io.FileOutputStream
import java.util.UUID

class MainActivity : FlutterFragmentActivity() {
    private val downloadsChannel = "alwrraq/downloads"
    private val securityChannel = "alwrraq/security"
    private val shareChannelName = "alwrraq/share"
    private var shareChannel: MethodChannel? = null

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

        shareChannel = MethodChannel(
            flutterEngine.dartExecutor.binaryMessenger,
            shareChannelName
        ).also { channel ->
            channel.setMethodCallHandler { call, result ->
                when (call.method) {
                    "getInitialShare" -> result.success(consumeShareIntent(intent))
                    "getCookies" -> {
                        val url = call.argument<String>("url")
                        result.success(
                            if (url.isNullOrBlank()) null
                            else CookieManager.getInstance().getCookie(url)
                        )
                    }
                    else -> result.notImplemented()
                }
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        val files = consumeShareIntent(intent)
        if (files.isNotEmpty()) {
            shareChannel?.invokeMethod("sharedFiles", files)
        }
    }

    private fun consumeShareIntent(sourceIntent: Intent?): List<Map<String, Any>> {
        if (sourceIntent == null ||
            (sourceIntent.action != Intent.ACTION_SEND &&
                sourceIntent.action != Intent.ACTION_SEND_MULTIPLE)
        ) {
            return emptyList()
        }

        val uris = linkedSetOf<Uri>()
        sourceIntent.clipData?.let { clipData ->
            for (index in 0 until clipData.itemCount) {
                clipData.getItemAt(index).uri?.let(uris::add)
            }
        }

        @Suppress("DEPRECATION")
        if (sourceIntent.action == Intent.ACTION_SEND_MULTIPLE) {
            sourceIntent.getParcelableArrayListExtra<Uri>(Intent.EXTRA_STREAM)
                ?.let(uris::addAll)
        } else {
            @Suppress("DEPRECATION")
            sourceIntent.getParcelableExtra<Uri>(Intent.EXTRA_STREAM)?.let(uris::add)
        }

        sourceIntent.action = null
        return uris.mapNotNull { copySharedUri(it, sourceIntent.type) }
    }

    private fun copySharedUri(uri: Uri, fallbackMimeType: String?): Map<String, Any>? {
        return try {
            var displayName: String? = null
            var size = 0L
            contentResolver.query(
                uri,
                arrayOf(OpenableColumns.DISPLAY_NAME, OpenableColumns.SIZE),
                null,
                null,
                null
            )?.use { cursor ->
                if (cursor.moveToFirst()) {
                    val nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME)
                    val sizeIndex = cursor.getColumnIndex(OpenableColumns.SIZE)
                    if (nameIndex >= 0) displayName = cursor.getString(nameIndex)
                    if (sizeIndex >= 0 && !cursor.isNull(sizeIndex)) size = cursor.getLong(sizeIndex)
                }
            }

            val mimeType = contentResolver.getType(uri)
                ?: fallbackMimeType
                ?: "application/octet-stream"
            val extension = displayName
                ?.substringAfterLast('.', "")
                ?.takeIf { it.isNotBlank() }
                ?: MimeTypeMap.getSingleton().getExtensionFromMimeType(mimeType)
                ?: "bin"
            val originalName = displayName?.takeIf { it.isNotBlank() }
                ?: "shared-${System.currentTimeMillis()}.$extension"
            val safeName = originalName.replace(Regex("[^A-Za-z0-9._\\-\u0600-\u06FF ]"), "_")
            val directory = File(cacheDir, "shared_imports").apply { mkdirs() }
            val destination = File(directory, "${UUID.randomUUID()}-$safeName")

            val input = contentResolver.openInputStream(uri) ?: return null
            input.use { source ->
                FileOutputStream(destination).use { target -> source.copyTo(target) }
            }
            if (size <= 0) size = destination.length()

            mapOf(
                "path" to destination.absolutePath,
                "name" to originalName,
                "mimeType" to mimeType,
                "size" to size
            )
        } catch (_: Exception) {
            null
        }
    }
}
