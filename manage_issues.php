<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'it') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IT Dashboard - Manage Issues</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* --- Global Styles --- */
        body { font-family: 'Roboto', sans-serif; background: #f4f6f8; margin:0; padding:0; }
        header { background: #007BFF; color: white; padding: 20px; text-align: center; }
        main { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        h2 { color: #333; margin-bottom: 20px; }

        /* --- Filter Bar --- */
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters select { padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; }
        .filters button { background: #007BFF; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .filters button:hover { background: #0056b3; }

        /* --- Issue Card --- */
        .issue-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); display:flex; justify-content: space-between; flex-wrap: wrap; position: relative; transition: transform .2s ease, box-shadow .2s ease; }
        .issue-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,0.12); }
        .issue-info { flex: 1 1 60%; }
        .issue-info p { margin: 5px 0; }
        .badge { padding: 5px 10px; border-radius: 20px; font-weight: 500; color:white; font-size:13px; }
        .priority-Low { background: #28a745; }
        .priority-Medium { background: #ffc107; color:#212529; }
        .priority-High { background: #dc3545; }
        .status-Pending { background: #fd7e14; }
        .status-In\ Progress { background: #17a2b8; }
        .status-Resolved { background: #28a745; }

        .actions { flex: 1 1 35%; text-align:right; }
        .actions select, .actions button { padding: 8px 12px; margin-left:5px; border-radius:5px; border:1px solid #ccc; font-size:14px; }
        .actions button { background: #007BFF; color:white; border:none; cursor:pointer; transition:0.3s; }
        .actions button:hover { background:#0056b3; }

        /* --- Modal --- */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:20px; border-radius:12px; max-width:600px; width:90%; position:relative; box-shadow: 0 10px 24px rgba(0,0,0,0.2); max-height: 80vh; overflow-y: auto; border: 1px solid rgba(0,123,255,0.25); }
        .close-btn { position:absolute; top:10px; right:10px; cursor:pointer; font-weight:bold; font-size:18px; }
        .modal-content h3 { background: linear-gradient(135deg, #2962ff, #4e8cff); color:white; padding:10px 14px; border-radius:8px; margin:-8px 0 12px; box-shadow: 0 6px 14px rgba(41,98,255,0.2); }

        /* --- Notification --- */
        .notification { position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 20px; border-radius: 5px; display: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2); animation: slidein 0.5s forwards; }
        @keyframes slidein { from { opacity:0; transform: translateX(100%); } to { opacity:1; transform: translateX(0); } }
        /* Spinner */
        .spinner { display:none; width: 28px; height: 28px; border: 3px solid #e0e7ff; border-top-color: #2962ff; border-radius: 50%; animation: spin .9s linear infinite; margin: 10px auto; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<header>
    <h1>IT Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['name']; ?>!</p>
</header>

<main>
    <h2>All Reported Issues</h2>

    <!-- Quick Filters -->
    <div class="filters">
        <select id="filter-status">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
        </select>
        <select id="filter-priority">
            <option value="">All Priority</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
        </select>
        <button onclick="loadIssues()">Apply Filters</button>
    </div>

    <!-- Issues Container -->
    <div id="listSpinner" class="spinner"></div>
    <div id="issues-container"></div>
</main>

<!-- Modal -->
<div class="modal" id="issue-modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Issue Details</h3>
        <div id="modalSpinner" class="spinner"></div>
        <p><strong>ID:</strong> <span id="modal-id"></span></p>
        <p><strong>Reporter:</strong> <span id="modal-reporter"></span></p>
        <p><strong>Title:</strong> <span id="modal-title"></span></p>
        <p><strong>Category:</strong> <span id="modal-category"></span></p>
        <p><strong>Priority:</strong> <span id="modal-priority"></span></p>
        <p><strong>Status:</strong> <span id="modal-status"></span></p>
        <p><strong>Description:</strong></p>
        <p id="modal-description"></p>
        <p id="modal-attachment"></p>
        <hr>
        <h4>Comments</h4>
        <div id="modal-comments"></div>
</div>
</div>

<!-- Update Modal -->
<div class="modal" id="update-modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeUpdateModal()">&times;</span>
        <h3>Update Issue Status</h3>
        <div id="updateSpinner" class="spinner"></div>
        <p><strong>ID:</strong> <span id="update-issue-id"></span></p>
        <label for="update-status">Status</label>
        <select id="update-status">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
        </select>
        <label for="update-comment">Comment (optional)</label>
        <textarea id="update-comment" rows="4" placeholder="Add a comment for the reporter"></textarea>
        <button onclick="submitUpdate()">Save</button>
    </div>
</div>

<div class="notification" id="notification"></div>

<script>
function formatDate(dtStr) {
    if (!dtStr) return '';
    const safe = dtStr.replace(' ', 'T');
    const d = new Date(safe);
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
// --- Fetch and Render Issues ---
let lastIssueCount = 0;
function loadIssues() {
    let statusFilter = document.getElementById('filter-status').value;
    let priorityFilter = document.getElementById('filter-priority').value;
    document.getElementById('listSpinner').style.display = 'block';
    fetch('fetch_all_issues_ajax.php')
    .then(res => res.json())
    .then(data => {
        document.getElementById('listSpinner').style.display = 'none';
        if(data.length > lastIssueCount) {
            showNotification('New issue reported!');
        }
        lastIssueCount = data.length;

        if(statusFilter) data = data.filter(i => i.status == statusFilter);
        if(priorityFilter) data = data.filter(i => i.priority == priorityFilter);

        let container = document.getElementById('issues-container');
        container.innerHTML = '';
        data.forEach(issue => {
            let card = document.createElement('div');
            card.className = 'issue-card';
            card.innerHTML = `
                <div class="issue-info">
                    <p><strong>ID:</strong> ${issue.id}</p>
                    <p><strong>Reporter:</strong> ${issue.reporter_name}</p>
                    <p><strong>Title:</strong> ${issue.title}</p>
                    <p><strong>Category:</strong> ${issue.category}</p>
                    <p><strong>Priority:</strong> <span class="badge priority-${issue.priority}">${issue.priority}</span></p>
                    <p><strong>Status:</strong> <span class="badge status-${issue.status.replace(' ', '\\ ')}" id="status-text-${issue.id}">${issue.status}</span></p>
                    <p><strong>Created:</strong> ${issue.created_at}</p>
                </div>
                <div class="actions">
                    <button class="btn btn-update" onclick="openUpdateModal(${issue.id}, '${issue.status}')">Update</button>
                    <button class="btn btn-view" onclick="openModal(${issue.id})">View</button>
                </div>
            `;
            container.appendChild(card);
        });
    });
}

// --- AJAX Status Update ---
function openUpdateModal(issueId, currentStatus) {
    document.getElementById('update-issue-id').innerText = issueId;
    document.getElementById('update-status').value = currentStatus;
    document.getElementById('update-comment').value = '';
    document.getElementById('update-modal').style.display = 'flex';
}
function closeUpdateModal() { document.getElementById('update-modal').style.display = 'none'; }

function submitUpdate() {
    document.getElementById('updateSpinner').style.display = 'block';
    const issueId = parseInt(document.getElementById('update-issue-id').innerText, 10);
    const status = document.getElementById('update-status').value;
    const comment = document.getElementById('update-comment').value.trim();

    const body = 'issue_id='+issueId+'&status='+encodeURIComponent(status)+'&comment='+encodeURIComponent(comment);

    fetch('update_status_ajax.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('updateSpinner').style.display = 'none';
        if (data === 'success') {
            document.getElementById('status-text-'+issueId).innerText = status;
            showNotification('Status updated!');
            closeUpdateModal();
        } else {
            alert('Error updating status');
        }
    })
    .catch(() => alert('Error updating status'));
}

// --- Modal ---
function openModal(issueId) {
    document.getElementById('modalSpinner').style.display = 'block';
    fetch('fetch_issue_detail_ajax.php?id='+issueId)
    .then(res => res.json())
    .then(issue => {
        document.getElementById('modalSpinner').style.display = 'none';
        document.getElementById('modal-id').innerText = issue.id;
        document.getElementById('modal-reporter').innerText = issue.reporter_name;
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
        // Load comments
        let commentsHtml = '';
        if(issue.comments && issue.comments.length > 0) {
            issue.comments.forEach(comment => {
                commentsHtml += `<p><strong>${comment.commenter_name} (${formatDate(comment.created_at)}):</strong> ${comment.comment}</p>`;
            });
        } else {
            commentsHtml = '<p>No comments yet.</p>';
        }
        document.getElementById('modal-comments').innerHTML = commentsHtml;
        document.getElementById('issue-modal').style.display = 'flex';
    });
}
function closeModal() { document.getElementById('issue-modal').style.display = 'none'; }

// --- Notification ---
function showNotification(msg) {
    let n = document.getElementById('notification');
    n.innerText = msg;
    n.style.display = 'block';
    setTimeout(()=>{n.style.display='none';},3000);
}

// --- Initial Load + Polling ---
loadIssues();
setInterval(loadIssues,3000);
</script>

</body>
</html>
