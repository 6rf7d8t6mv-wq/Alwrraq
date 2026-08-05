import 'package:flutter_test/flutter_test.dart';
import 'package:file_picker/file_picker.dart';

import 'package:alwrraq_app/android_file_selector.dart';
import 'package:alwrraq_app/main.dart';

void main() {
  test('App root can be constructed', () {
    expect(const AlwrraqApp(), isA<AlwrraqApp>());
  });

  group('Android WebView file filters', () {
    test('PDF fields only expose PDF files', () {
      final plan = androidFileSelectionPlan(['application/pdf']);

      expect(plan.type, FileType.custom);
      expect(plan.allowedExtensions, ['pdf']);
    });

    test('Word fields only expose DOC and DOCX files', () {
      final plan = androidFileSelectionPlan([
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      ]);

      expect(plan.type, FileType.custom);
      expect(plan.allowedExtensions, ['doc', 'docx']);
    });

    test('image fields open an image-only picker', () {
      final plan = androidFileSelectionPlan(['image/*']);

      expect(plan.type, FileType.image);
      expect(plan.allowedExtensions, isNull);
    });

    test('chat attachments preserve every supported extension', () {
      final plan = androidFileSelectionPlan([
        'image/jpeg,image/png,image/webp,image/gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip',
      ]);

      expect(plan.type, FileType.custom);
      expect(plan.allowedExtensions, [
        'csv',
        'doc',
        'docx',
        'gif',
        'jpeg',
        'jpg',
        'pdf',
        'png',
        'ppt',
        'pptx',
        'txt',
        'webp',
        'xls',
        'xlsx',
        'zip',
      ]);
    });
  });
}
