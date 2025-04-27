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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <style>
    .navbar {
        background-color:bisque !important;
        color: white !important;
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .product-card {
        background-color: white;
        border: 1px solid var(--border-color, #eee);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-image-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #f2f2f2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-image {
        color: #aaa;
        font-size: 24px;
        text-align: center;
    }

    .product-info {
        padding: 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .product-info h3 {
        font-size: 18px;
        color: var(--primary-color, #333);
        margin: 0;
    }

    .product-info .price {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .product-info .category {
        font-size: 14px;
        color: #777;
    }

    .product-info .status {
        margin-top: 5px;
    }

    .action-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: auto;
    }

    .action-buttons a,
    .action-buttons button {
        background-color: var(--primary-color, #ff8800);
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.3s;
    }

    .action-buttons a:hover,
    .action-buttons button:hover {
        background-color: #d48806;
    }

    .delete-btn {
        background-color: var(--danger-color, #e74c3c);
    }

    .delete-btn:hover {
        background-color: #c0392b;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 13px;
        display: inline-block;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }
</style>
<div class="container mt-4">
<div class="products-grid" id="productGrid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image-container">
                    <?php if (!empty($product['image'])): ?>
                        <img src="../images/product/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    <?php else: ?>
                        <div class="no-image"><i class="fas fa-image"></i> No image</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    <p class="category"><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></p>
                    <p class="status">
                        <?php if ($product['available']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Available</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-times"></i> Not Available</span>
                        <?php endif; ?>
                    </p>
                    <div class="action-buttons">
                        <a href="addproduct.php?edit=<?= $product['id'] ?>" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="delete_product" class="btn btn-danger delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;">No products found. Add your first product above.</p>
    <?php endif; ?>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = Array.from(document.querySelectorAll('.product-card'));
    const productsPerPage = 6;
    const container = document.getElementById('productGrid');

    let currentPage = 1;
    let totalPages = Math.ceil(products.length / productsPerPage);

    function renderProducts() {
        container.innerHTML = '';

        const start = (currentPage - 1) * productsPerPage;
        const end = start + productsPerPage;
        const productsToShow = products.slice(start, end);

        productsToShow.forEach(product => container.appendChild(product));

        renderPagination();
    }

    function renderPagination() {
        let paginationContainer = document.getElementById('pagination');
        if (!paginationContainer) {
            paginationContainer = document.createElement('div');
            paginationContainer.id = 'pagination';
            paginationContainer.style.textAlign = 'center';
            paginationContainer.style.marginTop = '20px';
            container.parentNode.appendChild(paginationContainer);
        }

        paginationContainer.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.style.margin = '0 5px';
            pageBtn.style.padding = '8px 12px';
            pageBtn.style.border = 'none';
            pageBtn.style.backgroundColor = i === currentPage ? '#ff8800' : '#ccc';
            pageBtn.style.color = 'white';
            pageBtn.style.borderRadius = '6px';
            pageBtn.style.cursor = 'pointer';

            pageBtn.addEventListener('click', () => {
                currentPage = i;
                renderProducts();
            });

            paginationContainer.appendChild(pageBtn);
        }
    }

    renderProducts();
});
</script>

</body>
</html>
