import 'package:flutter_test/flutter_test.dart';

import 'package:alwrraq_app/main.dart';

void main() {
  testWidgets('App opens login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const AlwrraqApp());

    expect(find.text('الورّاق'), findsWidgets);
    expect(find.text('تسجيل الدخول'), findsWidgets);
    expect(find.text('رقم الجوال'), findsOneWidget);
    expect(find.text('كلمة المرور'), findsOneWidget);
    expect(find.text('دخول'), findsOneWidget);
    expect(find.text('إنشاء حساب جديد'), findsOneWidget);
  });
}
