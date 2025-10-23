import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class LocalStorageService {
  static const String _likedArticlesKey = 'liked_articles';
  static const String _favoritesKey = 'favorite_articles';

  /// حفظ قائمة المقالات المُعجب بها
  static Future<void> saveLikedArticles(List<int> articleIds) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String encodedData = json.encode(articleIds);
      await prefs.setString(_likedArticlesKey, encodedData);
      print('Saved liked articles: $articleIds');
    } catch (e) {
      print('Error saving liked articles: $e');
    }
  }

  /// الحصول على قائمة المقالات المُعجب بها
  static Future<List<int>> getLikedArticles() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? encodedData = prefs.getString(_likedArticlesKey);
      if (encodedData != null) {
        final List<dynamic> decodedData = json.decode(encodedData);
        return decodedData.cast<int>();
      }
      return [];
    } catch (e) {
      print('Error getting liked articles: $e');
      return [];
    }
  }

  /// إضافة مقال إلى المفضلة
  static Future<void> addToLiked(int articleId) async {
    try {
      final likedArticles = await getLikedArticles();
      if (!likedArticles.contains(articleId)) {
        likedArticles.add(articleId);
        await saveLikedArticles(likedArticles);
        print('Added article $articleId to liked');
      }
    } catch (e) {
      print('Error adding to liked: $e');
    }
  }

  /// إزالة مقال من المفضلة
  static Future<void> removeFromLiked(int articleId) async {
    try {
      final likedArticles = await getLikedArticles();
      if (likedArticles.contains(articleId)) {
        likedArticles.remove(articleId);
        await saveLikedArticles(likedArticles);
        print('Removed article $articleId from liked');
      }
    } catch (e) {
      print('Error removing from liked: $e');
    }
  }

  /// التحقق من كون المقال مُعجب به
  static Future<bool> isArticleLiked(int articleId) async {
    try {
      final likedArticles = await getLikedArticles();
      return likedArticles.contains(articleId);
    } catch (e) {
      print('Error checking if article is liked: $e');
      return false;
    }
  }

  /// مسح جميع المقالات المُعجب بها
  static Future<void> clearLikedArticles() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_likedArticlesKey);
      print('Cleared all liked articles');
    } catch (e) {
      print('Error clearing liked articles: $e');
    }
  }

  /// حفظ المقالات المفضلة (نسخ كاملة)
  static Future<void> saveFavoriteArticles(
      List<Map<String, dynamic>> articles) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String encodedData = json.encode(articles);
      await prefs.setString(_favoritesKey, encodedData);
      print('Saved ${articles.length} favorite articles');
    } catch (e) {
      print('Error saving favorite articles: $e');
    }
  }

  /// الحصول على المقالات المفضلة
  static Future<List<Map<String, dynamic>>> getFavoriteArticles() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? encodedData = prefs.getString(_favoritesKey);
      if (encodedData != null) {
        final List<dynamic> decodedData = json.decode(encodedData);
        return decodedData.cast<Map<String, dynamic>>();
      }
      return [];
    } catch (e) {
      print('Error getting favorite articles: $e');
      return [];
    }
  }

  /// إضافة مقال إلى المفضلة (نسخة كاملة)
  static Future<void> addToFavorites(Map<String, dynamic> article) async {
    try {
      final favorites = await getFavoriteArticles();
      final articleId = article['id'];

      // التحقق من عدم وجود المقال مسبقاً
      final exists = favorites.any((fav) => fav['id'] == articleId);
      if (!exists) {
        favorites.add(article);
        await saveFavoriteArticles(favorites);
        print('Added article $articleId to favorites');
      }
    } catch (e) {
      print('Error adding to favorites: $e');
    }
  }

  /// إزالة مقال من المفضلة
  static Future<void> removeFromFavorites(int articleId) async {
    try {
      final favorites = await getFavoriteArticles();
      favorites.removeWhere((article) => article['id'] == articleId);
      await saveFavoriteArticles(favorites);
      print('Removed article $articleId from favorites');
    } catch (e) {
      print('Error removing from favorites: $e');
    }
  }
}
