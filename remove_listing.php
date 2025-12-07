<?php
session_start();
if(!isset($_SESSION['userID']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM products WHERE product_id=?");
$stmt->execute([$id]);

header("Location: listings.php");
exit;
