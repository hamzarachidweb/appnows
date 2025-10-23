import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:my_app/main.dart';

void main() {
  group('App Integration Tests', () {
    testWidgets('App should load and display articles',
        (WidgetTester tester) async {
      // Build our app and trigger a frame.
      await tester.pumpWidget(const MyApp());

      // Verify that the app bar is present
      expect(find.text('Articles'), findsOneWidget);

      // Wait for the loading to complete
      await tester.pump(const Duration(seconds: 1));

      // Let the app load data
      await tester.pump();
      await tester.pump(const Duration(seconds: 2));

      // Check if refresh button is present
      expect(find.byIcon(Icons.refresh), findsOneWidget);

      // The app should show either articles or an error state
      // Since we can't guarantee network state in tests, we check for UI elements
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
      expect(find.byType(RefreshIndicator), findsOneWidget);
    });

    testWidgets('App should have proper theme setup',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      final MaterialApp app = tester.widget(find.byType(MaterialApp));

      expect(app.title, equals('Articles App'));
      expect(app.debugShowCheckedModeBanner, isFalse);
      expect(app.theme, isNotNull);
      expect(app.theme!.useMaterial3, isTrue);
    });
  });
}
