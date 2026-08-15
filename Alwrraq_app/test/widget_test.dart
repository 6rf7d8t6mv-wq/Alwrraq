import 'package:flutter_test/flutter_test.dart';
import 'package:file_picker/file_picker.dart';

import 'package:alwrraq_app/android_file_selector.dart';
import 'package:alwrraq_app/main.dart';
import 'package:alwrraq_app/shared_import.dart';

void main() {
  test('App root can be constructed', () {
    expect(const AlwrraqApp(), isA<AlwrraqApp>());
  });

  test('Biometric background lock uses a fixed 30 second grace period', () {
    expect(biometricBackgroundLockTimeout, const Duration(seconds: 30));
    expect(shouldLockAfterBackground(const Duration(seconds: 29)), isFalse);
    expect(shouldLockAfterBackground(const Duration(seconds: 30)), isTrue);
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

  group('Shared file routing', () {
    test('classifies PDF, Word, and image files', () {
      expect(
        sharedFileKind(name: 'document.pdf', mimeType: ''),
        SharedFileKind.pdf,
      );
      expect(
        sharedFileKind(
          name: 'document',
          mimeType:
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ),
        SharedFileKind.word,
      );
      expect(
        sharedFileKind(name: 'photo.heic', mimeType: 'image/heic'),
        SharedFileKind.image,
      );
    });

    test('PDF only exposes PDF-compatible services', () {
      final kinds = {SharedFileKind.pdf};

      expect(serviceAcceptsSharedKinds('notes', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('thesis', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('formatting', kinds), isFalse);
      expect(serviceAcceptsSharedKinds('images', kinds), isFalse);
    });

    test('Word only exposes Word-compatible services', () {
      final kinds = {SharedFileKind.word};

      expect(serviceAcceptsSharedKinds('formatting', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('phd', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('notes', kinds), isFalse);
    });

    test('images only expose image services', () {
      final kinds = {SharedFileKind.image};

      expect(serviceAcceptsSharedKinds('images', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('thesis', kinds), isFalse);
    });

    test('mixed PDF and Word only expose services accepting both', () {
      final kinds = {SharedFileKind.pdf, SharedFileKind.word};

      expect(serviceAcceptsSharedKinds('thesis', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('phd', kinds), isTrue);
      expect(serviceAcceptsSharedKinds('notes', kinds), isFalse);
      expect(serviceAcceptsSharedKinds('formatting', kinds), isFalse);
    });
  });
}
