<?php
require "../connect.php";
session_start();

$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product"])) {
    $delete_id = intval($_POST["delete_id"]);
    
    $query = "SELECT image FROM products WHERE id = $delete_id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
    if ($product && !empty($product['image'])) {
        $imagePath = "../images/product/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-color: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-bg);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid var(--border-color);
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .product-image {
            max-width: 80px;
            max-height: 80px;
            border-radius: 4px;
            object-fit: cover;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons a {
            color: var(--primary-color);
            text-decoration: none;
            padding: 5px;
            border-radius: 4px;
        }

        .action-buttons a:hover {
            background-color: #f1f1f1;
        }

        .action-buttons a.delete {
            color: var(--danger-color);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            color: var(--primary-color);
            text-decoration: none;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons a {
                margin-bottom: 5px;
            }

            th, td {
                padding: 8px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><i class="fas fa-box"></i> Product Management</h1>
        <a href="addproduct.php" class="btn">Add Product</a>
    </header>
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
                                <div class="no-image"><i class="fas fa-image"></i> No image</div>
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
                        <td><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></td>
                        <td class="action-buttons">
                            <a href="addproduct.php?edit=<?= $product['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="delete_product" class="btn" style="background-color: var(--danger-color); color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">No products found. Add your first product above.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>
<script>
    const table = document.getElementById('productTable');
    const rows = Array.from(table.getElementsByTagName('tbody')[0].rows);
    const pagination = document.getElementById('pagination');
    const rowsPerPage = 4;
    const pageCount = Math.ceil(rows.length / rowsPerPage);

    function displayPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? '' : 'none';
        });

        pagination.innerHTML = '';
        for (let i = 1; i <= pageCount; i++) {
            const link = document.createElement('a');
            link.textContent = i;
            link.href = "#";
            link.className = i === page ? 'active' : '';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                displayPage(i);
            });
            pagination.appendChild(link);
        }
    }

    displayPage(1);
</script>
</body>
</html>
