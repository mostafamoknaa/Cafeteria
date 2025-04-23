<?php
require "../connect.php";
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = ["name" => "", "price" => "", "category" => "", "image" => ""];
$name = $price = $category_id = $image = "";
$available = 1;
$product_id = null;

$categories = [];
$result = mysqli_query($conn, "SELECT id, name FROM categories");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}

// Fetch product if editing
if (isset($_GET['edit'])) {
    $product_id = intval($_GET['edit']);
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        $name = $product['name'];
        $price = $product['price'];
        $category_id = $product['category_id'];
        $available = $product['available'];
        $image = $product['image'];
    } else {
        $_SESSION['error_message'] = "Product not found.";
        header("Location: listproduct.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["name"] ?? '');
    $price = mysqli_real_escape_string($conn, $_POST["price"] ?? '');
    $category_id = mysqli_real_escape_string($conn, $_POST["category_id"] ?? '');
    $available = isset($_POST["available"]) ? 1 : 0;
    $product_id = $_POST["product_id"] ?? '';

    $imageFile = $_FILES["image"] ?? null;
    $imageName = $_FILES["image"]["name"] ?? '';
    $imageTmpName = $_FILES["image"]["tmp_name"] ?? '';
    $imageError = $_FILES["image"]["error"] ?? '';
    $imageSize = $_FILES["image"]["size"] ?? 0;
    $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
    $maxFileSize = 2 * 1024 * 1024;
    $uploadDir = "../images/product/";
    $uploadFile = $uploadDir . basename($imageName);

    if (empty($name)) {
        $errors["name"] = "Please enter a product name.";
    }
    if (empty($price) || !is_numeric($price) || $price < 0) {
        $errors["price"] = "Please enter a valid price.";
    }
    if (empty($category_id)) {
        $errors["category"] = "Please select a category.";
    }

    if (!empty($imageName)) {
        if ($imageError !== 0) {
            $errors["image"] = "Please upload a valid image file.";
        } elseif (!in_array($imageExtension, $allowedExtensions)) {
            $errors["image"] = "Allowed types: jpg, jpeg, png, gif.";
        } elseif ($imageSize > $maxFileSize) {
            $errors["image"] = "Image exceeds 2MB limit.";
        } elseif (!move_uploaded_file($imageTmpName, $uploadFile)) {
            $errors["image"] = "Failed to upload the image.";
        } else {
            if (!empty($_POST["current_image"]) && file_exists($uploadDir . $_POST["current_image"])) {
                unlink($uploadDir . $_POST["current_image"]);
            }
            $image = $imageName;
        }
    } else if (isset($_POST["updatebtn"])) {
        $image = $_POST["current_image"] ?? '';
    }

    if (empty(array_filter($errors))) {
        if (isset($_POST["addproduct"])) {
            $sql = "INSERT INTO products (name, price, category_id, image, available) 
                    VALUES ('$name', '$price', '$category_id', '$image', '$available')";
        } elseif (isset($_POST["updatebtn"])) {
            $sql = "UPDATE products 
                    SET name='$name', price='$price', category_id='$category_id', image='$image', available='$available' 
                    WHERE id='$product_id'";
        }

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = isset($_POST["addproduct"]) ? "Product added!" : "Product updated!";
            header("Location: listproduct.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error saving product.";
            header("Location: listproduct.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Product Form</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
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
      background-color: var(--light-bg);
      color: var(--text-color);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .form-wrapper {
      width: 100%;
      max-width: 500px;
    }

    .card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 25px;
    }

    h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
      text-align: center;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
    }

    input[type="text"],
    input[type="number"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 16px;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
    }

    .checkbox-group label {
      margin-left: 8px;
    }

    .file-input-group {
      margin-bottom: 10px;
    }

    .current-image {
      margin-top: 10px;
      max-width: 100px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      padding: 5px;
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
      text-align: center;
      transition: background-color 0.3s;
    }

    .btn:hover {
      background-color: #2980b9;
    }

    .btn-success {
      background-color: var(--secondary-color);
    }

    .btn-success:hover {
      background-color: #27ae60;
    }

    .btn + .btn {
      margin-left: 10px;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      color: white;
      font-weight: 500;
    }

    .alert-success {
      background-color: var(--secondary-color);
    }

    .alert-danger {
      background-color: var(--danger-color);
    }

    .alert ul {
      margin-left: 20px;
    }
  </style>
</head>
<body>

<div class="form-wrapper">
  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
      <?php unset($_SESSION['success_message']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
      <?php unset($_SESSION['error_message']); ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2><?= isset($_GET['edit']) ? 'Edit Product' : 'Add New Product' ?></h2>
    <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <?php if (isset($_GET['edit'])): ?>
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
        <input type="hidden" name="current_image" value="<?= htmlspecialchars($image) ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
      </div>

      <div class="form-group">
        <label for="price">Price ($):</label>
        <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" min="0" required>
      </div>

      <div class="form-group">
        <label for="category">Category:</label>
        <select id="category" name="category_id" required>
          <option value="">Select Category</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group file-input-group">
        <label for="image">Product Image:</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (isset($_GET['edit']) && !empty($image)): ?>
          <div>
            <p>Current image:</p>
            <img src="../images/product/<?= htmlspecialchars($image) ?>" alt="Current product image" class="current-image">
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" id="available" name="available" <?= $available ? 'checked' : '' ?>>
        <label for="available">Product Available</label>
      </div>

      <div class="form-group">
        <button type="submit" name="<?= isset($_GET['edit']) ? 'updatebtn' : 'addproduct' ?>" class="btn btn-success">
          <i class="fas fa-save"></i> <?= isset($_GET['edit']) ? 'Update Product' : 'Add Product' ?>
        </button>
        <?php if (isset($_GET['edit'])): ?>
          <a href="products.php" class="btn">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('image').onchange = function(event) {
    var reader = new FileReader();
    reader.onload = function() {
      var output = document.querySelector('.current-image');
      if (!output) {
        output = document.createElement('img');
        output.className = 'current-image';
        document.querySelector('.file-input-group').after(output);
      }
      output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  };
</script>

</body>
</html>