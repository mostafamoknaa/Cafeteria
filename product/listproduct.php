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
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
    :root {
        --primary-color: #D8AC9F;
        --primary-hover: #C28D7A;
        --text-color: #5d4037;
        --light-bg: #f8f9fa;
        --border-color: #D8AC9F;
        --danger-color: #e74c3c;
        --success-color: #27ae60;
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
        color: var(--text-color);
        margin-bottom: 20px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: var(--primary-color);
        color: var(--text-color);
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s;
        font-weight: 500;
    }

    .btn:hover {
        background-color: var(--primary-hover);
        color: white;
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
        border: 1px solid var(--border-color);
    }

    th, td {
        border: 1px solid var(--border-color);
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: var(--primary-color);
        color: var(--text-color);
        font-weight: 600;
    }

    tr:nth-child(even) {
        background-color: rgba(216, 172, 159, 0.1);
    }

    tr:hover {
        background-color: rgba(216, 172, 159, 0.2);
    }

    .product-image {
        max-width: 80px;
        max-height: 80px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid var(--border-color);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.3s;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .edit-btn {
        background-color: var(--primary-color);
        color: var(--text-color);
    }

    .edit-btn:hover {
        background-color: var(--primary-hover);
        color: white;
    }

    .delete-btn {
        background-color: #f8d7da;
        color: var(--danger-color);
    }

    .delete-btn:hover {
        background-color: var(--danger-color);
        color: white;
    }

    .action-btn i {
        margin-right: 5px;
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
        margin-top: 20px;
    }

    .pagination a {
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        color: var(--text-color);
        text-decoration: none;
        transition: all 0.3s;
    }

    .pagination a:hover {
        background-color: var(--primary-hover);
        color: white;
    }

    .pagination a.active {
        background-color: var(--primary-color);
        color: white;
    }

    #searchInput {
        width: 300px;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        margin-bottom: 20px;
    }

    #searchInput:focus {
        outline: none;
        border-color: var(--primary-hover);
        box-shadow: 0 0 0 0.2rem rgba(216, 172, 159, 0.25);
    }

    .no-image {
        color: #8d6e63;
        font-size: 14px;
    }

    .action-buttons {
    border: none;
}


    @media (max-width: 768px) {
        header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        #searchInput {
            width: 100%;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 5px;
        }
        
        th, td {
            padding: 8px;
            font-size: 14px;
        }
        
        .product-image {
            max-width: 50px;
            max-height: 50px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <header>
        <h1><i class="fas fa-box"></i> Product Management</h1>
        <a href="addproduct.php" class="btn"><i class="fas fa-plus"></i> Add Product</a>
    </header>
    
    <div>
        <input type="text" id="searchInput" placeholder="Search products...">
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
                            <a href="addproduct.php?edit=<?= $product['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="delete_product" class="action-btn delete-btn">
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
    const rowsPerPage = 5;
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
            for (let cell of cells) {
                if (cell.innerText.toLowerCase().includes(searchTerm)) {
                    return true;
                }
            }
            return false;
        });
        displayPage(1, filteredRows);
    }

    searchInput.addEventListener('input', filterRows);
    displayPage(1, filteredRows);
</script>

</body>
</html>