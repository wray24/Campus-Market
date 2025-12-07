<?php
session_start();
if(!isset($_SESSION['userID'])){
    header("Location: login.php");
    exit;
}

require 'db.php';
include 'nav.php';

$isAdmin = ($_SESSION['role'] == "admin");

// Fetch available listings only
$listings = $pdo->query("
    SELECT p.*, u.full_name, c.category_name 
    FROM products p
    JOIN ecomm_users u ON p.product_ownerID = u.userID
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.status = 'available'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html>
<head>
    <title>Listings - CampusMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
    .container {
        padding: 30px;
        max-width: 1200px;
        margin: 30px auto;
        background: #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border-radius: 12px;
    }

    h2 {
        margin-bottom: 25px;
        font-size: 30px;
        font-weight: 600;
        color: #222;
    }

    /* Search + Filter */
    input, select {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #ccc;
        margin-right: 10px;
        margin-bottom: 15px;
        font-size: 14px;
        transition: all 0.2s ease-in-out;
    }

    input:focus, select:focus {
        outline: none;
        border-color: #0077cc;
        box-shadow: 0 0 5px rgba(0, 119, 204, 0.3);
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    th {
        background: #f4f6f9;
        padding: 14px;
        font-weight: 600;
        color: #555;
        text-align: left;
    }

    td {
        padding: 14px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 14px;
        color: #333;
        vertical-align: middle;
    }

    tr:hover {
        background: #eef5ff;
    }

    img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 10px;
        transition: transform 0.2s ease;
    }

    img:hover {
        transform: scale(1.05);
    }

    .status {
        font-weight: 600;
        text-transform: capitalize;
    }

    table td a {
        font-size: 15px;
        font-weight: 500;
        color: #0077cc;
        text-decoration: none;
        transition: 0.2s ease;
    }

    table td a:hover {
        text-decoration: underline;
        color: #005fa3;
    }

    .btn-delete {
        background: #ff4a4a;
        color: white;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        background: #cc0000;
        transform: scale(1.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }

        tr {
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        td {
            padding-left: 50%;
            position: relative;
        }

        td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            font-weight: 600;
            color: #555;
        }

        th {
            display: none;
        }
    }
</style>


</head>
<body>
<div class="container">


<h2>Product Listings</h2>

<!-- SEARCH + FILTER -->
<input type="text" id="searchInput" placeholder="Search listings...">
<select id="categoryFilter">
    <option value="">Filter by Category</option>
    <?php 
        $cats = $pdo->query("SELECT * FROM categories")->fetchAll();
        foreach($cats as $c){
            echo "<option value='{$c['category_name']}'>{$c['category_name']}</option>";
        }
    ?>
</select>

<select id="statusFilter">
    <option value="">Filter by Status</option>
    <option value="available">Available</option>
    <option value="sold">Sold</option>
</select>

<!-- LISTINGS TABLE -->
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Seller</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <?php if($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($listings as $l): ?>
        <tr>
            <td data-label="Image"><img src="uploads/<?= $l['product_image'] ?>"></td>
            <td data-label="Title"><a href="listing.php?id=<?= $l['product_id'] ?>"><?= $l['product_name'] ?></a></td>
            <td data-label="Seller"><?= $l['full_name'] ?></td>
            <td data-label="Category" class="cat"><?= $l['category_name'] ?></td>
            <td data-label="Price">₱<?= number_format($l['product_price'], 2) ?></td>
            <td data-label="Status" class="status"><?= ucfirst($l['status']) ?></td>

            <?php if($isAdmin): ?>
            <td data-label="Actions">
                <a class="btn-delete" 
                   href="remove_listing.php?id=<?= $l['product_id'] ?>"
                   onclick="return confirm('Delete this listing?');">
                   Delete
                </a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


</div>

<!-- SEARCH + FILTER SCRIPT -->

<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});

document.getElementById("categoryFilter").addEventListener("change", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        let cat = row.querySelector(".cat").textContent.toLowerCase();
        row.style.display = (filter === "" || cat === filter) ? "" : "none";
    });
});

document.getElementById("statusFilter").addEventListener("change", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        let stat = row.querySelector(".status").textContent.toLowerCase();
        row.style.display = (filter === "" || stat === filter) ? "" : "none";
    });
});
</script>

</body>
</html>








