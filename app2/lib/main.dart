import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'screens/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  // Set preferred orientations
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'تطبيق المقالات',
      debugShowCheckedModeBanner: false,
      // دعم اللغة العربية والاتجاه من اليمين لليسار
      locale: const Locale('ar'),
      supportedLocales: const [
        Locale('ar'), // Arabic
        Locale('en'), // English (fallback)
      ],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      // تفعيل RTL
      builder: (context, child) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: child!,
        );
      },
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0D47A1),
          primary: const Color(0xFF0D47A1),
          secondary: const Color(0xFFFFC107),
        ),
        useMaterial3: true,
        // استخدام خط Cairo العربي
        fontFamily: 'Cairo',
        // تحسين دعم RTL
        visualDensity: VisualDensity.adaptivePlatformDensity,
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF0D47A1),
          foregroundColor: Colors.white,
          elevation: 2,
          centerTitle: true,
          titleTextStyle: TextStyle(
            fontFamily: 'Cairo',
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        cardTheme: CardThemeData(
          elevation: 4,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        ),
        // تحسين النصوص العربية
        textTheme: const TextTheme(
          bodyLarge: TextStyle(fontFamily: 'Cairo', height: 1.6),
          bodyMedium: TextStyle(fontFamily: 'Cairo', height: 1.5),
          bodySmall: TextStyle(fontFamily: 'Cairo', height: 1.4),
          headlineLarge: TextStyle(fontFamily: 'Cairo', height: 1.3),
          headlineMedium: TextStyle(fontFamily: 'Cairo', height: 1.3),
          headlineSmall: TextStyle(fontFamily: 'Cairo', height: 1.3),
          titleLarge: TextStyle(fontFamily: 'Cairo', height: 1.4),
          titleMedium: TextStyle(fontFamily: 'Cairo', height: 1.4),
          titleSmall: TextStyle(fontFamily: 'Cairo', height: 1.4),
          labelLarge: TextStyle(fontFamily: 'Cairo'),
          labelMedium: TextStyle(fontFamily: 'Cairo'),
          labelSmall: TextStyle(fontFamily: 'Cairo'),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            textStyle: const TextStyle(fontFamily: 'Cairo'),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
        ),
        textButtonTheme: TextButtonThemeData(
          style: TextButton.styleFrom(
            textStyle: const TextStyle(fontFamily: 'Cairo'),
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            textStyle: const TextStyle(fontFamily: 'Cairo'),
          ),
        ),
        // تحسين النماذج والحقول
        inputDecorationTheme: const InputDecorationTheme(
          labelStyle: TextStyle(fontFamily: 'Cairo'),
          hintStyle: TextStyle(fontFamily: 'Cairo'),
          helperStyle: TextStyle(fontFamily: 'Cairo'),
          errorStyle: TextStyle(fontFamily: 'Cairo'),
        ),
      ),
      home: const SplashScreen(),
    );
  }
}
