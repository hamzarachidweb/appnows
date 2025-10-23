import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/article.dart';

class ApiService {
  // توحيد رابط API الأساسي
  static const String _baseUrl = 'http://localhost/nows/admin/api';

  // نقاط النهاية
  static const String _articlesEndpoint = '$_baseUrl/get_articles.php';

  // إعدادات الطلبات
  static const Duration _requestTimeout = Duration(seconds: 10);
  static const Map<String, String> _headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  /// Fetch all articles from the API
  static Future<List<Article>> fetchArticles() async {
    try {
      print('Trying URL: $_articlesEndpoint'); // Debug log
      final uri = Uri.parse(_articlesEndpoint);
      final response = await http
          .get(
            uri,
            headers: _headers,
          )
          .timeout(_requestTimeout);

      if (response.statusCode != 200) {
        throw Exception(
            'Failed to load articles. Status code: ${response.statusCode}');
      }

      print('Successfully connected to: $_articlesEndpoint'); // Debug log
      print('Response status code: ${response.statusCode}'); // Debug log
      print('Response body length: ${response.body.length}'); // Debug log

      final String responseBody = response.body;

      if (responseBody.isEmpty) {
        throw Exception('Empty response from server');
      }

      final Map<String, dynamic> jsonData = json.decode(responseBody);
      print('JSON data keys: ${jsonData.keys.toList()}'); // Debug log

      final ArticlesResponse articlesResponse =
          ArticlesResponse.fromJson(jsonData);

      if (articlesResponse.status == 'success') {
        print(
            'Successfully fetched ${articlesResponse.articles.length} articles'); // Debug log
        return articlesResponse.articles;
      } else {
        throw Exception(
            'API returned error status: ${articlesResponse.status}');
      }
    } catch (e) {
      print('Error fetching articles: $e'); // Debug log
      throw Exception('Network error: $e');
    }
  }

  /// Check if the API is reachable
  static Future<bool> isApiReachable() async {
    try {
      print('Checking API connectivity: $_articlesEndpoint'); // Debug log
      final uri = Uri.parse(_articlesEndpoint);
      final response = await http
          .get(
            uri,
            headers: _headers,
          )
          .timeout(const Duration(seconds: 5));

      print(
          'API connectivity check - Status: ${response.statusCode}'); // Debug log

      if (response.statusCode == 200 || response.statusCode == 201) {
        return true;
      }
      return false;
    } catch (e) {
      print('API connectivity check failed: $e'); // Debug log
      return false;
    }
  }

  /// Get articles count without loading full data
  static Future<int> getArticlesCount() async {
    try {
      final articles = await fetchArticles();
      return articles.length;
    } catch (e) {
      print('Error getting articles count: $e');
      return 0;
    }
  }

  /// إضافة إعجاب لمقال (محاكاة محلية)
  static Future<bool> likeArticle(int articleId) async {
    try {
      print('Simulating like for article: $articleId');
      // محاكاة تأخير الشبكة
      await Future.delayed(const Duration(milliseconds: 500));
      return true; // دائماً ناجح في المحاكاة
    } catch (e) {
      print('Error liking article: $e');
      return false;
    }
  }

  /// إزالة إعجاب من مقال (محاكاة محلية)
  static Future<bool> unlikeArticle(int articleId) async {
    try {
      print('Simulating unlike for article: $articleId');
      // محاكاة تأخير الشبكة
      await Future.delayed(const Duration(milliseconds: 500));
      return true; // دائماً ناجح في المحاكاة
    } catch (e) {
      print('Error unliking article: $e');
      return false;
    }
  }

  /// الحصول على قائمة المقالات المُعجب بها (محاكاة محلية)
  static Future<List<int>> getLikedArticles() async {
    try {
      print('Simulating get liked articles');
      // محاكاة تأخير الشبكة
      await Future.delayed(const Duration(milliseconds: 500));
      // إرجاع قائمة فارغة - الاعتماد على التخزين المحلي
      return [];
    } catch (e) {
      print('Error getting liked articles: $e');
      return [];
    }
  }

  /// الحصول على عدد الإعجابات لمقال (محاكاة محلية)
  static Future<int> getArticleLikesCount(int articleId) async {
    try {
      print('Simulating get likes count for article: $articleId');
      // محاكاة تأخير الشبكة
      await Future.delayed(const Duration(milliseconds: 300));
      // إرجاع عدد عشوائي للمحاكاة
      return 0; // سيتم الاعتماد على العدد المحلي
    } catch (e) {
      print('Error getting article likes count: $e');
      return 0;
    }
  }
}
