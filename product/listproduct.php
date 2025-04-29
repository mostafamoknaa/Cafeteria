<?php
// listproduct.php

require "../connect.php";
session_start();

// Ensure only admins access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
    header('Location: ../shared/login.php');
    exit();
}

// Fetch all products with their categories
$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle product deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product"])) {
    $delete_id = intval($_POST["delete_id"]);

    // Delete associated image
    $query = "SELECT image FROM products WHERE id = $delete_id";
    $img_result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($img_result);

    if ($product && !empty($product['image'])) {
        $imagePath = "../images/product/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product from database
    $delete_query = "DELETE FROM products WHERE id = $delete_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION["success_message"] = "Product deleted successfully.";
    } else {
        $_SESSION["error_message"] = "Failed to delete product.";
    }

    header("Location: listproduct.php");
    exit();
}
?>

<?php include "../shared/navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">

    <style>
        /* --- Styles (CSS) --- */
        :root {
            --primary-color: bisque;
            --secondary-color: #2ecc71;
            --danger-color:rgb(194, 26, 7);
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-color: #ddd;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--light-bg); padding: 20px; color: var(--text-color); }
        .container { max-width: 1200px; margin: auto; padding: 15px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
        h1, h2 { color: burlywood; margin-bottom: 20px; }
        .btn {background-color:antiquewhite; padding: 10px 20px; border: none; border-radius: 4px; color: black; cursor: pointer; text-decoration: none; transition: background-color 0.3s; }
        .btn:hover {color: white; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { border: 1px solid var(--border-color); padding: 12px; text-align: left; }
        th { background-color: var(--primary-color); }
        tr:nth-child(even) { background: #f2f2f2; }
        .product-image { max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 4px; }
        .action-buttons { display: flex; gap: 8px; }
        .action-buttons a { color: orange; text-decoration: none; padding: 5px; }
        .action-buttons a.delete { color: var(--danger-color); }
        .badge { padding: 4px 8px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .pagination { display: flex; justify-content: center; gap: 10px; }
        .pagination a { padding: 8px 12px; border: 1px solid var(--primary-color); border-radius: 4px; color: black; text-decoration: none; }
        .pagination a.active { background-color: black; color: white; }
        @media (max-width: 768px) {
            .action-buttons { flex-direction: column; }
            .table-responsive { overflow-x: auto; }
        }
    </style>
</head>

<body>

<div class="container">
    <header>
        <h1><i class="fas fa-box"></i> Product Management</h1>
        <a href="addproduct.php" class="btn btn-info">Add Product</a>
    </header>

    <div style="margin-bottom: 20px;">
        <input type="text" id="searchInput" placeholder="Search products..." style="padding:10px; width:20%; border:1px solid var(--border-color); border-radius:4px;">
    </div>

    <h2><i class="fas fa-list"></i> Product List</h2>

    <div class="table-responsive">
        <table id="productTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($product['image']): ?>
                                    <img src="../images/product/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                <?php else: ?>
                                    <div class="no-image"><i class="fas fa-image"></i> No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td>
                                <?php if ($product['available']): ?>
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Available</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Not Available</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td class="action-buttons">
                                <a href="addproduct.php?edit=<?= $product['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                                <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmDeleteModal" 
                                        data-delete-id="<?= $product['id'] ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">No products found. Add your first product above.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div id="pagination" class="pagination"></div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this product?
                    <input type="hidden" name="delete_id" id="modalDeleteId">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_product" class="btn btn-danger">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    confirmDeleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const deleteId = button.getAttribute('data-delete-id');
        document.getElementById('modalDeleteId').value = deleteId;
    });

    const table = document.getElementById('productTable');
    const rows = Array.from(table.getElementsByTagName('tbody')[0].rows);
    const pagination = document.getElementById('pagination');
    const rowsPerPage = 3;
    let filteredRows = [...rows];
    const searchInput = document.getElementById('searchInput');

    function displayPage(page, rowsToShow) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(row => row.style.display = 'none');
        rowsToShow.slice(start, end).forEach(row => row.style.display = '');

        pagination.innerHTML = '';
        const pageCount = Math.ceil(rowsToShow.length / rowsPerPage);

        for (let i = 1; i <= pageCount; i++) {
            const link = document.createElement('a');
            link.textContent = i;
            link.href = "#";
            link.className = i === page ? 'active' : '';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                displayPage(i, rowsToShow);
            });
            pagination.appendChild(link);
        }
    }

    function filterRows() {
        const searchTerm = searchInput.value.toLowerCase();
        filteredRows = rows.filter(row => {
            const cells = row.getElementsByTagName('td');
            return Array.from(cells).some(cell => cell.innerText.toLowerCase().includes(searchTerm));
        });
        displayPage(1, filteredRows);
    }

    searchInput.addEventListener('input', filterRows);
    displayPage(1, filteredRows);
</script>

</body>
</html>
