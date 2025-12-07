<?php
session_start();
if(!isset($_SESSION['userID'])){
    header('Location: login.php');
    exit;
}

require 'db.php';

// --- DITO KO NA NILAGAY ANG SLOGAN CODE --- //
$slogans = [
    "Find What You Need, Right Inside the Campus.",
    "Shop Smart. Buy Local. Support Students.",
    "Everything You Need, Sold by Your Schoolmates.",
    "Campus Deals, Trusted Sellers.",
    "Browse Student Listings Today."
];

$random_slogan = $slogans[array_rand($slogans)];
// ------------------------------------------- //
?>

<!DOCTYPE html>
<html>
<head>
<title>CampusMarket</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: url('assets/images/index-bg.jpg') no-repeat center center fixed;
    background-size: cover;
}

.body-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.35);
    z-index: -1;
}

nav {
    background-color: #0A5D2A;
    padding: 15px 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 20px;
    position: sticky;
    top: 0;
    z-index: 10;
}

nav a {
    text-decoration: none;
    font-weight: 500;
    color: #fff;
    transition: 0.3s;
}

nav a:hover {
    color: #F4C300;
}

.header {
    padding: 50px 20px 30px;
    text-align: center;
}

.header h2 {
    font-size: 42px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 10px;
    text-shadow: 
        0 3px 6px rgba(0,0,0,0.7),
        0 0 10px rgba(0,0,0,0.6);
}

.header p {
    font-size: 22px;
    font-weight: 500;
    color: #ffffff;
    text-shadow: 
        0 3px 6px rgba(0,0,0,0.7),
        0 0 10px rgba(0,0,0,0.6);
}

/* ==== FADE ANIMATION ==== */
.fade {
    opacity: 0;
    animation: fadeIn 1.5s forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
/* ======================== */

.product-grid {
    width: 90%;
    margin: 30px auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
    padding-bottom: 50px;
}

.product-card {
    background: rgba(255,255,255,0.85);
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.2);
    transition: 0.3s;
    backdrop-filter: blur(4px);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.product-card img {
    width: 100%;
    height: 180px;
    border-radius: 10px;
    object-fit: cover;
}

.product-title {
    font-size: 18px;
    font-weight: 600;
    margin-top: 10px;
    color: #0A5D2A;
}

.product-info {
    font-size: 14px;
    margin-top: 5px;
    opacity: 0.8;
}

.product-card a {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 15px;
    background: #0A5D2A;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    transition: 0.3s;
}

product-card a:hover {
    background: #D6281F;
    transform: translateY(-2px);
}

@media (max-width: 768px){
    .product-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px){
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="body-overlay"></div>

<?php include 'nav.php'; ?>

<div class="header">
    <h2>Welcome, <?= $_SESSION['full_name'] ?>!</h2>
    <p class="fade"><?= $random_slogan ?></p>
</div>

<div class="product-grid">
<?php
$products = $pdo->query("SELECT p.*, c.category_name, u.full_name 
                         FROM products p
                         JOIN categories c ON p.category_id = c.category_id
                         JOIN ecomm_users u ON p.product_ownerID = u.userID
                         WHERE p.status='available'")->fetchAll();

foreach($products as $p): ?>
    <div class="product-card">
        <img src="uploads/<?= $p['product_image'] ?>">

        <div class="product-title"><?= $p['product_name'] ?></div>

        <div class="product-info">₱<?= number_format($p['product_price'],2) ?></div>
        <div class="product-info">Category: <?= $p['category_name'] ?></div>
        <div class="product-info">Seller: <?= $p['full_name'] ?></div>

        <a href="listing.php?id=<?= $p['product_id'] ?>">View Details</a>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>




