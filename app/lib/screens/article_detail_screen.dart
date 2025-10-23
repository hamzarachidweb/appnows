import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/article.dart';
import '../services/like_service.dart';

class ArticleDetailScreen extends StatefulWidget {
  final Article article;

  const ArticleDetailScreen({
    super.key,
    required this.article,
  });

  @override
  State<ArticleDetailScreen> createState() => _ArticleDetailScreenState();
}

class _ArticleDetailScreenState extends State<ArticleDetailScreen> {
  bool _isLiking = false;

  @override
  void initState() {
    super.initState();
    _loadLikeStatus();
  }

  Future<void> _loadLikeStatus() async {
    try {
      final isLiked = await LikeService.isLiked(widget.article.id);
      if (mounted) {
        setState(() {
          widget.article.isLiked = isLiked;
        });
      }
    } catch (e) {
      print('خطأ في تحميل حالة الإعجاب: $e');
    }
  }

  Future<void> _toggleLike() async {
    if (_isLiking) return;

    setState(() {
      _isLiking = true;
    });

    try {
      final success = await LikeService.toggleLikeWithData(widget.article);
      if (success) {
        setState(() {
          widget.article.isLiked = !widget.article.isLiked;
        });

        // إظهار رسالة تأكيد
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                widget.article.isLiked
                    ? 'تمت إضافة المقال للمفضلة'
                    : 'تم إزالة المقال من المفضلة',
                style: const TextStyle(fontFamily: 'Cairo'),
              ),
              duration: const Duration(seconds: 2),
              backgroundColor:
                  widget.article.isLiked ? Colors.green : Colors.orange,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'حدث خطأ في تحديث الإعجاب',
              style: TextStyle(fontFamily: 'Cairo'),
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLiking = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'تفاصيل المقال',
          style: TextStyle(
            fontFamily: 'Cairo',
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF0D47A1), // اللون الأزرق
        elevation: 2,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          // زر الإعجاب في شريط التطبيق
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8.0),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                borderRadius: BorderRadius.circular(20),
                onTap: _isLiking ? null : _toggleLike,
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _isLiking
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : Icon(
                              widget.article.isLiked
                                  ? Icons.favorite
                                  : Icons.favorite_border,
                              size: 24,
                              color: widget.article.isLiked ? Colors.red : null,
                            ),
                      const SizedBox(width: 4),
                      Text(
                        widget.article.isLiked ? 'مُعجب' : 'إعجاب',
                        style: TextStyle(
                          fontSize: 12,
                          color: widget.article.isLiked ? Colors.red : null,
                          fontFamily: 'Cairo',
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // صورة المقال
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: CachedNetworkImage(
                imageUrl: widget.article.image,
                height: 200,
                width: double.infinity,
                fit: BoxFit.cover,
                placeholder: (context, url) => Container(
                  height: 200,
                  color: Colors.grey[300],
                  child: const Center(
                    child: CircularProgressIndicator(),
                  ),
                ),
                errorWidget: (context, url, error) => Container(
                  height: 200,
                  color: Colors.grey[300],
                  child: const Center(
                    child: Icon(
                      Icons.image_not_supported,
                      size: 50,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 16),

            // عنوان المقال
            Text(
              widget.article.title,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                height: 1.3,
                fontFamily: 'Cairo',
              ),
              textAlign: TextAlign.right,
            ),

            const SizedBox(height: 16),

            // تفاصيل المقال (التاريخ والفئة)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.secondary,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    widget.article.category.name,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                      fontFamily: 'Cairo',
                    ),
                  ),
                ),
                Text(
                  widget.article.formattedDate,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                    fontFamily: 'Cairo',
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // محتوى المقال
            Text(
              widget.article.content,
              style: const TextStyle(
                fontSize: 16,
                height: 1.6,
                fontFamily: 'Cairo',
              ),
              textAlign: TextAlign.right,
            ),
          ],
        ),
      ),
    );
  }
}
