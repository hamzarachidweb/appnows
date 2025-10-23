<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    // تحقق من الاتصال بقاعدة البيانات
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }

    // جلب جميع المقالات مع اسم التصنيف
    $sql = "
        SELECT 
            a.id,
            a.title,
            a.content,
            a.image,
            a.created_at,
            c.id as category_id,
            c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
    ";

    $articles = $db->fetchAll($sql);

    // تجهيز البيانات للإرسال
    $formatted = [];
    foreach ($articles as $a) {
        $formatted[] = [
            'id' => (int)$a['id'],
            'title' => $a['title'],
            'content' => $a['content'],
            'short_description' => mb_substr(strip_tags($a['content']), 0, 100) . '...',
            'image' => $a['image'] ? SITE_URL . UPLOAD_URL . $a['image'] : null,
            'category' => [
                'id' => $a['category_id'] ? (int)$a['category_id'] : null,
                'name' => $a['category_name'] ?? 'Uncategorized'
            ],
            'created_at' => $a['created_at'],
            'formatted_date' => date('M d, Y', strtotime($a['created_at']))
        ];
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($formatted),
        'articles' => $formatted
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch articles',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
