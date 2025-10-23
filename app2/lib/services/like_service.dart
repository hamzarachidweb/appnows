import 'api_service.dart';
import 'local_storage_service.dart';
import '../models/article.dart';

class LikeService {
  /// إعجاب بمقال مع حفظ البيانات الكاملة
  static Future<bool> likeArticleWithData(Article article) async {
    try {
      // إرسال الإعجاب إلى الخادم
      final success = await ApiService.likeArticle(article.id);

      if (success) {
        // حفظ الإعجاب محلياً
        await LocalStorageService.addToLiked(article.id);
        // حفظ المقال الكامل في المفضلة
        await LocalStorageService.addToFavorites(article.toJson());
        print('Successfully liked article ${article.id} with full data');
        return true;
      } else {
        print('Failed to like article ${article.id} on server');
        return false;
      }
    } catch (e) {
      print('Error liking article ${article.id}: $e');
      return false;
    }
  }

  /// إعجاب بمقال (النسخة القديمة)
  static Future<bool> likeArticle(int articleId) async {
    try {
      // إرسال الإعجاب إلى الخادم
      final success = await ApiService.likeArticle(articleId);

      if (success) {
        // حفظ الإعجاب محلياً
        await LocalStorageService.addToLiked(articleId);
        print('Successfully liked article $articleId');
        return true;
      } else {
        print('Failed to like article $articleId on server');
        return false;
      }
    } catch (e) {
      print('Error liking article $articleId: $e');
      return false;
    }
  }

  /// إلغاء الإعجاب بمقال
  static Future<bool> unlikeArticle(int articleId) async {
    try {
      // إرسال إلغاء الإعجاب إلى الخادم
      final success = await ApiService.unlikeArticle(articleId);

      if (success) {
        // إزالة الإعجاب محلياً
        await LocalStorageService.removeFromLiked(articleId);
        // إزالة من المفضلة أيضاً
        await LocalStorageService.removeFromFavorites(articleId);
        print('Successfully unliked article $articleId');
        return true;
      } else {
        print('Failed to unlike article $articleId on server');
        return false;
      }
    } catch (e) {
      print('Error unliking article $articleId: $e');
      return false;
    }
  }

  /// تبديل حالة الإعجاب مع بيانات المقال
  static Future<bool> toggleLikeWithData(Article article) async {
    try {
      final isLiked = await LocalStorageService.isArticleLiked(article.id);

      if (isLiked) {
        return await unlikeArticle(article.id);
      } else {
        return await likeArticleWithData(article);
      }
    } catch (e) {
      print('Error toggling like for article ${article.id}: $e');
      return false;
    }
  }

  /// تبديل حالة الإعجاب (النسخة القديمة)
  static Future<bool> toggleLike(int articleId) async {
    try {
      final isLiked = await LocalStorageService.isArticleLiked(articleId);

      if (isLiked) {
        return await unlikeArticle(articleId);
      } else {
        return await likeArticle(articleId);
      }
    } catch (e) {
      print('Error toggling like for article $articleId: $e');
      return false;
    }
  }

  /// التحقق من حالة الإعجاب لمقال
  static Future<bool> isLiked(int articleId) async {
    try {
      return await LocalStorageService.isArticleLiked(articleId);
    } catch (e) {
      print('Error checking like status for article $articleId: $e');
      return false;
    }
  }

  /// الحصول على جميع المقالات المُعجب بها
  static Future<List<int>> getLikedArticleIds() async {
    try {
      return await LocalStorageService.getLikedArticles();
    } catch (e) {
      print('Error getting liked articles: $e');
      return [];
    }
  }

  /// مزامنة الإعجابات مع الخادم
  static Future<void> syncWithServer() async {
    try {
      print('Syncing likes with server...');

      // الحصول على الإعجابات المحلية
      final localLikes = await LocalStorageService.getLikedArticles();

      // الحصول على الإعجابات من الخادم
      final serverLikes = await ApiService.getLikedArticles();

      // دمج القوائم (إعطاء الأولوية للخادم)
      final Set<int> allLikes = {...serverLikes, ...localLikes};

      // حفظ القائمة المدمجة محلياً
      await LocalStorageService.saveLikedArticles(allLikes.toList());

      print('Sync completed: ${allLikes.length} liked articles');
    } catch (e) {
      print('Error syncing with server: $e');
    }
  }

  /// إضافة مقال إلى المفضلة (مع البيانات الكاملة)
  static Future<void> addToFavorites(Map<String, dynamic> article) async {
    try {
      await LocalStorageService.addToFavorites(article);
      print('Added article ${article['id']} to favorites');
    } catch (e) {
      print('Error adding to favorites: $e');
    }
  }

  /// إزالة مقال من المفضلة
  static Future<void> removeFromFavorites(int articleId) async {
    try {
      await LocalStorageService.removeFromFavorites(articleId);
      print('Removed article $articleId from favorites');
    } catch (e) {
      print('Error removing from favorites: $e');
    }
  }

  /// الحصول على المقالات المفضلة
  static Future<List<Map<String, dynamic>>> getFavoriteArticles() async {
    try {
      return await LocalStorageService.getFavoriteArticles();
    } catch (e) {
      print('Error getting favorite articles: $e');
      return [];
    }
  }
}
