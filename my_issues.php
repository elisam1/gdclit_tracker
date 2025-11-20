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
    <title>My Reported Issues</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: #f4f6f8; margin:0; padding:0; }
        header { background: #007BFF; color:white; padding:20px; text-align:center; }
        main { max-width: 1000px; margin: 20px auto; padding:0 20px; }
        h2 { color:#333; margin-bottom:20px; }

        .issue-card { background:white; border-radius:8px; padding:20px; margin-bottom:15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05); display:flex; justify-content: space-between; flex-wrap: wrap; }
        .issue-info { flex: 1 1 60%; }
        .issue-info p { margin:5px 0; }
        .badge { padding:5px 10px; border-radius:20px; font-weight:500; color:white; font-size:13px; }
        .priority-Low { background: #28a745; }
        .priority-Medium { background: #ffc107; color:#212529; }
        .priority-High { background: #dc3545; }
        .status-Pending { background: #fd7e14; }
        .status-In\ Progress { background: #17a2b8; }
        .status-Resolved { background: #28a745; }

        .actions { flex: 1 1 35%; text-align:right; }
        .actions button { background:#007BFF; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; transition:0.3s; }
        .actions button:hover { background:#0056b3; }

        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:20px; border-radius:8px; max-width:600px; width:90%; position:relative; }
        .close-btn { position:absolute; top:10px; right:10px; cursor:pointer; font-weight:bold; font-size:18px; }
    </style>
</head>
<body>

<header>
    <h1>My Reported Issues</h1>
    <p>Welcome, <?php echo $_SESSION['name']; ?>!</p>
</header>

<main>
    <div id="issues-container"></div>
</main>

<!-- Modal -->
<div class="modal" id="issue-modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Issue Details</h3>
        <p><strong>ID:</strong> <span id="modal-id"></span></p>
        <p><strong>Title:</strong> <span id="modal-title"></span></p>
        <p><strong>Category:</strong> <span id="modal-category"></span></p>
        <p><strong>Priority:</strong> <span id="modal-priority"></span></p>
        <p><strong>Status:</strong> <span id="modal-status"></span></p>
        <p><strong>Description:</strong></p>
        <p id="modal-description"></p>
        <p id="modal-attachment"></p>
    </div>
</div>

<script>
// --- Fetch My Issues ---
function loadIssues() {
    fetch('fetch_my_issues_ajax.php')
    .then(res => res.json())
    .then(data => {
        let container = document.getElementById('issues-container');
        container.innerHTML = '';
        data.forEach(issue => {
            let card = document.createElement('div');
            card.className = 'issue-card';
            card.innerHTML = `
                <div class="issue-info">
                    <p><strong>ID:</strong> ${issue.id}</p>
                    <p><strong>Title:</strong> ${issue.title}</p>
                    <p><strong>Category:</strong> ${issue.category}</p>
                    <p><strong>Priority:</strong> <span class="badge priority-${issue.priority}">${issue.priority}</span></p>
                    <p><strong>Status:</strong> <span class="badge status-${issue.status.replace(' ','\\ ')}">${issue.status}</span></p>
                    <p><strong>Created:</strong> ${issue.created_at}</p>
                </div>
                <div class="actions">
                    <button onclick="openModal(${issue.id})">View</button>
                </div>
            `;
            container.appendChild(card);
        });
    });
}

// --- Modal ---
function openModal(issueId){
    fetch('fetch_issue_detail_ajax.php?id='+issueId)
    .then(res => res.json())
    .then(issue => {
        document.getElementById('modal-id').innerText = issue.id;
        document.getElementById('modal-title').innerText = issue.title;
        document.getElementById('modal-category').innerText = issue.category;
        document.getElementById('modal-priority').innerText = issue.priority;
        document.getElementById('modal-status').innerText = issue.status;
        document.getElementById('modal-description').innerText = issue.description;
        if(issue.attachment) {
            document.getElementById('modal-attachment').innerHTML = `<strong>Attachment:</strong> <a href="uploads/${issue.attachment}" target="_blank">${issue.attachment}</a>`;
        } else {
            document.getElementById('modal-attachment').innerHTML = '';
        }
        document.getElementById('issue-modal').style.display = 'flex';
    });
}
function closeModal(){ document.getElementById('issue-modal').style.display = 'none'; }

// --- Initial Load + Polling ---
loadIssues();
setInterval(loadIssues,3000);
</script>

</body>
</html>
