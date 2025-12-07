<?php
session_start();
if(!isset($_SESSION['userID'])){
    header('Location: login.php');
    exit;
}

require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['userID'];
    $rating = $_POST['feedback_rating'];
    $comment = $_POST['feedback_comment'];

    $stmt = $pdo->prepare("INSERT INTO feedback (product_id, userID, feedback_rating, feedback_comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product_id, $user_id, $rating, $comment]);

    header("Location: listing.php?id=$product_id");
    exit;
}
?>
