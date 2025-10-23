// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:my_app/main.dart';

void main() {
  testWidgets('App launches and displays home screen',
      (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const MyApp());

    // Wait for the widget to settle
    await tester.pumpAndSettle();

    // Verify that our app launches successfully
    expect(find.byType(MaterialApp), findsOneWidget);

    // You can add more specific tests here based on your app's content
    // For example, if your HomeScreen has specific widgets or text
  });
}
