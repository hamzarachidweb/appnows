import 'package:flutter/material.dart';
import '../models/article.dart';
import '../services/like_service.dart';
import '../services/api_service.dart';
import '../widgets/article_card.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  List<Article> favoriteArticles = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFavorites();
  }

  Future<void> _loadFavorites() async {
    setState(() {
      isLoading = true;
    });

    try {
      // الحصول على IDs المقالات المُعجب بها
      final likedIds = await LikeService.getLikedArticleIds();

      if (likedIds.isEmpty) {
        if (mounted) {
          setState(() {
            favoriteArticles = [];
            isLoading = false;
          });
        }
        return;
      }

      // الحصول على جميع المقالات من الـ API
      final allArticles = await ApiService.fetchArticles();

      // تصفية المقالات المُعجب بها
      final favorites = allArticles
          .where((article) => likedIds.contains(article.id))
          .toList();

      // تحديث حالة الإعجاب
      for (var article in favorites) {
        article.isLiked = true;
      }

      if (mounted) {
        setState(() {
          favoriteArticles = favorites;
          isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'خطأ في تحميل المفضلة: $e',
              style: const TextStyle(fontFamily: 'Cairo'),
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _onLikeChanged() {
    _loadFavorites(); // إعادة تحميل المفضلة عند تغيير الإعجاب
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'المقالات المفضلة',
          style: TextStyle(
            fontFamily: 'Cairo',
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF0D47A1), // اللون الأزرق
        elevation: 2,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            onPressed: _loadFavorites,
            icon: isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.refresh),
            tooltip: 'تحديث المفضلة',
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text(
              'جاري تحميل المفضلة...',
              style: TextStyle(
                fontSize: 16,
                fontFamily: 'Cairo',
              ),
            ),
          ],
        ),
      );
    }

    if (favoriteArticles.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.favorite_border,
                size: 80,
                color: Colors.grey[400],
              ),
              const SizedBox(height: 24),
              Text(
                'لا توجد مقالات مفضلة',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey[700],
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'قم بإضافة مقالات إلى المفضلة بالضغط على أيقونة القلب',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey[500],
                  fontFamily: 'Cairo',
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                },
                icon: const Icon(Icons.arrow_back),
                label: const Text(
                  'العودة للمقالات',
                  style: TextStyle(fontFamily: 'Cairo'),
                ),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadFavorites,
      child: ListView.builder(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: favoriteArticles.length + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            // Header with count
            return Container(
              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.favorite,
                    size: 16,
                    color: Colors.red[400],
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'عدد المقالات المفضلة: ${favoriteArticles.length}',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                      fontFamily: 'Cairo',
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Icon(
                    Icons.favorite,
                    size: 16,
                    color: Colors.red[400],
                  ),
                ],
              ),
            );
          }

          final article = favoriteArticles[index - 1];
          // المقال مؤكد أنه مُعجب به لأنه في المفضلة

          return ArticleCard(
            article: article,
            onLikeChanged: _onLikeChanged,
          );
        },
      ),
    );
  }
}
