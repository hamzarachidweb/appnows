<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$articleId = (int)($_GET['id'] ?? 0);

if (!$articleId) {
    setFlashMessage('error', 'Invalid article ID');
    header('Location: articles.php');
    exit();
}

// Get article details
$article = $db->fetchOne("SELECT * FROM articles WHERE id = ?", [$articleId]);

if (!$article) {
    setFlashMessage('error', 'Article not found');
    header('Location: articles.php');
    exit();
}

// Get all categories for the dropdown
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Article title is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Article content is required';
    }
    
    if ($categoryId && !$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$categoryId])) {
        $errors[] = 'Invalid category selected';
    }
    
    // Handle image upload
    $imageName = $article['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $newImageName = uploadImage($_FILES['image']);
            
            // Delete old image if exists
            if ($article['image']) {
                deleteImage($article['image']);
            }
            
            $imageName = $newImageName;
        } catch (Exception $e) {
            $errors[] = 'Image upload failed: ' . $e->getMessage();
        }
    }
    
    // Update article if no errors
    if (empty($errors)) {
        try {
            $articleData = [
                'title' => $title,
                'content' => $content,
                'category_id' => $categoryId ?: null,
                'image' => $imageName
            ];
            
            $db->update('articles', $articleData, 'id = ?', [$articleId]);
            
            setFlashMessage('success', 'Article updated successfully!');
            header('Location: articles.php');
            exit();
        } catch (Exception $e) {
            $errors[] = 'Failed to update article: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - Blog Admin</title>
    
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
        
        .form-control, .form-select {
            border: 2px solid #f1f1f1;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-secondary {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .image-preview {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .image-preview:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        
        .image-preview.has-image {
            border-style: solid;
            border-color: #28a745;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }
        
        .current-image {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .content-editor {
            min-height: 300px;
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
                        <span class="navbar-brand mb-0 h1">Edit Article</span>
                        <div class="navbar-nav ms-auto">
                            <a href="articles.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Articles
                            </a>
                        </div>
                    </div>
                </nav>
                
                <div class="main-content">
                    <h1 class="page-title">
                        <i class="fas fa-edit"></i> Edit Article: <?php echo sanitize($article['title']); ?>
                    </h1>
                    
                    <div class="content-card">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo sanitize($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading"></i> Article Title *
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo sanitize($_POST['title'] ?? $article['title']); ?>" 
                                               placeholder="Enter article title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">
                                            <i class="fas fa-align-left"></i> Article Content *
                                        </label>
                                        <textarea class="form-control content-editor" id="content" name="content" 
                                                  placeholder="Write your article content here..." required><?php echo sanitize($_POST['content'] ?? $article['content']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">
                                            <i class="fas fa-tag"></i> Category
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) ? ($_POST['category_id'] == $category['id']) : ($article['category_id'] == $category['id'])) ? 'selected' : ''; ?>>
                                                    <?php echo sanitize($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-image"></i> Article Image
                                        </label>
                                        
                                        <?php if ($article['image']): ?>
                                            <div class="mb-3">
                                                <p class="text-muted mb-2">Current Image:</p>
                                                <img src="<?php echo UPLOAD_URL . $article['image']; ?>" 
                                                     alt="Current Article Image" class="current-image">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="image-preview" onclick="document.getElementById('image').click()">
                                            <div id="preview-content">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">
                                                    <?php echo $article['image'] ? 'Click to change image' : 'Click to upload image'; ?>
                                                </p>
                                                <small class="text-muted">Supported: JPG, PNG, GIF, WEBP (Max: 5MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="form-control d-none" id="image" name="image" 
                                               accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted">
                                            <i class="fas fa-info-circle"></i> Article Info
                                        </label>
                                        <div class="bg-light p-3 rounded">
                                            <small class="text-muted">
                                                <strong>Created:</strong> <?php echo formatDate($article['created_at'], 'M d, Y H:i'); ?><br>
                                                <strong>ID:</strong> #<?php echo $article['id']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Article
                                        </button>
                                        <a href="articles.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        // Image preview function
        function previewImage(input) {
            const previewContainer = document.getElementById('preview-content');
            const imagePreview = document.querySelector('.image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="preview-image">
                        <p class="text-success mt-2 mb-0">
                            <i class="fas fa-check-circle"></i> New image selected: ${input.files[0].name}
                        </p>
                        <small class="text-muted">Click to change image</small>
                    `;
                    imagePreview.classList.add('has-image');
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields (Title and Content).'
                });
                return false;
            }
            
            // Show loading
            Swal.fire({
                title: 'Updating Article...',
                text: 'Please wait while we update your article.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
        
        // Auto-resize textarea
        const textarea = document.getElementById('content');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(this.scrollHeight, 300) + 'px';
        });
        
        // Initial textarea height adjustment
        textarea.style.height = Math.max(textarea.scrollHeight, 300) + 'px';
        
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
    </script>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
}
?>