<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - Report Issue</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background: #f4f6f8;
        }
        header {
            background: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        h2 {
            color: #333;
        }

        /* Form Styles */
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .card label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .card input, .card textarea, .card select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .card button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .card button:hover {
            background: #218838;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background: #007BFF;
            color: white;
            font-weight: 500;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* Status badges */
        .status-Pending { background: #ffc107; color: white; padding: 5px 10px; border-radius: 20px; font-weight: 500; }
        .status-In\ Progress { background: #17a2b8; color: white; padding: 5px 10px; border-radius: 20px; font-weight: 500; }
        .status-Resolved { background: #28a745; color: white; padding: 5px 10px; border-radius: 20px; font-weight: 500; }

    </style>
</head>
<body>

<header>
    <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
    <p>Role: <strong><?php echo $_SESSION['role']; ?></strong></p>
</header>

<main>
    <!-- Report Issue Form -->
    <div class="card">
        <h2>Report a New Issue</h2>
        <form id="issue-form" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" required>

            <label>Description</label>
            <textarea name="description" rows="4" required></textarea>

            <label>Category</label>
            <input type="text" name="category" required>

            <label>Priority</label>
            <select name="priority" required>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>

            <label>Attachment (optional)</label>
            <input type="file" name="attachment">

            <button type="submit">Submit Issue</button>
        </form>
    </div>

    <!-- My Reported Issues Table -->
    <div class="card">
        <h2>My Reported Issues</h2>
        <table id="issues-table">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Last Updated</th>
            </tr>
        </table>
    </div>
</main>

<script>
function loadIssues() {
    fetch('fetch_my_issues_ajax.php')
    .then(res => res.json())
    .then(data => {
        let table = document.getElementById('issues-table');
        table.querySelectorAll('tr:not(:first-child)').forEach(r => r.remove());

        data.forEach(issue => {
            let row = table.insertRow();
            row.innerHTML = `
                <td>${issue.id}</td>
                <td>${issue.title}</td>
                <td>${issue.category}</td>
                <td>${issue.priority}</td>
                <td class="status-${issue.status.replace(' ', '\\ ')}">${issue.status}</td>
                <td>${issue.created_at}</td>
                <td>${issue.updated_at}</td>
            `;
        });
    });
}

// Initial load
loadIssues();
setInterval(loadIssues, 3000);

// Handle form submission
document.getElementById('issue-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch('submit_issue_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            this.reset();
            loadIssues();
            alert('Issue submitted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    });
});
</script>

</body>
</html>
