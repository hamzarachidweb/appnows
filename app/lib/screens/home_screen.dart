import 'package:flutter/material.dart';
import '../models/article.dart';
import '../services/api_service.dart';
import '../services/like_service.dart';
import '../widgets/article_card.dart';
import 'settings_screen.dart';
import 'favorites_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Article> articles = [];
  bool isLoading = false;
  bool hasError = false;
  String errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadArticles();
  }

  Future<void> _loadArticles() async {
    setState(() {
      isLoading = true;
      hasError = false;
      errorMessage = '';
    });

    try {
      // Check API connectivity first
      final isReachable = await ApiService.isApiReachable();
      if (!isReachable) {
        throw Exception('لا يمكن الاتصال بالخادم. تأكد من:\n' +
            '• إمكانية الوصول لواجهة API\n' +
            '• عمل اتصال الشبكة');
      }

      final fetchedArticles = await ApiService.fetchArticles();

      // تحديث حالة الإعجاب لكل مقال
      await _updateLikeStates(fetchedArticles);

      if (mounted) {
        setState(() {
          articles = fetchedArticles;
          isLoading = false;
        });

        print('تم تحميل ${fetchedArticles.length} مقال بنجاح');
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          hasError = true;
          errorMessage = e.toString().replaceFirst('Exception: ', '');
          isLoading = false;
        });
      }
      print('خطأ في تحميل المقالات: $e');
    }
  }

  Future<void> _updateLikeStates(List<Article> articles) async {
    try {
      final likedIds = await LikeService.getLikedArticleIds();
      for (var article in articles) {
        article.isLiked = likedIds.contains(article.id);
      }
    } catch (e) {
      print('خطأ في تحديث حالة الإعجابات: $e');
    }
  }

  void _onLikeChanged() {
    // يمكن إضافة منطق إضافي هنا إذا لزم الأمر
    setState(() {});
  }

  Future<void> _refreshArticles() async {
    try {
      await _loadArticles();
      if (!hasError && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تم تحديث المقالات (${articles.length} مقال)',
                style: const TextStyle(fontFamily: 'Cairo')),
            backgroundColor: Colors.green,
            duration: const Duration(seconds: 2),
            action: SnackBarAction(
              label: 'إخفاء',
              textColor: Colors.white,
              onPressed: () {
                ScaffoldMessenger.of(context).hideCurrentSnackBar();
              },
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('فشل تحديث المقالات: ${e.toString()}',
                style: const TextStyle(fontFamily: 'Cairo')),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'أخبار الآن',
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
            onPressed: isLoading ? null : _refreshArticles,
            icon: isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.refresh),
            tooltip: 'تحديث المقالات',
          ),
          IconButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const FavoritesScreen(),
                ),
              );
            },
            icon: const Icon(Icons.favorite),
            tooltip: 'المفضلة',
          ),
          IconButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const SettingsScreen(),
                ),
              );
            },
            icon: const Icon(Icons.settings),
            tooltip: 'الإعدادات',
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
              'جاري تحميل المقالات...',
              style: TextStyle(
                fontSize: 16,
                fontFamily: 'Cairo',
              ),
            ),
          ],
        ),
      );
    }

    if (hasError) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.red[400],
              ),
              const SizedBox(height: 16),
              const Text(
                'خطأ في تحميل المقالات',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 8),
              Text(
                errorMessage,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _refreshArticles,
                icon: const Icon(Icons.refresh),
                label: const Text(
                  'إعادة المحاولة',
                  style: TextStyle(fontFamily: 'Cairo'),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (articles.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.article_outlined,
              size: 64,
              color: Colors.grey,
            ),
            SizedBox(height: 16),
            Text(
              'لا توجد مقالات متاحة',
              style: TextStyle(
                fontSize: 18,
                fontFamily: 'Cairo',
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refreshArticles,
      child: ListView.builder(
        itemCount: articles.length,
        itemBuilder: (context, index) {
          return ArticleCard(
            article: articles[index],
            onLikeChanged: _onLikeChanged,
          );
        },
      ),
    );
  }
}
