<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe Ordering System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .navbar { background-color: bisque !important; }
    .menu-item { transition: transform 0.2s; cursor: pointer; }
    .menu-item:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .product-icon { height: 80px; display: flex; align-items: center; justify-content: center; }
    .btn-confirm { background-color: bisque; color: white; }
    .btn-confirm:hover { background-color: #e3b98c; }
    .order-item { background-color: #f5f5f5; border-radius: 5px; margin-bottom: 10px; }
    .pagination { justify-content: center; margin-top: 20px; }
    .search-box { margin-bottom: 20px; }
    .btn-bisque {
    background-color: bisque;
    border-color: bisque;
    color: black;
  }
  .btn-bisque:hover {
    background-color: #e3b98c;
    border-color: #e3b98c;
    color: black;
  }
  </style>
</head>
<body>

<?php
require_once('../connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../shared/login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!isset($_SESSION['order_items'])) {
  $_SESSION['order_items'] = [];
  $_SESSION['order_notes'] = '';
  $_SESSION['order_room'] = 'Room 101';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $post = $_POST;
  if (isset($post['add_item'])) {
    $product_id = (int)$post['product_id'];
    $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
    if ($product) {
      $found = false;
      foreach ($_SESSION['order_items'] as &$item) {
        if ($item['id'] == $product_id) {
          $item['quantity']++;
          $found = true;
          break;
        }
      }
      if (!$found) {
        $_SESSION['order_items'][] = ['id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'], 'quantity' => 1];
      }
    }
  } elseif (isset($post['increase_qty']) || isset($post['remove_item'])) {
    $id = (int)$post['item_id'];
    foreach ($_SESSION['order_items'] as $index => &$item) {
      if ($item['id'] == $id) {
        if (isset($post['increase_qty'])) {
          $item['quantity']++;
        } else {
          unset($_SESSION['order_items'][$index]);
        }
        break;
      }
    }
    $_SESSION['order_items'] = array_values($_SESSION['order_items']);
  } elseif (isset($post['update_notes'])) {
    $_SESSION['order_notes'] = $post['notes'];
  } elseif (isset($post['update_room'])) {
    $_SESSION['order_room'] = $post['room'];
  } elseif (isset($post['confirm_order']) && !empty($_SESSION['order_items'])) {
    $conn->begin_transaction();
    try {
      $date = date('Y-m-d H:i:s');
      $total = array_reduce($_SESSION['order_items'], fn($sum, $item) => $sum + $item['price'] * $item['quantity'], 0);
      $conn->query("INSERT INTO orders (user_id, created_at, notes, status, total) VALUES ('$user_id', '$date', '{$conn->real_escape_string($_SESSION['order_notes'])}', 'Processing', '$total')");
      $order_id = $conn->insert_id;
      foreach ($_SESSION['order_items'] as $item) {
        $conn->query("INSERT INTO order_products (order_id, product_id, quantity, price) VALUES ('$order_id', '{$item['id']}', '{$item['quantity']}', '{$item['price']}')");
      }
      $conn->commit();
      $_SESSION['order_items'] = [];
      $_SESSION['order_notes'] = '';
      $_SESSION['order_room'] = 'Room 101';
      $_SESSION['order_success'] = "Order #$order_id confirmed.";
    } catch (Exception $e) {
      $conn->rollback();
      $_SESSION['order_error'] = 'Order failed: ' . $e->getMessage();
    }
  }
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}

$products = $conn->query("SELECT * FROM products WHERE available = 1")->fetch_all(MYSQLI_ASSOC);
$total = array_reduce($_SESSION['order_items'], fn($sum, $item) => $sum + $item['price'] * $item['quantity'], 0);
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$query = "SELECT products.*, categories.name AS category_name 
          FROM products 
          JOIN categories ON products.category_id = categories.id 
          WHERE 1";

if (!empty($search)) {
  $search_safe = $conn->real_escape_string($search);
  $query .= " AND products.name LIKE '%$search_safe%'";
}

if ($category_id > 0) {
  $query .= " AND products.category_id = $category_id";
}

$query .= " ORDER BY products.id DESC";

$all_products = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
$total_products = count($all_products);

$products_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); 
$offset = ($page - 1) * $products_per_page;

$total_pages = ceil($total_products / $products_per_page);
$products = array_slice($all_products, $offset, $products_per_page);

$categories = $conn->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand" href="#">Cafeteria</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
      </ul>
    </div>
    <div class="dropdown">
      <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?php if (!empty($user['picture'])): ?>
          <img src="../images/<?= htmlspecialchars($user['picture']) ?>" width="30" height="30" class="rounded-circle me-2">
        <?php else: ?>
          <i class="fas fa-user-circle me-2 fs-5"></i>
        <?php endif; ?>
        <span><?= htmlspecialchars($user['name']) ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item text-danger" href="../shared/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<?php if (isset($_SESSION['order_success'])): ?>
<div class="alert alert-success container mt-3"><?= $_SESSION['order_success'] ?></div>
<?php unset($_SESSION['order_success']); endif; ?>

<?php if (isset($_SESSION['order_error'])): ?>
<div class="alert alert-danger container mt-3"><?= $_SESSION['order_error'] ?></div>
<?php unset($_SESSION['order_error']); endif; ?>

<div class="container mt-4">
  <div class="row">
    <!-- Current Order -->
    <div class="col-md-4 mb-4">
      <div class="card">
        <div class="card-header">Current Order</div>
        <div class="card-body">
          <?php foreach ($_SESSION['order_items'] as $item): ?>
            <div class="order-item p-2 mb-2 d-flex justify-content-between">
              <div>
                <strong><?= htmlspecialchars($item['name']) ?></strong>
                <div>EGP <?= number_format($item['price'], 2) ?></div>
              </div>
              <div class="d-flex align-items-center">
                <span class="mx-2">x<?= $item['quantity'] ?></span>
                <form method="post"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button name="increase_qty" class="btn btn-sm btn-dark"><i class="fas fa-plus"></i></button></form>
                <form method="post" class="ms-2"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button name="remove_item" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
              </div>
            </div>
          <?php endforeach; ?>
          <form method="post" class="mt-3">
            <textarea class="form-control mb-2" name="notes" placeholder="Add notes..."><?= htmlspecialchars($_SESSION['order_notes']) ?></textarea>
            <button name="update_notes" class="btn btn-sm btn-bisque w-100">Update Notes</button>
          </form>
          <form method="post" class="mt-3">
            <select name="room" class="form-select mb-2">
              <?php foreach (["Room 101", "Room 102", "Room 103"] as $room): ?>
                <option value="<?= $room ?>" <?= $_SESSION['order_room'] == $room ? 'selected' : '' ?>><?= $room ?></option>
              <?php endforeach; ?>
            </select>
            <button name="update_room" class="btn btn-sm btn-bisque w-100">Update Room</button>
          </form>
          <div class="mt-3 text-end">
            <h5>Total: EGP <?= number_format($total, 2) ?></h5>
          </div>
          <form method="post">
            <button name="confirm_order" class="btn btn-confirm w-100 mt-2" <?= empty($_SESSION['order_items']) ? 'disabled' : '' ?>>
              <i class="fas fa-check"></i> Confirm Order
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="col-md-8">
      <h4>Menu</h4>
      <form method="get" class="row g-3 mb-4">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-4">
            <select name="category_id" class="form-select">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <div class="row">
              <div class="col-md-6 mb-2 mb-md-0">
                <button type="submit" class="btn btn-bisque w-100">Filter</button>
              </div>
              <div class="col-md-6">
                <button type="button" class="btn btn-bisque w-100" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">Reset</button>
              </div>
            </div>
          </div>

        </form>
      <div class="row row-cols-1 row-cols-md-3 g-4" id="productsContainer">
        <?php foreach ($products as $product): ?>
        <div class="col product-card">
          <form method="post">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <div class="card menu-item">
              <div class="product-icon">
                <img src="../images/product/<?= htmlspecialchars($product['image']) ?>" width="50" height="50">
              </div>
              <div class="card-body text-center">
                <h6 class="product-name"><?= htmlspecialchars($product['name']) ?></h6>
                <p class="text-muted">EGP <?= number_format($product['price'], 2) ?></p>
                <button name="add_item" class="btn btn-sm btn-bisque">
                  <i class="fas fa-shopping-cart"></i> Add to Order
                </button>
              </div>
            </div>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 d-flex justify-content-center">
        <nav>
          <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item  <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pagination and search
const itemsPerPage = 6;
let currentPage = 1;

function filterProducts() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const cards = document.querySelectorAll('.product-card');
  cards.forEach(card => {
    const name = card.querySelector('.product-name').textContent.toLowerCase();
    card.dataset.visible = name.includes(search) ? 'true' : 'false';
  });
  currentPage = 1;
  paginateProducts();
}

function paginateProducts() {
  const cards = document.querySelectorAll('.product-card');
  const visibleCards = Array.from(cards).filter(c => c.dataset.visible === 'true');
  const totalPages = Math.ceil(visibleCards.length / itemsPerPage);

  const pagination = document.getElementById('pagination');
  pagination.innerHTML = '';

  if (totalPages > 1) {
    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');
      li.className = `page-item ${i === currentPage ? 'active' : ''}`;
      li.innerHTML = `<button class="page-link" type="button">${i}</button>`;
      li.addEventListener('click', () => {
        currentPage = i;
        showPage();
      });
      pagination.appendChild(li);
    }
  }
  showPage();
}

function showPage() {
  const cards = document.querySelectorAll('.product-card');
  const visibleCards = Array.from(cards).filter(c => c.dataset.visible === 'true');
  visibleCards.forEach((card, idx) => {
    card.style.display = (idx >= (currentPage - 1) * itemsPerPage && idx < currentPage * itemsPerPage) ? '' : 'none';
  });
}

document.getElementById('searchInput').addEventListener('input', filterProducts);

window.onload = () => {
  const cards = document.querySelectorAll('.product-card');
  cards.forEach(card => card.dataset.visible = 'true');
  filterProducts();
};
</script>


</body>
</html>