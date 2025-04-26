<?php
require_once('../connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../shared/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify the order belongs to the user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'Processing'");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update order status to cancelled
        $update = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $update->bind_param("i", $order_id);
        $update->execute();
        $update->close();
        
        $_SESSION['order_message'] = "Order #$order_id has been cancelled.";
    } else {
        $_SESSION['order_error'] = "Order not found or cannot be cancelled.";
    }
    
    $stmt->close();
    header('Location: my_orders.php');
    exit();
}

header('Location: my_orders.php');
exit();