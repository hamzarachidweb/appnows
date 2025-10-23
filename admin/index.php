<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

// Get dashboard statistics
$totalArticles = $db->count('articles');
$totalCategories = $db->count('categories');

// Get recent articles
$recentArticles = $db->fetchAll("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Admin Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #212529 0%, #343a40 100%);
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stats-card.articles {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card.categories {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .recent-articles {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .article-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
        }
        
        .article-item:hover {
            background-color: #f8f9fa;
        }
        
        .article-item:last-child {
            border-bottom: none;
        }
        
        .navbar {
            background: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-blog"></i> Blog Admin
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="articles.php">
                            <i class="fas fa-newspaper"></i> Articles
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                        <a class="nav-link" href="add_article.php">
                            <i class="fas fa-plus"></i> Add Article
                        </a>
                        <hr class="my-3" style="border-color: #495057;">
                        <a class="nav-link" href="?logout=1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Dashboard</span>
                        <div class="navbar-nav ms-auto">
                            <span class="navbar-text">
                                Welcome, <?php echo sanitize($_SESSION['admin_username']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <div class="main-content">
                    <h1 class="page-title">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Overview
                    </h1>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stats-card articles">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3><?php echo $totalArticles; ?></h3>
                                        <p class="mb-0">Total Articles</p>
                                    </div>
                                    <i class="fas fa-newspaper fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card categories">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3><?php echo $totalCategories; ?></h3>
                                        <p class="mb-0">Total Categories</p>
                                    </div>
                                    <i class="fas fa-tags fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Articles -->
                    <div class="recent-articles">
                        <h4 class="mb-4">
                            <i class="fas fa-clock"></i> Recent Articles
                        </h4>
                        <?php if (empty($recentArticles)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-newspaper fa-3x mb-3"></i>
                                <p>No articles found. <a href="add_article.php">Create your first article</a></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentArticles as $article): ?>
                                <div class="article-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1"><?php echo sanitize($article['title']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-tag"></i> <?php echo sanitize($article['category_name'] ?? 'Uncategorized'); ?>
                                                &nbsp;|&nbsp;
                                                <i class="fas fa-calendar"></i> <?php echo formatDate($article['created_at'], 'M d, Y'); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="articles.php" class="btn btn-primary">
                                    <i class="fas fa-list"></i> View All Articles
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Handle logout
        document.querySelector('a[href="?logout=1"]').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will be logged out of the admin panel.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?logout=1';
                }
            });
        });
        
        // Show flash message if exists
        <?php if ($flashMessage): ?>
            Swal.fire({
                icon: '<?php echo $flashMessage['type']; ?>',
                title: '<?php echo ucfirst($flashMessage['type']); ?>',
                text: '<?php echo sanitize($flashMessage['message']); ?>',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
}
?>