class Article {
  final int id;
  final String title;
  final String content;
  final String shortDescription;
  final String image;
  final Category category;
  final String createdAt;
  final String formattedDate;
  bool isLiked; // حالة الإعجاب

  Article({
    required this.id,
    required this.title,
    required this.content,
    required this.shortDescription,
    required this.image,
    required this.category,
    required this.createdAt,
    required this.formattedDate,
    this.isLiked = false,
  });

  factory Article.fromJson(Map<String, dynamic> json) {
    return Article(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      content: json['content'] ?? '',
      shortDescription: json['short_description'] ?? '',
      image: json['image'] ?? '',
      category: Category.fromJson(json['category'] ?? {}),
      createdAt: json['created_at'] ?? '',
      formattedDate: json['formatted_date'] ?? '',
      isLiked: json['is_liked'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'content': content,
      'short_description': shortDescription,
      'image': image,
      'category': category.toJson(),
      'created_at': createdAt,
      'formatted_date': formattedDate,
      'is_liked': isLiked,
    };
  }
}

class Category {
  final int id;
  final String name;

  Category({
    required this.id,
    required this.name,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
    };
  }
}

class ArticlesResponse {
  final String status;
  final int count;
  final List<Article> articles;

  ArticlesResponse({
    required this.status,
    required this.count,
    required this.articles,
  });

  factory ArticlesResponse.fromJson(Map<String, dynamic> json) {
    return ArticlesResponse(
      status: json['status'] ?? 'error',
      count: json['count'] ?? 0,
      articles: (json['articles'] as List<dynamic>?)
              ?.map((articleJson) => Article.fromJson(articleJson))
              .toList() ??
          [],
    );
  }
}
