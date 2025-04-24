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
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
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
            background-color: #df4adf;
            cursor: pointer;
        }

        button:hover {
            background-color: #c83cc8;
        }

        .error {
            color: red;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #df4adf;
            color: white;
        }

        .update-btn {
            background-color: #e0ac0d;
        }

        .update-btn:hover {
            background-color: #cf9708;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #c82333;
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
    <div class="container">
        <h2>Manage Categories</h2>
        <form id="category-form" action="" method="POST" onsubmit="return validateForm()">
            <input type="hidden" id="category-id" name="categoryId">
            <input type="text" id="category-name" name="category" placeholder="Enter Category Name">
            <button type="submit" id="form-button" name="addcategory">Add Category</button>
        </form>

        <p class="error" id="error-msg"><?php echo $errors["category"] ?? ""; ?></p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = $offset + 1;
                if (mysqli_num_rows($categoriesResult) === 0) {
                    echo "<tr><td colspan='4'>No categories found.</td></tr>";
                } else {
                    while ($row = mysqli_fetch_assoc($categoriesResult)) {
                        echo "<tr>";
                        echo "<td>" . $count++ . "</td>";
                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                        echo "<td><button class='update-btn' type='button' onclick=\"editCategory('{$row['id']}', '" . htmlspecialchars($row['name'], ENT_QUOTES) . "')\">Edit</button></td>";
                        echo "<td>
                                <form method='POST' style='display:inline'>
                                    <input type='hidden' name='categoryId' value='{$row['id']}'>
                                    <button type='submit' name='delbtn' class='delete-btn'>Delete</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>

            </table>
            <div style="margin-top: 20px; text-align: center;">
                <?php if ($totalPages > 1): ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" style="margin: 0 5px; text-decoration: none; color: <?= $i == $page ? '#df4adf' : '#333' ?>; font-weight: <?= $i == $page ? 'bold' : 'normal' ?>;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            
    </div>

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
