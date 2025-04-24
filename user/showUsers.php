
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Users</title>
    <style> 
    .table-responsive {
    max-width: 1200px;
    margin: 0 auto;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
}

.table th {
    font-weight: 600;
}

.table img {
    transition: transform 0.3s;
}

.table img:hover {
    transform: scale(1.1);
}

.btn-sm {
    margin: 2px;
}</style>
</head>
<body>
    
</body>
</html>
<?php
include_once "connect.php";

// Fetch all users from database
$query = "SELECT id, name, email, picture, role FROM users";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo '
    <div class="container mt-5">
        <h2 class="text-center mb-4" style="color: #0d6efd;">Registered Users</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Profile Picture</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
    
    while($row = $result->fetch_assoc()) {
        echo '
                    <tr>
                        <td>'.$row["id"].'</td>
                        <td>'.$row["name"].'</td>
                        <td>'.$row["email"].'</td>
                        <td><img src="'.$row["picture"].'" alt="Profile" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"></td>
                        <td>'.ucfirst($row["role"]).'</td>
                        <td>
                            <a href="edit_user.php?id='.$row["id"].'" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_user.php?id='.$row["id"].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
                        </td>
                    </tr>';
    }
    
    echo '
                </tbody>
            </table>
        </div>
    </div>';
} else {
    echo '<div class="container mt-5"><div class="alert alert-info">No users found in database.</div></div>';
}

$conn->close();
?>