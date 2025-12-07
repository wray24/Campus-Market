<?php
session_start();
if(!isset($_SESSION['userID'])){
    header('Location: login.php');
    exit;
}

require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['userID'];

    // Create unique filename
    $image_name = time() . '_' . basename($_FILES['image']['name']);  
    $tmp_name = $_FILES['image']['tmp_name'];

    // Set upload directory
    $upload_dir = __DIR__ . "/uploads/";  

    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0777, true);
    }

    if(move_uploaded_file($tmp_name, $upload_dir . $image_name)){
        $stmt = $pdo->prepare("INSERT INTO products 
            (product_ownerID, category_id, product_name, product_description, product_price, product_image, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'available')");

        $stmt->execute([$user_id, $category_id, $title, $description, $price, $image_name]);

        echo "<p style='color:green; text-align:center;'>Product added successfully! 
              <a href='listings.php'>View Listings</a></p>";
    } else {
        die("Failed to upload image. Check folder path and permissions!");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product - CampusMarket</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: #f5f7fa;
}

/* NAVIGATION */
nav {
    background: rgba(255,255,255,0.95);
    padding: 15px 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    position: sticky;
    top: 0;
}

nav a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: 0.3s;
}
nav a:hover {
    color: #4A8CF7;
}

/* CONTAINER */
.form-container {
    width: 400px;
    margin: 40px auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

h2 {
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
}

/* INPUTS */
input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
}

textarea {
    height: 80px;
    resize: none;
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    background: #4A8CF7;
    border: none;
    color: white;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #2E6ADF;
}
</style>

</head>

<body>

<?php include 'nav.php'; ?>

<div class="form-container">
    <h2>Add Product Listing</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Description:</label>
        <textarea name="description"></textarea>

        <label>Price (₱):</label>
        <input type="number" name="price" step="0.01" required>

        <label>Category:</label>
        <select name="category_id">
            <?php
            $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
            foreach($categories as $cat){
                echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
            }
            ?>
        </select>

        <label>Product Image:</label>
        <input type="file" name="image" required>

        <button type="submit">Add Product</button>
    </form>
</div>

</body>
</html>


