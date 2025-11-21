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

        .issue-card { background:white; border-radius:10px; padding:20px; margin-bottom:15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); display:flex; justify-content: space-between; flex-wrap: wrap; transition: transform .2s ease, box-shadow .2s ease; }
        .issue-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,0.12); }
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
        .actions button { background:#007BFF; color:white; border:none; padding:10px 14px; border-radius:6px; cursor:pointer; transition:0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .actions button:hover { background:#0056b3; }

        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:20px; border-radius:12px; max-width:600px; width:90%; position:relative; box-shadow: 0 10px 24px rgba(0,0,0,0.2); max-height: 80vh; overflow-y: auto; border: 1px solid rgba(0,123,255,0.25); }
        .close-btn { position:absolute; top:10px; right:10px; cursor:pointer; font-weight:bold; font-size:18px; }
        .modal-content h3 { background: linear-gradient(135deg, #2962ff, #4e8cff); color:white; padding:10px 14px; border-radius:8px; margin:-8px 0 12px; box-shadow: 0 6px 14px rgba(41,98,255,0.2); }
        .comment-box { margin-top: 15px; }
        .comment-box textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
        .comment-box button { margin-top:10px; background:#28a745; color:white; border:none; padding:10px 14px; border-radius:6px; cursor:pointer; }
        .comment-box button:hover { background:#218838; }

        /* Spinner */
        .spinner { display:none; width: 28px; height: 28px; border: 3px solid #e0e7ff; border-top-color: #2962ff; border-radius: 50%; animation: spin .9s linear infinite; margin: 10px auto; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<header>
    <h1>My Reported Issues</h1>
    <p>Welcome, <?php echo $_SESSION['name']; ?>!</p>
</header>

<main>
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
        <div class="comment-box">
            <h4>Add Comment</h4>
            <textarea id="new-comment" rows="3" placeholder="Write a comment..."></textarea>
            <button onclick="submitStaffComment()">Post Comment</button>
        </div>
    </div>
</div>

<script>
const CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; ?>;
function formatDate(dtStr) {
    if (!dtStr) return '';
    const safe = dtStr.replace(' ', 'T');
    const d = new Date(safe);
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
// --- Fetch My Issues ---
function loadIssues() {
    document.getElementById('listSpinner').style.display = 'block';
    fetch('fetch_my_issues_ajax.php')
    .then(res => res.json())
    .then(data => {
        document.getElementById('listSpinner').style.display = 'none';
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
    document.getElementById('modalSpinner').style.display = 'block';
    fetch('fetch_issue_detail_ajax.php?id='+issueId)
    .then(res => res.json())
    .then(issue => {
        document.getElementById('modalSpinner').style.display = 'none';
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
        // Render comments
        const commentsEl = document.getElementById('modal-comments');
        commentsEl.innerHTML = '';
        if (issue.comments && issue.comments.length > 0) {
            issue.comments.forEach(c => {
                const p = document.createElement('p');
                let html = `<strong>${c.commenter_name} (${formatDate(c.created_at)}):</strong> ${c.comment}`;
                if (c.commenter_id && Number(c.commenter_id) === CURRENT_USER_ID) {
                    html += ` &nbsp; <button style="margin-left:8px;" onclick="editComment(${c.id}, '${c.comment.replace(/'/g, "&#39;")}')">Edit</button>`;
                    html += ` <button style="margin-left:4px;" onclick="deleteComment(${c.id})">Delete</button>`;
                }
                p.innerHTML = html;
                commentsEl.appendChild(p);
            });
        } else {
            commentsEl.innerHTML = '<p>No comments yet.</p>';
        }
        document.getElementById('issue-modal').style.display = 'flex';
    });
}
function closeModal(){ document.getElementById('issue-modal').style.display = 'none'; }

// --- Post Comment ---
function submitStaffComment() {
    const issueId = document.getElementById('modal-id').innerText;
    const comment = document.getElementById('new-comment').value.trim();
    if (!comment) { alert('Please write a comment'); return; }

    const body = 'issue_id='+encodeURIComponent(issueId)+'&comment='+encodeURIComponent(comment);
    fetch('add_comment_ajax.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body
    })
    .then(res => res.json())
    .then(data => {
        if (data && !data.error) {
            const list = document.getElementById('modal-comments');
            const p = document.createElement('p');
            p.innerHTML = `<strong>${data.commenter_name} (${formatDate(data.created_at)}):</strong> ${data.comment}`;
            list.appendChild(p);
            document.getElementById('new-comment').value = '';
        } else {
            alert(data.error || 'Failed to post comment');
        }
    })
    .catch(() => alert('Failed to post comment'));
}

function editComment(commentId, oldText) {
    const updated = prompt('Edit your comment:', oldText);
    if (updated === null) return;
    const text = updated.trim();
    if (!text) { alert('Comment cannot be empty'); return; }
    const body = 'comment_id='+encodeURIComponent(commentId)+'&comment='+encodeURIComponent(text);
    fetch('edit_comment_ajax.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
    .then(r=>r.json())
    .then(resp=>{
        if (resp.status === 'success') {
            // Refresh comments
            openModal(document.getElementById('modal-id').innerText);
        } else {
            alert(resp.error || 'Failed to edit comment');
        }
    })
    .catch(()=> alert('Failed to edit comment'));
}

function deleteComment(commentId) {
    if (!confirm('Delete this comment?')) return;
    const body = 'comment_id='+encodeURIComponent(commentId);
    fetch('delete_comment_ajax.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
    .then(r=>r.json())
    .then(resp=>{
        if (resp.status === 'success') {
            // Refresh comments
            openModal(document.getElementById('modal-id').innerText);
        } else {
            alert(resp.error || 'Failed to delete comment');
        }
    })
    .catch(()=> alert('Failed to delete comment'));
}

// --- Initial Load + Polling ---
loadIssues();
setInterval(loadIssues,3000);
</script>

</body>
</html>
