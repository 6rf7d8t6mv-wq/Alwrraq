import 'package:file_picker/file_picker.dart' as picker;
import 'package:webview_flutter_android/webview_flutter_android.dart';

class AndroidFileSelectionPlan {
  const AndroidFileSelectionPlan({required this.type, this.allowedExtensions});

  final picker.FileType type;
  final List<String>? allowedExtensions;
}

const Map<String, List<String>> _mimeExtensions = {
  'image/jpeg': ['jpg', 'jpeg'],
  'image/png': ['png'],
  'image/webp': ['webp'],
  'image/gif': ['gif'],
  'image/heic': ['heic'],
  'image/heif': ['heif'],
  'application/pdf': ['pdf'],
  'application/msword': ['doc'],
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document': [
    'docx',
  ],
  'application/vnd.ms-excel': ['xls'],
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['xlsx'],
  'application/vnd.ms-powerpoint': ['ppt'],
  'application/vnd.openxmlformats-officedocument.presentationml.presentation': [
    'pptx',
  ],
  'text/plain': ['txt'],
  'text/csv': ['csv'],
  'application/zip': ['zip'],
  'application/x-zip-compressed': ['zip'],
};

AndroidFileSelectionPlan androidFileSelectionPlan(List<String> acceptTypes) {
  final accepts = acceptTypes
      .expand((value) => value.split(','))
      .map((value) => value.trim().toLowerCase())
      .where((value) => value.isNotEmpty)
      .toSet();

  if (accepts.isEmpty || accepts.contains('*/*')) {
    return const AndroidFileSelectionPlan(type: picker.FileType.any);
  }

  final imageOnly = accepts.every(
    (value) =>
        value == 'image/*' ||
        value.startsWith('image/') ||
        const {
          '.jpg',
          '.jpeg',
          '.png',
          '.webp',
          '.gif',
          '.heic',
          '.heif',
        }.contains(value),
  );
  if (imageOnly) {
    return const AndroidFileSelectionPlan(type: picker.FileType.image);
  }

  final extensions = <String>{};
  for (final accept in accepts) {
    if (accept.startsWith('.')) {
      extensions.add(accept.substring(1));
    } else {
      extensions.addAll(_mimeExtensions[accept] ?? const <String>[]);
    }
  }

  if (extensions.isEmpty) {
    return const AndroidFileSelectionPlan(type: picker.FileType.any);
  }

  return AndroidFileSelectionPlan(
    type: picker.FileType.custom,
    allowedExtensions: extensions.toList()..sort(),
  );
}

Future<List<String>> selectAndroidWebViewFiles(
  FileSelectorParams params,
) async {
  final plan = androidFileSelectionPlan(params.acceptTypes);
  final result = await picker.FilePicker.pickFiles(
    type: plan.type,
    allowedExtensions: plan.allowedExtensions,
    allowMultiple: params.mode == FileSelectorMode.openMultiple,
    withData: false,
    withReadStream: false,
  );

  if (result == null) return const <String>[];

  return result.files
      .map((file) => file.path)
      .whereType<String>()
      .map((path) {
        if (path.startsWith('content://') || path.startsWith('file://')) {
          return path;
        }
        return Uri.file(path).toString();
      })
      .toList(growable: false);
}
