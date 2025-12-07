<?php
session_start();
require 'db.php';
if(!isset($_SESSION['userID'])){
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $product_id = $_POST['product_id'];

    // Check if product is still available
    $check = $pdo->prepare("SELECT status FROM products WHERE product_id=?");
    $check->execute([$product_id]);
    $product = $check->fetch();

    if($product && $product['status'] == 'available'){
        // Update product status
        $stmt = $pdo->prepare("UPDATE products SET status='sold' WHERE product_id=?");
        $stmt->execute([$product_id]);

        // Insert purchase record
        $stmt2 = $pdo->prepare("INSERT INTO shopping_cart (product_id, buyer_id) VALUES (?, ?)");
        $stmt2->execute([$product_id, $_SESSION['userID']]);

        // Redirect with success
        header("Location: listing.php?id=$product_id&bought=1");
        exit;
    } else {
        // Already sold
        header("Location: listing.php?id=$product_id&bought=0");
        exit;
    }
}
?>
