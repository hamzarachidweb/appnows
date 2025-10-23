<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$errors = [];
$success = false;

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Add new category
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Category name is required';
        } else {
            // Check if category already exists
            $existing = $db->fetchOne("SELECT id FROM categories WHERE name = ?", [$name]);
            if ($existing) {
                $errors[] = 'Category with this name already exists';
            }
        }
        
        if (empty($errors)) {
            try {
                $categoryData = [
                    'name' => $name,
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('categories', $categoryData);
                
                if (handleAjax()) {
                    sendJsonResponse(['status' => 'success', 'message' => 'Category added successfully']);
                } else {
                    setFlashMessage('success', 'Category added successfully');
                    header('Location: categories.php');
                    exit();
                }
            } catch (Exception $e) {
                if (handleAjax()) {
                    sendJsonResponse(['status' => 'error', 'message' => 'Failed to add category: ' . $e->getMessage()], 500);
                } else {
                    $errors[] = 'Failed to add category: ' . $e->getMessage();
                }
            }
        } else {
            if (handleAjax()) {
                sendJsonResponse(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
        }
    }
    
    if (isset($_POST['edit_category'])) {
        // Edit category
        $categoryId = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Category name is required';
        } else {
            // Check if category name already exists for another category
            $existing = $db->fetchOne("SELECT id FROM categories WHERE name = ? AND id != ?", [$name, $categoryId]);
            if ($existing) {
                $errors[] = 'Category with this name already exists';
            }
        }
        
        if (empty($errors)) {
            try {
                $categoryData = [
                    'name' => $name,
                    'description' => $description
                ];
                
                $db->update('categories', $categoryData, 'id = ?', [$categoryId]);
                
                if (handleAjax()) {
                    sendJsonResponse(['status' => 'success', 'message' => 'Category updated successfully']);
                } else {
                    setFlashMessage('success', 'Category updated successfully');
                    header('Location: categories.php');
                    exit();
                }
            } catch (Exception $e) {
                if (handleAjax()) {
                    sendJsonResponse(['status' => 'error', 'message' => 'Failed to update category: ' . $e->getMessage()], 500);
                } else {
                    $errors[] = 'Failed to update category: ' . $e->getMessage();
                }
            }
        } else {
            if (handleAjax()) {
                sendJsonResponse(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
        }
    }
    
    if (isset($_POST['delete_category'])) {
        // Delete category
        $categoryId = (int)$_POST['category_id'];
        
        try {
            // Check if category has articles
            $articleCount = $db->count('articles', 'category_id = ?', [$categoryId]);
            
            if ($articleCount > 0) {
                // Update articles to remove category reference
                $db->update('articles', ['category_id' => null], 'category_id = ?', [$categoryId]);
            }
            
            // Delete category
            $db->delete('categories', 'id = ?', [$categoryId]);
            
            if (handleAjax()) {
                sendJsonResponse(['status' => 'success', 'message' => 'Category deleted successfully']);
            } else {
                setFlashMessage('success', 'Category deleted successfully');
                header('Location: categories.php');
                exit();
            }
        } catch (Exception $e) {
            if (handleAjax()) {
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete category: ' . $e->getMessage()], 500);
            } else {
                $errors[] = 'Failed to delete category: ' . $e->getMessage();
            }
        }
    }
}

// Get single category for editing
if (isset($_GET['get_category']) && handleAjax()) {
    $categoryId = (int)$_GET['id'];
    $category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
    
    if ($category) {
        sendJsonResponse(['status' => 'success', 'category' => $category]);
    } else {
        sendJsonResponse(['status' => 'error', 'message' => 'Category not found'], 404);
    }
}

// Get all categories with article count
$categories = $db->fetchAll("
    SELECT c.*, COUNT(a.id) as article_count 
    FROM categories c 
    LEFT JOIN articles a ON c.id = a.category_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Blog Admin</title>
    
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
        
        .btn-action {
            margin: 2px;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .form-control {
            border: 2px solid #f1f1f1;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
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
        
        .category-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="articles.php">
                            <i class="fas fa-newspaper"></i> Articles
                        </a>
                        <a class="nav-link active" href="categories.php">
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
                        <span class="navbar-brand mb-0 h1">Manage Categories</span>
                        <div class="navbar-nav ms-auto">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="fas fa-plus"></i> Add New Category
                            </button>
                        </div>
                    </div>
                </nav>
                
                <div class="main-content">
                    <h1 class="page-title">
                        <i class="fas fa-tags"></i> Categories Management
                    </h1>
                    
                    <div class="content-card">
                        <div class="table-responsive">
                            <table id="categoriesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Articles</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <strong><?php echo sanitize($category['name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo $category['description'] ? truncateText(sanitize($category['description']), 80) : '<em class="text-muted">No description</em>'; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $category['article_count']; ?> articles
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo formatDate($category['created_at'], 'M d, Y'); ?><br>
                                                    <?php echo formatDate($category['created_at'], 'H:i'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-action edit-category" 
                                                        data-id="<?php echo $category['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger btn-action delete-category" 
                                                        data-id="<?php echo $category['id']; ?>"
                                                        data-name="<?php echo sanitize($category['name']); ?>"
                                                        data-articles="<?php echo $category['article_count']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($categories)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-tags fa-3x mb-3"></i>
                                <h5>No Categories Found</h5>
                                <p>Start by creating your first category.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                    <i class="fas fa-plus"></i> Add New Category
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">
                        <i class="fas fa-tag"></i> <span id="modalTitle">Add New Category</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" id="categoryId" name="category_id">
                        <input type="hidden" id="formAction" name="add_category" value="1">
                        
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">
                                <i class="fas fa-tag"></i> Category Name *
                            </label>
                            <input type="text" class="form-control" id="categoryName" name="name" 
                                   placeholder="Enter category name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">
                                <i class="fas fa-align-left"></i> Description
                            </label>
                            <textarea class="form-control" id="categoryDescription" name="description" 
                                      rows="3" placeholder="Enter category description (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> <span id="submitText">Add Category</span>
                        </button>
                    </div>
                </form>
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
            $('#categoriesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                language: {
                    search: "Search categories:",
                    lengthMenu: "Show _MENU_ categories per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ categories",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
            
            const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            
            // Reset modal when hidden
            $('#categoryModal').on('hidden.bs.modal', function() {
                resetForm();
            });
            
            // Handle category form submission
            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize() + '&ajax=1';
                const isEdit = $('#categoryId').val() !== '';
                
                // Show loading
                Swal.fire({
                    title: isEdit ? 'Updating Category...' : 'Adding Category...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.post('categories.php', formData)
                .done(function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            categoryModal.hide();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error!', 'Failed to process request. Please try again.', 'error');
                });
            });
            
            // Handle edit category
            $('.edit-category').on('click', function() {
                const categoryId = $(this).data('id');
                
                // Show loading
                Swal.fire({
                    title: 'Loading...',
                    text: 'Fetching category details.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.get('categories.php', {get_category: true, id: categoryId, ajax: true})
                .done(function(response) {
                    Swal.close();
                    
                    if (response.status === 'success') {
                        const category = response.category;
                        
                        // Set form to edit mode
                        $('#modalTitle').text('Edit Category');
                        $('#submitText').text('Update Category');
                        $('#categoryId').val(category.id);
                        $('#formAction').attr('name', 'edit_category');
                        $('#categoryName').val(category.name);
                        $('#categoryDescription').val(category.description);
                        
                        categoryModal.show();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.close();
                    Swal.fire('Error!', 'Failed to fetch category details.', 'error');
                });
            });
            
            // Handle category deletion
            $('.delete-category').on('click', function() {
                const categoryId = $(this).data('id');
                const categoryName = $(this).data('name');
                const articleCount = $(this).data('articles');
                
                let warningText = `You are about to delete "${categoryName}".`;
                if (articleCount > 0) {
                    warningText += ` This category has ${articleCount} article(s). The articles will be moved to "Uncategorized".`;
                }
                warningText += ' This action cannot be undone.';
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: warningText,
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
                            text: 'Please wait while we delete the category.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send AJAX request
                        $.post('categories.php', {
                            ajax: true,
                            delete_category: true,
                            category_id: categoryId
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
                            Swal.fire('Error!', 'Failed to delete category. Please try again.', 'error');
                        });
                    }
                });
            });
            
            function resetForm() {
                $('#modalTitle').text('Add New Category');
                $('#submitText').text('Add Category');
                $('#categoryId').val('');
                $('#formAction').attr('name', 'add_category');
                $('#categoryName').val('');
                $('#categoryDescription').val('');
            }
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