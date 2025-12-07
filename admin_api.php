<?php
// admin_api.php
session_start();
require 'db.php';

// Only admin allowed
if(!isset($_SESSION['userID']) || ($_SESSION['role'] ?? '') !== 'admin'){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

function json_response($arr){
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}

try {
    if($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $user_id = intval($_POST['user_id']);
        // prevent deleting yourself
        if($user_id == $_SESSION['userID']) json_response(['error' => "You can't delete yourself."]);
        $stmt = $pdo->prepare("DELETE FROM ecomm_users WHERE userID=?");
        $stmt->execute([$user_id]);
        json_response(['ok'=>true]);
    }

    if($action === 'toggle_user_active' && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $user_id = intval($_POST['user_id']);
        $stmt = $pdo->prepare("UPDATE ecomm_users SET is_active = NOT is_active WHERE userID=?");
        $stmt->execute([$user_id]);
        json_response(['ok'=>true]);
    }

    if($action === 'delete_listing' && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id=?");
        $stmt->execute([$product_id]);
        json_response(['ok'=>true]);
    }

    if($action === 'toggle_listing_status' && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $product_id = intval($_POST['product_id']);
        // toggle between 'available' and 'sold'
        $stmt = $pdo->prepare("UPDATE products SET status = CASE WHEN status='available' THEN 'sold' ELSE 'available' END WHERE product_id=?");
        $stmt->execute([$product_id]);
        json_response(['ok'=>true]);
    }

    if($action === 'export_csv' && isset($_GET['type'])){
        $type = $_GET['type'];
        if($type === 'users'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="users_export_'.date('Ymd_His').'.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['userID','full_name','email','role','is_active']);
            $stmt = $pdo->query("SELECT userID, full_name, email, role, is_active FROM users");
            while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
                fputcsv($out, [$r['userID'],$r['full_name'],$r['email'],$r['role'],$r['is_active']]);
            }
            fclose($out);
            exit;
        } elseif($type === 'listings'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="listings_export_'.date('Ymd_His').'.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['product_id','title','seller_id','price','category_id','status','created_at']);
            $stmt = $pdo->query("SELECT product_id, product_name, product_ownerID AS seller_id, price, category_id, status FROM products");
            while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
                fputcsv($out, [$r['product_id'],$r['product_name'],$r['seller_id'],$r['price'],$r['category_id'],$r['status']]);
            }
            fclose($out);
            exit;
        }
    }

    if($action === 'chart_data'){
        // users count
        $users_total = $pdo->query("SELECT COUNT(*) FROM ecomm_users")->fetchColumn();
        // products available vs sold
        $available = $pdo->query("SELECT COUNT(*) FROM products WHERE status='available'")->fetchColumn();
        $sold = $pdo->query("SELECT COUNT(*) FROM products WHERE status='sold'")->fetchColumn();
        // purchases per month (last 6 months)
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(purchase_at, '%Y-%m') AS ym, COUNT(*) AS cnt
            FROM shopping_cart
            WHERE purchase_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY ym
            ORDER BY ym
        ");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response([
            'users_total' => intval($users_total),
            'available' => intval($available),
            'sold' => intval($sold),
            'sales' => $sales
        ]);
    }

    // unknown action
    json_response(['error'=>'Unknown action']);
} catch(PDOException $e){
    http_response_code(500);
    json_response(['error'=>$e->getMessage()]);
}
