<?php
session_start();
if(!isset($_SESSION['userID'])){
    header("Location: login.php");
    exit;
}

require 'db.php';
include 'nav.php';

$product_id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_review'])) {
        $product_id = $_GET['id'] ?? 0;

        // Delete the review for this user and product
        $stmt = $pdo->prepare("DELETE FROM prod_fb WHERE product_post=? AND user_fbID=?");
        $stmt->execute([$product_id, $_SESSION['userID']]);

        // Redirect to refresh the page
        header("Location: listing.php?id=$product_id");
        exit;
    }
}
// Fetch product
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, u.full_name, u.userID AS seller_id
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    JOIN ecomm_users u ON p.product_ownerID = u.userID
    WHERE p.product_id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Fetch reviews
$reviews = $pdo->prepare("
    SELECT r.*, u.full_name 
    FROM prod_fb r
    JOIN ecomm_users u ON r.user_fbID = u.userID
    WHERE r.product_post = ?
    ORDER BY r.rev_date DESC
");
$reviews->execute([$product_id]);
$review_list = $reviews->fetchAll();

// Average rating
$avgRating = $pdo->prepare("SELECT AVG(feedback_rating) FROM prod_fb WHERE product_post= ?");
$avgRating->execute([$product_id]);
$averageVal = $avgRating->fetchColumn();
$average = $averageVal ? round($averageVal, 1) : 0;

// Check if user reviewed
$check = $pdo->prepare("SELECT * FROM prod_fb WHERE product_post=? AND user_fbID=?");
$check->execute([$product_id, $_SESSION['userID']]);
$userReview = $check->fetch();

// Helper function for stars
function renderStars($num){
    $full = floor($num);
    $half = ($num - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - ($full + $half);
    return str_repeat("⭐", $full) . str_repeat("☆", $half) . str_repeat("✩", $empty);
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

    // Get values from form
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    // Prepare the INSERT statement
    $stmt = $pdo->prepare("
        INSERT INTO prod_fb 
        (user_fbID, product_post, feedback_rating, feedback_comment, rev_date) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    // Execute the query with actual values
    $stmt->execute([
        $_SESSION['userID'],   // current logged-in user
        $product_id,           // product being reviewed
        $rating,               // rating value
        $feedback              // review comment
    ]);

    // Redirect to refresh page and show the new review
    header("Location: listing.php?id=$product_id");
    exit;
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review'])) {
    // Get values from form
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    // Prepare the INSERT statement
    $stmt = $pdo->prepare("
        UPDATE prod_fb 
        SET feedback_rating = ?,
            feedback_Comment = ?,
            rev_date = NOW()
        WHERE user_fbID = ? and product_post = ?
    ");

    // Execute the query with actual values
    $stmt->execute([$rating,  $feedback, $_SESSION['userID'], $product_id]);

    // Redirect to refresh page and show the new review
    header("Location: listing.php?id=$product_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?= $product['product_name'] ?> - CampusMarket</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
body { background: #F3F3F3; margin: 0; font-family: 'Poppins', sans-serif; }
.container { width: 90%; max-width: 1100px; margin:auto; padding:30px; }

/* ALERTS */
.alert-success { padding:12px; background:#D4EDDA; color:#155724; border-radius:8px; margin-bottom:15px; }
.alert-error { padding:12px; background:#F8D7DA; color:#721C24; border-radius:8px; margin-bottom:15px; }

/* PRODUCT CARD */
.product-card { background:white; padding:25px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.15); display:flex; gap:30px; }
.product-card img { width:350px; height:350px; object-fit:cover; border-radius:12px; }
.title { font-size:30px; font-weight:600; color:#0A5D2A; }
.info { font-size:17px; margin:10px 0; opacity:.75; }
.rating-box { margin-top:10px; font-size:22px; color:#F4C300; }

/* BUY BUTTON */
.buy-btn { display:inline-block; margin-top:20px; background:#0A5D2A; color:white; padding:14px 22px; border-radius:10px; text-decoration:none; font-weight:600; font-size:18px; transition:.3s; }
.buy-btn:hover { background:#F4C300; color:#000; }

/* REVIEW FORM */
.review-box { background:#fff; margin-top:25px; padding:20px; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,0.1); }
.review-box textarea { width:100%; height:120px; padding:12px; border-radius:10px; border:1px solid #ccc; }
.btn { margin-top:10px; padding:12px 18px; background:#0A5D2A; color:white; border-radius:8px; border:none; cursor:pointer; transition:.3s; }
.btn:hover { background:#D6281F; }
.delete-btn { background:#D6281F; }

/* REVIEW LIST */
.review { background:white; padding:18px; border-radius:12px; margin-top:15px; box-shadow:0 3px 10px rgba(0,0,0,0.08); }
.review strong { color:#0A5D2A; }
.stars { color:#F4C300; font-size:20px; }
</style>
</head>
<body>

<div class="container">

<!-- ALERTS -->
<?php if(isset($_GET['bought'])): ?>
    <?php if($_GET['bought']==1): ?>
        <div class="alert-success">✅ Purchase successful!</div>
    <?php else: ?>
        <div class="alert-error">⚠️ You have already purchased this product or it is sold out.</div>
    <?php endif; ?>
<?php endif; ?>

<!-- PRODUCT CARD -->
<div class="product-card">
    <img src="uploads/<?= $product['product_image'] ?>">
    <div>
        <div class="title"><?= $product['product_name'] ?></div>
        <p class="info">₱<?= number_format($product['product_price'],2) ?></p>
        <p class="info">Category: <?= $product['category_name'] ?></p>
        <p class="info">Seller: <?= $product['full_name'] ?></p>
        <div class="rating-box">
            ⭐ Average Rating: <?= $average>0 ? renderStars($average)." ($average)" : "No ratings yet" ?>
        </div>

        <!-- BUY BUTTON (disable if sold or user is seller) -->
        <?php if($product['status']=='available' && $_SESSION['userID'] != $product['product_ownerID']): ?>
            <form method="POST" action="buy_product.php">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <button type="submit" class="buy-btn">Buy Now</button>
            </form>
        <?php elseif($_SESSION['userID']==$product['product_ownerID']): ?>
            <button class="buy-btn" style="background:#ccc; cursor:not-allowed;">Your Product</button>
        <?php else: ?>
            <button class="buy-btn" style="background:#ccc; cursor:not-allowed;">Sold Out</button>
        <?php endif; ?>
    </div>
</div>

<!-- REVIEW FORM -->
<div class="review-box">
    <h3 style="color:#0A5D2A;margin-bottom:10px;">Product Review</h3>
    <?php if(!$userReview): ?>
        <form method="POST">
            <label>Rating:</label><br>
            <select name="rating" required style="padding:8px;border-radius:8px;border:1px solid #ccc;margin:5px 0;">
                <option value="">Select rating</option>
                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                <option value="4">⭐⭐⭐⭐ Good</option>
                <option value="3">⭐⭐⭐ Average</option>
                <option value="2">⭐⭐ Poor</option>
                <option value="1">⭐ Very Bad</option>
            </select><br><br>
            <label>Feedback:</label>
            <textarea name="feedback" placeholder="Write your feedback..."></textarea>
            <button type="submit" class="btn" name="submit_review">Submit Review</button>
        </form>
    <?php else: 
        ?>
        <form method="POST">
            <label>Edit Your Rating:</label><br>
            <select name="rating" required style="padding:8px;border-radius:8px;border:1px solid #ccc;margin:5px 0;">
                <option value="5" <?= $userReview['feedback_rating']==5?'selected':'' ?>>⭐⭐⭐⭐⭐</option>
                <option value="4" <?= $userReview['feedback_rating']==4?'selected':'' ?>>⭐⭐⭐⭐</option>
                <option value="3" <?= $userReview['feedback_rating']==3?'selected':'' ?>>⭐⭐⭐</option>
                <option value="2" <?= $userReview['feedback_rating']==2?'selected':'' ?>>⭐⭐</option>
                <option value="1" <?= $userReview['feedback_rating']==1?'selected':'' ?>>⭐</option>
            </select>
            <textarea name="feedback"><?= $userReview['feedback_comment'] ?></textarea>
            <button type="submit" class="btn" name="edit_review">Update Review</button>
            <button type="submit" class="btn delete-btn" name="delete_review">Delete Review</button>
        </form>
    <?php 
        
       endif; ?>
</div>

<!-- REVIEWS -->
<h3 style="margin-top:30px;color:#0A5D2A;">Customer Reviews</h3>
<?php if(count($review_list)==0): ?><p>No reviews yet.</p><?php endif; ?>
<?php foreach($review_list as $rev): ?>
    <div class="review">
        <strong><?= $rev['full_name'] ?></strong><br>
        <div class="stars"><?= renderStars($rev['feedback_rating']) ?></div>
        <p><?= $rev['feedback_comment'] ?></p>
        <small><?= $rev['rev_date'] ?></small>
    </div>
<?php endforeach; ?>

</div>
</body>
</html>












