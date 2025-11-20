<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Assign session variables
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family:'Roboto',sans-serif;
            margin:0;
            background:#f4f6f8;
        }
        header {
            background:#007BFF;
            color:white;
            padding:20px;
            text-align:center;
        }
        main {
            max-width:1200px;
            margin:20px auto;
            padding:0 20px;
        }
        h2,h3 {
            color:#333;
        }
        .stats {
            display:flex;
            gap:20px;
            flex-wrap:wrap;
            margin-bottom:30px;
        }
        .card {
            flex:1;
            min-width:200px;
            padding:20px;
            border-radius:10px;
            color:white;
            text-align:center;
            box-shadow:0 4px 12px rgba(0,0,0,0.1);
        }
        .card h4 { margin:0 0 10px; font-weight:500; }
        .card p { font-size:24px; margin:0; font-weight:bold; }
        .pending { background:#ffc107; }
        .in-progress { background:#17a2b8; }
        .resolved { background:#28a745; }

        .links a {
            display:inline-block;
            margin:10px 10px 0 0;
            padding:10px 20px;
            background:#007BFF;
            color:white;
            text-decoration:none;
            border-radius:5px;
            transition:0.3s;
        }
        .links a:hover { background:#0056b3; }

        /* Table Styles */
        table {
            width:100%;
            border-collapse:collapse;
            background:white;
            border-radius:8px;
            overflow:hidden;
            box-shadow:0 4px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding:12px;
            text-align:left;
            border-bottom:1px solid #ddd;
        }
        th {
            background:#007BFF;
            color:white;
        }
        tr:nth-child(even) { background:#f9f9f9; }

        .status-Pending { background:#ffc107; color:white; padding:5px 10px; border-radius:20px; font-weight:500; }
        .status-In\ Progress { background:#17a2b8; color:white; padding:5px 10px; border-radius:20px; font-weight:500; }
        .status-Resolved { background:#28a745; color:white; padding:5px 10px; border-radius:20px; font-weight:500; }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
</header>

<main>
    <h3>Issue Stats</h3>
    <div class="stats">
        <div class="card pending">
            <h4>Pending</h4>
            <p>0</p>
        </div>
        <div class="card in-progress">
            <h4>In Progress</h4>
            <p>0</p>
        </div>
        <div class="card resolved">
            <h4>Resolved</h4>
            <p>0</p>
        </div>
    </div>

    <h3>Latest Issues</h3>
    <table id="issues-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Reporter</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="links">
        <a href="report_issue.php">Report an Issue</a>
        <?php if ($role == 'staff') { ?>
            <a href="my_issues.php">View My Reported Issues</a>
        <?php } ?>
        <?php if ($role == 'it') { ?>
            <a href="manage_issues.php">Manage Issues</a>
        <?php } ?>
        <a href="logout.php">Logout</a>
    </div>
</main>

<script>
// Load stats
function loadStats() {
    fetch('summary_ajax.php')
    .then(res => res.json())
    .then(data => {
        document.querySelector('.card.pending p').innerText = data.pending;
        document.querySelector('.card.in-progress p').innerText = data.in_progress;
        document.querySelector('.card.resolved p').innerText = data.resolved;
    });
}

// Load latest issues
function loadIssues() {
    fetch('fetch_issues_ajax.php')
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector('#issues-table tbody');
        tbody.innerHTML = '';

        data.forEach(issue => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${issue.id}</td>
                <td>${issue.reporter_name}</td>
                <td>${issue.title}</td>
                <td>${issue.category}</td>
                <td>${issue.priority}</td>
                <td class="status-${issue.status.replace(' ', '\\ ')}">${issue.status}</td>
                <td>${issue.created_at}</td>
            `;
            tbody.appendChild(tr);
        });
    });
}

// Initial load
loadStats();
loadIssues();

// Refresh every 5 seconds
setInterval(() => {
    loadStats();
    loadIssues();
}, 5000);
</script>

</body>
</html>
