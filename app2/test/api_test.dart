import 'package:flutter_test/flutter_test.dart';
import 'package:my_app/models/article.dart';
import 'package:my_app/services/api_service.dart';

void main() {
  group('API Service Tests', () {
    test('Should fetch articles successfully', () async {
      try {
        final articles = await ApiService.fetchArticles();

        expect(articles, isNotEmpty);
        expect(articles.length, greaterThan(0));

        // Test first article structure
        final firstArticle = articles.first;
        expect(firstArticle.id, isNotNull);
        expect(firstArticle.title, isNotEmpty);
        expect(firstArticle.content, isNotEmpty);
        expect(firstArticle.shortDescription, isNotEmpty);
        expect(firstArticle.image, isNotEmpty);
        expect(firstArticle.category.id, isNotNull);
        expect(firstArticle.category.name, isNotEmpty);
        expect(firstArticle.createdAt, isNotEmpty);
        expect(firstArticle.formattedDate, isNotEmpty);

        print('✅ Successfully fetched ${articles.length} articles');
        for (var article in articles) {
          print('- Article ${article.id}: ${article.title}');
          print('  Category: ${article.category.name}');
          print('  Date: ${article.formattedDate}');
          print('  Image: ${article.image}');
          print('');
        }
      } catch (e) {
        fail('Failed to fetch articles: $e');
      }
    });

    test('Should check API connectivity', () async {
      final isReachable = await ApiService.isApiReachable();
      expect(isReachable, isTrue);
      print('✅ API is reachable');
    });

    test('Should get articles count', () async {
      final count = await ApiService.getArticlesCount();
      expect(count, greaterThan(0));
      print('✅ Articles count: $count');
    });
  });

  group('Article Model Tests', () {
    test('Should parse article from JSON correctly', () {
      final jsonData = {
        "id": 7,
        "title": "كرة القدم: اللعبة التي توحّد العالم",
        "content": "تُعدّ كرة القدم أكثر من مجرد لعبة...",
        "short_description":
            "تُعدّ كرة القدم أكثر من مجرد لعبة، فهي شغف يجمع الملايين...",
        "image":
            "http://localhost/nows/admin/uploads/68f4e5e36a822_1760880099.png",
        "category": {"id": 3, "name": "Programming"},
        "created_at": "2025-10-19 15:21:39",
        "formatted_date": "Oct 19, 2025"
      };

      final article = Article.fromJson(jsonData);

      expect(article.id, equals(7));
      expect(article.title, equals("كرة القدم: اللعبة التي توحّد العالم"));
      expect(article.category.id, equals(3));
      expect(article.category.name, equals("Programming"));
      expect(article.formattedDate, equals("Oct 19, 2025"));

      print('✅ Article model parsing works correctly');
    });

    test('Should parse articles response from JSON correctly', () {
      final jsonData = {
        "status": "success",
        "count": 2,
        "articles": [
          {
            "id": 7,
            "title": "Test Article",
            "content": "Test content",
            "short_description": "Test description",
            "image": "http://localhost/test.jpg",
            "category": {"id": 3, "name": "Programming"},
            "created_at": "2025-10-19 15:21:39",
            "formatted_date": "Oct 19, 2025"
          }
        ]
      };

      final response = ArticlesResponse.fromJson(jsonData);

      expect(response.status, equals("success"));
      expect(response.count, equals(2));
      expect(response.articles.length, equals(1));
      expect(response.articles.first.title, equals("Test Article"));

      print('✅ Articles response model parsing works correctly');
    });
  });
}
