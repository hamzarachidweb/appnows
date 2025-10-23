<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

// Handle article deletion
if (isset($_POST['delete_article'])) {
    $articleId = (int)$_POST['article_id'];
    
    try {
        // Get article details to delete image
        $article = $db->fetchOne("SELECT image FROM articles WHERE id = ?", [$articleId]);
        
        // Delete article
        $db->delete('articles', 'id = ?', [$articleId]);
        
        // Delete image file if exists
        if ($article && $article['image']) {
            deleteImage($article['image']);
        }
        
        if (handleAjax()) {
            sendJsonResponse(['status' => 'success', 'message' => 'Article deleted successfully']);
        } else {
            setFlashMessage('success', 'Article deleted successfully');
            header('Location: articles.php');
            exit();
        }
    } catch (Exception $e) {
        if (handleAjax()) {
            sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete article: ' . $e->getMessage()], 500);
        } else {
            setFlashMessage('error', 'Failed to delete article: ' . $e->getMessage());
        }
    }
}

// Get all articles with category information
$articles = $db->fetchAll("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    ORDER BY a.created_at DESC
");

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles - Blog Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
        
        .article-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .badge-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-action {
            margin: 2px;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="articles.php">
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
                        <span class="navbar-brand mb-0 h1">Manage Articles</span>
                        <div class="navbar-nav ms-auto">
                            <a href="add_article.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Article
                            </a>
                        </div>
                    </div>
                </nav>
                
                <div class="main-content">
                    <h1 class="page-title">
                        <i class="fas fa-newspaper"></i> Articles Management
                    </h1>
                    
                    <div class="content-card">
                        <div class="table-responsive">
                            <table id="articlesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td><?php echo $article['id']; ?></td>
                                            <td>
                                                <?php if ($article['image']): ?>
                                                    <img src="<?php echo UPLOAD_URL . $article['image']; ?>" 
                                                         alt="Article Image" class="article-image">
                                                <?php else: ?>
                                                    <div class="article-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo sanitize($article['title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo truncateText(strip_tags($article['content']), 60); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-category">
                                                    <?php echo sanitize($article['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo formatDate($article['created_at'], 'M d, Y'); ?><br>
                                                    <?php echo formatDate($article['created_at'], 'H:i'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="edit_article.php?id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary btn-action">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger btn-action delete-article" 
                                                        data-id="<?php echo $article['id']; ?>"
                                                        data-title="<?php echo sanitize($article['title']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($articles)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-newspaper fa-3x mb-3"></i>
                                <h5>No Articles Found</h5>
                                <p>Start by creating your first article.</p>
                                <a href="add_article.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Article
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#articlesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                language: {
                    search: "Search articles:",
                    lengthMenu: "Show _MENU_ articles per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ articles",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
            
            // Handle article deletion
            $('.delete-article').on('click', function() {
                const articleId = $(this).data('id');
                const articleTitle = $(this).data('title');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${articleTitle}". This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the article.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send AJAX request
                        $.post('articles.php', {
                            ajax: true,
                            delete_article: true,
                            article_id: articleId
                        })
                        .done(function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error!', 'Failed to delete article. Please try again.', 'error');
                        });
                    }
                });
            });
        });
        
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