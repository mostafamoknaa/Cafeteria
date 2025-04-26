<?php
require "../connect.php";
session_start(); 
$errors = ["category" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = mysqli_real_escape_string($conn, $_POST["category"] ?? '');
    $categoryId = $_POST["categoryId"] ?? '';

    if (empty($category)) {
        $errors["category"] = "Please enter a category.";
    } else {
        $checkQuery = "SELECT * FROM categories WHERE name = '$category'";
        if ($categoryId) {
            $checkQuery .= " AND id != '$categoryId'";
        }
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $errors["category"] = "This category already exists.";
        }
    }

    if (!array_filter($errors)) {
        if (isset($_POST["addcategory"])) {
            $sql = "INSERT INTO categories (name) VALUES ('$category')";
        } elseif (isset($_POST["updatebtn"])) {
            $sql = "UPDATE categories SET name='$category' WHERE id='$categoryId'";
        }

        if (mysqli_query($conn, $sql)) {
            header("Location: listcategory.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

if (isset($_POST["delbtn"])) {
    $categoryId = $_POST["categoryId"];
    $deleteQuery = "DELETE FROM categories WHERE id = '$categoryId'";
    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: listcategory.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$totalQuery = "SELECT COUNT(*) AS total FROM categories";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRows = $totalRow['total'];
$totalPages = ceil($totalRows / $limit);

$categoriesQuery = "SELECT * FROM categories ORDER BY id ASC LIMIT $limit OFFSET $offset";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .main-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="text"] {
            flex: 1 1 60%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            color: white;
            background-color: #5d4037;
            cursor: pointer;
        }

        button:hover {
            background-color:rgb(15, 4, 1);
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #5d4037;
            color: white;
        }

        .update-btn {
            background-color: #e0ac0d;
            border-radius: 10px;
        }

        .update-btn:hover {
            background-color: #cf9708;
        }

        .delete-btn {
            background-color: #dc3545;
            border-radius: 10px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #5d4037;
            color: white;
        }

        @media (max-width: 600px) {
            form {
                flex-direction: column;
                align-items: stretch;
            }

            input, button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include "../shared/navbar.php"; ?>
    
    <div class="main-container">
        <h2>Manage Categories</h2>
        <form id="category-form" action="" method="POST" onsubmit="return validateForm()">
            <input type="hidden" id="category-id" name="categoryId">
            <input type="text" id="category-name" name="category" placeholder="Enter Category Name">
            <button type="submit" id="form-button" name="addcategory">Add Category</button>
        </form>

        <p class="error" id="error-msg"><?php echo $errors["category"] ?? ""; ?></p>

        <table>
            <thead>
                <tr class="table-header text-center align-middle text-white bg-dark ">
                    <th>#</th>
                    <th>Category</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body text-center align-middle text-dark bg-light ">
                <?php
                $count = $offset + 1;
                if (mysqli_num_rows($categoriesResult) === 0) {
                    echo "<tr><td colspan='4'>No categories found.</td></tr>";
                } else {
                    while ($row = mysqli_fetch_assoc($categoriesResult)) {
                        echo "<tr>";
                        echo "<td>" . $count++ . "</td>";
                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                        echo "<td><button class='update-btn' type='button' onclick=\"editCategory('" . $row['id'] . "', '" . htmlspecialchars($row['name'], ENT_QUOTES) . "')\">Edit</button></td>";
                        echo "<td>
                                <form method='POST' style='display:inline; margin:0; padding:0;'>
                                    <input type='hidden' name='categoryId' value='" . $row['id'] . "'>
                                    <button type='submit' name='delbtn' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this category?');\">Delete</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-kQtW33rZJAHjgefvhyyzcGFETqB7g/0Qfdf5xUL6VwKZV8Hj1/igZ0SovJUc1Y6z" crossorigin="anonymous"></script>
    <script>
        function editCategory(id, name) {
            document.getElementById("category-id").value = id;
            document.getElementById("category-name").value = name;
            const button = document.getElementById("form-button");
            button.textContent = "Update Category";
            button.name = "updatebtn";
        }

        function validateForm() {
            const categoryName = document.getElementById("category-name").value.trim();
            if (!categoryName) {
                document.getElementById("error-msg").innerText = "Please enter a category.";
                return false;
            }
            return true;
        }
    </script>
</body>
</html>