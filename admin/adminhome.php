<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe Ordering System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-kQtW33rZJAHjgefvhyyzcGFETqB7g/0Qfdf5xUL6VwKZV8Hj1/igZ0SovJUc1Y6z" crossorigin="anonymous"></script>
  <style>
    body { background-color: #f8f9fa; }
    .navbar { background-color: bisque !important;
      color: white !important;
     }
    
    .menu-item { transition: transform 0.2s; cursor: pointer; }
    .menu-item:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .product-icon { width: 100%;  height: 80px; display: flex; align-items: center; justify-content: center;}
    .btn-confirm { background-color: bisque; color: white; }
    .btn-confirm:hover { background-color: bisque; }
    .order-item { background-color: #f5f5f5; border-radius: 5px; margin-bottom: 10px; }
    .order-for-badge { background-color: #5d4037; color: white; }
    .user-selection { margin-bottom: 20px; padding: 15px; border-radius: 8px; background-color: #eee; }
    .btn-bisque {
    background-color: bisque !important;
    border-color: bisque !important;
    color: black !important;
  }
  .btn-bisque:hover {
    background-color: #e3b98c !important;
    border-color: #e3b98c !important;
    color: black !important;
  }
  </style>
</head>
<body>
<?php
require_once('../connect.php');
session_start();

if (!isset($_SESSION['user_id'] ) || $_SESSION['user_role'] != 'admin') {
  $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
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

if (!isset($_SESSION['selected_user_id'])) {
  $_SESSION['selected_user_id'] = $user_id;
}

if (isset($_POST['change_user'])) {
  $_SESSION['selected_user_id'] = (int)$_POST['selected_user_id'];
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
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
      $order_for_user_id = $_SESSION['selected_user_id'];
      $placed_by_user_id = $user_id;
      
      $conn->query("INSERT INTO orders (user_id, created_at, notes, status, total) 
                   VALUES ('$order_for_user_id', '$date', '{$conn->real_escape_string($_SESSION['order_notes'])}', 
                   'Processing', '$total')");
                   
      $order_id = $conn->insert_id;
      foreach ($_SESSION['order_items'] as $item) {
        $conn->query("INSERT INTO order_products (order_id, product_id, quantity, price) 
                     VALUES ('$order_id', '{$item['id']}', '{$item['quantity']}', '{$item['price']}')");
      }
      $conn->commit();
      
      $ordered_for = $conn->query("SELECT name FROM users WHERE id = $order_for_user_id")->fetch_assoc();
      
      $_SESSION['order_items'] = [];
      $_SESSION['order_notes'] = '';
      $_SESSION['order_success'] = "Order #$order_id confirmed for " . $ordered_for['name'] . ".";
    } catch (Exception $e) {
      $conn->rollback();
      $_SESSION['order_error'] = 'Order failed: ' . $e->getMessage();
    }
  }
  
  if (!isset($_POST['change_user'])) {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }
}


$allusers = [];
$userresult = mysqli_query($conn, "SELECT id, name FROM users");
if ($userresult) {
    while ($row = mysqli_fetch_assoc($userresult)) {
        $allusers[] = $row;
    }
}
$total = array_reduce($_SESSION['order_items'], function($sum, $item) {
  return $sum + ($item['price'] * $item['quantity']);
}, 0);

$selected_user_id = $_SESSION['selected_user_id'];
$selected_user = $conn->query("SELECT name FROM users WHERE id = $selected_user_id")->fetch_assoc();

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

<?php include "../shared/navbar.php"; ?>

<?php if (isset($_SESSION['order_success'])): ?>
<div class="alert alert-success container mt-3"> <?= $_SESSION['order_success'] ?> </div>
<?php unset($_SESSION['order_success']); endif; ?>

<?php if (isset($_SESSION['order_error'])): ?>
<div class="alert alert-danger container mt-3"> <?= $_SESSION['order_error'] ?> </div>
<?php unset($_SESSION['order_error']); endif; ?>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-4">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <span>Current Order</span>
          <span class="badge order-for-badge">
            For: <?= htmlspecialchars($selected_user['name']) ?>
          </span>
        </div>
        <div class="card-body">
          <?php if (empty($_SESSION['order_items'])): ?>
            <div class="text-center text-muted py-3">
              <i class="fas fa-shopping-cart fa-2x mb-2"></i>
              <p>Your order is empty</p>
            </div>
          <?php else: ?>
            <?php foreach ($_SESSION['order_items'] as $item): ?>
              <div class="order-item p-2 mb-2 d-flex justify-content-between">
                <div>
                  <strong><?= htmlspecialchars($item['name']) ?></strong>
                  <div>EGP <?= number_format($item['price'], 2) ?></div>
                </div>
                <div class="d-flex align-items-center">
                  <span class="mx-2">x<?= $item['quantity'] ?></span>
                  <form method="post"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <button name="increase_qty" class="btn btn-sm  btn-bisque w-100"><i class="fas fa-plus"></i></button></form>
                  <form method="post" class="ms-2"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <button name="remove_item" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <form method="post" class="mt-3">
            <textarea class="form-control mb-2" name="notes" placeholder="Add notes..."><?= htmlspecialchars($_SESSION['order_notes']) ?></textarea>
            <button name="update_notes" class="btn btn-bisque w-100 btn-sm">Update Notes</button>
          </form>
          <form method="post" class="mt-3">
            <select name="room" class="form-select mb-2">
              <?php foreach (["Room 101", "Room 102", "Room 103"] as $room): ?>
                <option value="<?= $room ?>" <?= $_SESSION['order_room'] == $room ? 'selected' : '' ?>><?= $room ?></option>
              <?php endforeach; ?>
            </select>
            <button name="update_room" class="btn btn-bisque w-100 btn-sm">Update Room</button>
          </form>
          
          <div class="mt-3 text-end">
            <h5>Total: EGP <?= number_format($total, 2) ?></h5>
          </div>
          
          <form method="post">
            <button name="confirm_order" class="btn btn-confirm w-100 mt-2  btn-bisque w-100" <?= empty($_SESSION['order_items']) ? 'disabled' : '' ?>>
              <i class="fas fa-check"></i> Confirm Order for <?= htmlspecialchars($selected_user['name']) ?>
            </button>
          </form>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">Order For</div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label for="selected_user_id" class="form-label">Select User:</label>
              <select class="form-select" id="selected_user_id" name="selected_user_id" required>
                <?php foreach ($allusers as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= $selected_user_id == $u['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['name']) ?> <?= $u['id'] == $user_id ? '(You)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button name="change_user" class="btn btn-bisque w-100">
              <i class="fas fa-user-check"></i> Change Order Recipient
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card mb-4">
        <div class="card-header">Menu</div>
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

        <div class="card-body">
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <?php if (empty($products)): ?>
            <div class="col">
              <div class="alert alert-warning text-center">No products found.</div>
            </div>
          <?php endif; ?>

        <?php foreach ($products as $product): ?>
        <div class="col">
          <form method="post">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <div class="card menu-item h-100">
              <div class="product-icon">
                <img src="../images/product/<?= htmlspecialchars($product['image']) ?>" width="100" height="80" alt="<?= htmlspecialchars($product['name']) ?> " class="img-fluid rounded ">
              </div>
              <div class="card-body text-center">
                <h6><?= htmlspecialchars($product['name']) ?></h6>
                <p class="text-muted">EGP <?= number_format($product['price'], 2) ?></p>
                <button name="add_item" class="btn btn-bisque w-100">
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
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>