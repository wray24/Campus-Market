<?php
// nav.php
// Assumes session_start() is already called in parent file
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<style>
/* ===== NAVBAR STYLES ===== */
nav {
    background-color: #0A5D2A; /* Dark Green */
    padding: 12px 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

nav .logo {
    color: #fff;
    font-weight: 700;
    font-size: 26px;
    text-decoration: none;
    display: flex;
    align-items: center;
}

nav .logo img {
    height: 35px;
    margin-right: 10px;
}

nav .menu {
    display: flex;
    align-items: center;
    position: relative;
}

nav .menu a {
    color: #fff;
    text-decoration: none;
    margin-left: 25px;
    font-weight: 500;
    font-size: 16px;
    transition: all 0.3s ease;
    position: relative;
}

nav .menu a::after {
    content: '';
    display: block;
    width: 0;
    height: 2px;
    background: #F4C300; /* Gold hover underline */
    transition: width 0.3s;
    position: absolute;
    bottom: -4px;
    left: 0;
}

nav .menu a:hover::after {
    width: 100%;
}

nav .menu a:hover {
    color: #F4C300; /* Gold text on hover */
    transform: translateY(-2px);
}

/* Dropdown styles */
.dropdown {
    position: relative;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 180px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    border-radius: 6px;
    top: 40px;
    z-index: 100;
    flex-direction: column;
}

.dropdown-content a {
    color: #0A5D2A; /* Dark Green text */
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    transition: background 0.3s;
}

.dropdown-content a:hover {
    background-color: #F4C300; /* Gold hover */
    color: #fff;
}

/* Show dropdown on hover */
.dropdown:hover .dropdown-content {
    display: flex;
}

/* Responsive */
@media (max-width: 768px) {
    nav {
        flex-direction: column;
        align-items: flex-start;
    }
    nav .menu {
        flex-direction: column;
        width: 100%;
        margin-top: 10px;
    }
    nav .menu a, .dropdown {
        margin: 8px 0;
    }
    .dropdown-content {
        position: relative;
        top: 0;
        box-shadow: none;
    }
}
</style>

<nav>
    <a href="index.php" class="logo">
        <!-- Optional logo -->
        <!-- <img src="logo.png" alt="Logo"> -->
        CampusMarket
    </a>
    <div class="menu">
        <a href="index.php">&#127968; Home</a>
        <a href="listings.php">&#128722; Listings</a>
        <a href="add_listing.php">&#10133; Add Product</a>

        <?php if($isAdmin): ?>
            <div class="dropdown">
                <a href="admin_dashboard.php">&#9881; Admin &#9662;</a>
                <div class="dropdown-content">
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="users.php">Manage Users</a>
                    <a href="listings.php">Manage Listings</a>
                    <a href="reports.php">Reports</a>
                </div>
            </div>
        <?php endif; ?>

        <a href="logout.php">&#128274; Logout</a>
    </div>
</nav>


