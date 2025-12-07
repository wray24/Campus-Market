<?php
session_start();
if(!isset($_SESSION['userID']) || ($_SESSION['role'] ?? '') != 'admin'){
    header('Location: login.php');
    exit;
}
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - CampusMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --green: #0E462E;
            --light-green: #E5F2EB;
            --soft-bg: #F4F6F4;
            --white: #FFFFFF;
            --text-dark: #1A1A1A;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--soft-bg);
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
        }

        /* Top Header */
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .title {
            font-size: 26px;
            font-weight: 600;
            color: var(--green);
            letter-spacing: 0.5px;
        }

        .controls {
            display: flex;
            gap: 10px;
        }

        /* Beautiful minimal Apple-style buttons */
        .btn {
            padding: 11px 18px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.25s;
        }

        .btn-green {
            background: var(--green);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(14, 70, 46, 0.25);
        }
        .btn-green:hover {
            background: #0b3623;
        }

        .btn-soft {
            background: var(--white);
            border: 1px solid #ddd;
        }
        .btn-soft:hover {
            background: #f5f5f5;
        }

        /* GRID LAYOUT */
        .grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        /* Neumorphic Card */
        .card {
            background: var(--white);
            border-radius: 18px;
            padding: 20px;
            box-shadow:
                0 8px 20px rgba(0,0,0,0.08),
                0 2px 6px rgba(0,0,0,0.04);
        }

        /* Summary */
        .summary {
            display: flex;
            gap: 14px;
        }
        .summary .box {
            flex: 1;
            background: var(--light-green);
            border-radius: 14px;
            padding: 16px;
            text-align: center;
        }
        .summary .num {
            font-size: 24px;
            font-weight: 700;
            color: var(--green);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }
        th {
            background: var(--light-green);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 10px;
            border-radius: 10px;
            border: none;
            font-size: 13px;
            cursor: pointer;
        }
        .danger { background: #D9534F; color: #fff; }
        .ok { background: var(--green); color: white; }
        .muted { background: #eee; }

        .chart-wrap {
            height: 260px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<?php include 'nav.php'; ?>

<div class="top">
    <div class="title">CampusMarket — Admin Dashboard</div>

    <div class="controls">
        <button class="btn btn-green" onclick="location.reload()">Refresh</button>
        <button class="btn btn-soft" onclick="exportCSV('users')">Export Users</button>
        <button class="btn btn-soft" onclick="exportCSV('listings')">Export Listings</button>
    </div>
</div>

<div class="grid">

    <!-- LEFT -->
    <div>

        <!-- Users Table -->
        <div class="card">
            <h3 style="margin:0 0 10px;color:var(--green);">Users</h3>

            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php
                $users = $pdo->query("SELECT userID, full_name, email, role, is_active FROM ecomm_users ORDER BY userID DESC")->fetchAll();
                foreach($users as $u){
                    $active = $u['is_active'] ? 'Yes' : 'No';

                    echo "<tr id='user-{$u['userID']}'>
                        <td>{$u['userID']}</td>
                        <td>".htmlspecialchars($u['full_name'])."</td>
                        <td>".htmlspecialchars($u['email'])."</td>
                        <td>{$u['role']}</td>
                        <td>{$active}</td>
                        <td>
                            <button class='action-btn ok' onclick='toggleUser({$u['userID']})'>".($u['is_active']? 'Suspend':'Unsuspend')."</button>
                            <button class='action-btn danger' onclick='deleteUser({$u['userID']})'>Delete</button>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Listings Table -->
        <div class="card" style="margin-top:20px;">
            <h3 style="margin:0 0 10px;color:var(--green);">Active Listings</h3>

            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php
                $listings = $pdo->query("
                    SELECT p.product_id, p.product_name, u.full_name, c.category_name, p.product_price, p.status
                    FROM products p
                    JOIN ecomm_users u ON p.product_ownerID=u.userID
                    JOIN categories c ON p.category_id=c.category_id
                    ORDER BY p.product_id DESC
                ")->fetchAll();

                foreach($listings as $l){
                    echo "<tr id='prod-{$l['product_id']}'>
                        <td>{$l['product_id']}</td>
                        <td>".htmlspecialchars($l['product_name'])."</td>
                        <td>".htmlspecialchars($l['full_name'])."</td>
                        <td>{$l['category_name']}</td>
                        <td>₱{$l['product_price']}</td>
                        <td>{$l['status']}</td>
                        <td>
                            <button class='action-btn ok' onclick='toggleListing({$l['product_id']})'>".($l['status']=='available'?'Mark Sold':'Mark Available')."</button>
                            <button class='action-btn danger' onclick='deleteListing({$l['product_id']})'>Remove</button>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- RIGHT -->
    <div>

        <!-- Summary -->
        <div class="card">
            <h3 style="margin:0 0 12px;color:var(--green);">Quick Summary</h3>

            <div class="summary">
                <div class="box">
                    <div class="num" id="sum-users">0</div>
                    Users
                </div>

                <div class="box">
                    <div class="num" id="sum-available">0</div>
                    Available
                </div>

                <div class="box">
                    <div class="num" id="sum-sold">0</div>
                    Sold
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="card" style="margin-top:20px;">
            <h3 style="margin:0 0 10px;color:var(--green);">Charts</h3>

            <canvas id="chart-sales" class="chart-wrap"></canvas>

            <div style="margin-top:18px;">
                <canvas id="chart-availability" class="chart-wrap"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
/* Helper for admin_api */
async function postJSON(body){
    const res = await fetch('admin_api.php?action='+(body.action||''), {
        method: 'POST',
        body: new URLSearchParams(body)
    });
    return res.json();
}

async function deleteUser(id){
    if(!confirm('Delete user ID '+id+'?')) return;
    const res = await postJSON({action:'delete_user', user_id:id});
    if(res.ok) document.getElementById('user-'+id).remove();
}

async function toggleUser(id){
    if(!confirm('Toggle user status?')) return;
    const res = await postJSON({action:'toggle_user_active', user_id:id});
    if(res.ok) location.reload();
}

async function deleteListing(id){
    if(!confirm('Remove listing '+id+'?')) return;
    const res = await postJSON({action:'delete_listing', product_id:id});
    if(res.ok) document.getElementById('prod-'+id).remove();
}

async function toggleListing(id){
    if(!confirm('Toggle listing?')) return;
    const res = await postJSON({action:'toggle_listing_status', product_id:id});
    if(res.ok) location.reload();
}

function exportCSV(type){
    window.location.href = "admin_api.php?action=export_csv&type="+type;
}

/* Load charts */
async function loadCharts(){
    const r = await fetch('admin_api.php?action=chart_data');
    const d = await r.json();

    document.getElementById('sum-users').textContent = d.users_total;
    document.getElementById('sum-available').textContent = d.available;
    document.getElementById('sum-sold').textContent = d.sold;

    /* Sales Chart */
    new Chart(document.getElementById('chart-sales'), {
        type: 'line',
        data: {
            labels: d.sales.map(x => x.ym),
            datasets: [{
                label: "Purchases (Last 6 Months)",
                data: d.sales.map(x => x.cnt),
                borderColor: "#0E462E",
                backgroundColor: "rgba(14,70,46,0.12)",
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true }
    });

    /* Availability Chart */
    new Chart(document.getElementById('chart-availability'), {
        type: 'doughnut',
        data: {
            labels: ["Available", "Sold"],
            datasets: [{
                data: [d.available, d.sold],
                backgroundColor: ["#0E462E", "#58CC73"]
            }]
        }
    });
}

loadCharts();
</script>

</body>
</html>





