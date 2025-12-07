<?php
session_start();
if(!isset($_SESSION['userID']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit;
}

require 'db.php';
include 'nav.php';

// Fetch all users
$users = $pdo->query("SELECT * FROM ecomm_users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users Management - Admin</title>

    <style>
        .container {
            padding: 25px;
            max-width: 1100px;
            margin: auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        input, select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background: #eaeaea;
            padding: 10px;
            text-align: left;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f5f5f5;
        }
    </style>

</head>
<body>

<div class="container">

    <h2>Users Management</h2>

    <!-- SEARCH + FILTER -->
    <input type="text" id="searchInput" placeholder="Search users...">
    
    <select id="filterSelect">
        <option value="">Filter by Role</option>
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
        <option value="admin">Admin</option>
    </select>

    <!-- USERS TABLE -->
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th class="role">Role</th>
                <th>Date Created</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($users as $u): ?>
                <tr>
                    <td><?= $u['userID'] ?></td>
                    <td><?= $u['full_name'] ?></td>
                    <td><?= $u['email'] ?></td>
                    <td class="role"><?= $u['role'] ?></td>
                    <td><?= $u['regdate'] ?? 'N/A' ?></td>
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

document.getElementById("filterSelect").addEventListener("change", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        let role = row.querySelector(".role").textContent.toLowerCase();
        row.style.display = (filter === "" || role === filter) ? "" : "none";
    });
});
</script>

</body>
</html>
