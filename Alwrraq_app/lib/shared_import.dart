import 'package:flutter/foundation.dart';

enum SharedFileKind { pdf, word, image }

@immutable
class SharedImportFile {
  const SharedImportFile({
    required this.path,
    required this.name,
    required this.mimeType,
    required this.size,
    required this.kind,
  });

  factory SharedImportFile.fromMap(Map<Object?, Object?> value) {
    final path = value['path']?.toString() ?? '';
    final name = value['name']?.toString() ?? path.split('/').last;
    final mimeType = value['mimeType']?.toString().toLowerCase() ?? '';
    final size = int.tryParse(value['size']?.toString() ?? '') ?? 0;
    final kind = sharedFileKind(name: name, mimeType: mimeType);

    if (path.isEmpty || kind == null) {
      throw const FormatException('Unsupported shared file');
    }

    return SharedImportFile(
      path: path,
      name: name,
      mimeType: mimeType,
      size: size,
      kind: kind,
    );
  }

  final String path;
  final String name;
  final String mimeType;
  final int size;
  final SharedFileKind kind;

  String get uploadType => kind.name;
}

SharedFileKind? sharedFileKind({
  required String name,
  required String mimeType,
}) {
  final extension = name.toLowerCase().split('.').last;
  final normalizedMime = mimeType.toLowerCase();

  if (normalizedMime == 'application/pdf' || extension == 'pdf') {
    return SharedFileKind.pdf;
  }
  if (const {
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      }.contains(normalizedMime) ||
      const {'doc', 'docx'}.contains(extension)) {
    return SharedFileKind.word;
  }
  if (normalizedMime.startsWith('image/') ||
      const {
        'jpg',
        'jpeg',
        'jpe',
        'png',
        'gif',
        'webp',
        'bmp',
        'dib',
        'tif',
        'tiff',
        'heic',
        'heif',
        'avif',
        'svg',
        'ico',
        'jfif',
        'jxl',
        'jp2',
        'j2k',
        'jpf',
        'jpx',
        'apng',
        'psd',
        'psb',
        'ai',
        'eps',
        'hdr',
        'exr',
        'pbm',
        'pgm',
        'ppm',
        'pnm',
        'raw',
        'dng',
        'cr2',
        'cr3',
        'nef',
        'nrw',
        'arw',
        'srf',
        'sr2',
        'raf',
        'orf',
        'rw2',
        'pef',
        'x3f',
      }.contains(extension)) {
    return SharedFileKind.image;
  }

  return null;
}

const Map<String, Set<SharedFileKind>> serviceFileKinds = {
  'notes': {SharedFileKind.pdf},
  'books': {SharedFileKind.pdf},
  'color_printing': {SharedFileKind.pdf},
  'thesis': {SharedFileKind.pdf, SharedFileKind.word},
  'phd': {SharedFileKind.pdf, SharedFileKind.word},
  'formatting': {SharedFileKind.word},
  'images': {SharedFileKind.image},
};

bool serviceAcceptsSharedKinds(String service, Set<SharedFileKind> kinds) {
  final supported = serviceFileKinds[service];
  return supported != null && kinds.isNotEmpty && supported.containsAll(kinds);
}
