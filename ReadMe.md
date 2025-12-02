**GDCLIT Tracker**

GDCLIT Tracker is a lightweight PHP/MySQL issue tracking system for internal teams. Staff users report issues with optional attachments; IT users triage and update statuses. The UI is built with PHP templates and vanilla JavaScript for fast, dependency‑free deployments.

---

**Features**

- Login with role‑based access (`staff`, `it`)
- Report issues with attachments (validated types, max 5MB)
- Real‑time dashboards for stats and latest issues
- Commenting on issues with edit/delete windows
- IT workflows: filter, view details, update status with email notifications

---

**Tech Stack**

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx (Apache recommended)
- Vanilla JavaScript and HTML/CSS

---

**Requirements**

- PHP >= 7.4 with `mysqli` and `mail()` configured
- MySQL >= 5.7
- Web server (Apache on Windows/XAMPP or Linux/LAMP)

---

**Quick Start (XAMPP on Windows)**

1. Install XAMPP: https://www.apachefriends.org/
2. Start Apache and MySQL from the XAMPP Control Panel
3. Clone and move the project into `C:\xampp\htdocs\gdclit_tracker`
   
   ```
   git clone https://github.com/elisam1/gdclit_tracker.git
   cd gdclit_tracker
   ```
4. Create the database `gdclit_tracker` in phpMyAdmin: http://localhost/phpmyadmin
5. Configure `db.php` with your credentials (default XAMPP uses user `root` and empty password):
   
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "gdclit_tracker";
   ```
6. Seed users and tables using the schema below
7. Visit `http://localhost/gdclit_tracker` and log in

---

**Database Schema**

Use this schema to create required tables and seed initial users. Passwords can use modern hashes (`password_hash`) or legacy MD5 (login supports both).

```sql
-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  role ENUM('staff','it') NOT NULL,
  password VARCHAR(255) NOT NULL
);

-- Issues
CREATE TABLE IF NOT EXISTS issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(100) NOT NULL,
  priority ENUM('Low','Medium','High') NOT NULL,
  attachment VARCHAR(255) DEFAULT NULL,
  status ENUM('Pending','In Progress','Resolved') NOT NULL DEFAULT 'Pending',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_issues_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Comments
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  issue_id INT NOT NULL,
  user_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_comments_issue FOREIGN KEY (issue_id) REFERENCES issues(id),
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed users (replace <HASHED_PASSWORD> with a password hash)
-- Recommended: PHP bcrypt (password_hash)
INSERT INTO users (name, email, role, password) VALUES
('Alice Staff', 'alice@example.com', 'staff', '<HASHED_PASSWORD>'),
('Ivan IT', 'ivan@example.com', 'it',    '<HASHED_PASSWORD>');

-- If needed, MD5 fallback (less secure)
-- UPDATE users SET password = MD5('yourpassword') WHERE email = 'alice@example.com';
```

Tip: Generate a bcrypt hash in PHP: `password_hash('yourpassword', PASSWORD_BCRYPT)`.

---

**App Navigation**

- `index.php` — login page
- `dashboard.php` — role‑aware dashboard with stats and latest issues
- `report_issue.php` — staff reports new issues with attachments
- `my_issues.php` — staff views reported issues, comments, and can edit/delete own comments
- `manage_issues.php` — IT views all issues, filters, updates status, adds comments
- `logout.php` — end session and redirect to login

---

**Key Behaviors**

- Authentication and roles: query and verification in `index.php` (index.php:10)
- DB connection: `db.php` uses `mysqli_connect` (db.php:7)
- Issue submission: validated inputs and safe uploads to `uploads/` (submit_issue_ajax.php:24)
- Dashboard polling: stats and latest issues via AJAX every few seconds (dashboard.php:152)
- Status updates: IT can update with optional comment and email notification (update_status_ajax.php:20)
- Comments: create/edit/delete with ownership and 15‑minute windows (edit_comment_ajax.php:23, delete_comment_ajax.php:18)

---

**AJAX Endpoints**

- `fetch_issues_ajax.php` — latest issues for role
- `fetch_my_issues_ajax.php` — issues for current staff user
- `fetch_all_issues_ajax.php` — all issues for IT
- `fetch_issue_detail_ajax.php` — issue details + comments
- `add_comment_ajax.php` / `post_comment.php` — add comment
- `edit_comment_ajax.php` — edit own comment within 15 minutes
- `delete_comment_ajax.php` — delete own comment within 15 minutes
- `summary_ajax.php` — stats for dashboard cards
- `update_status_ajax.php` — update status, persist optional comment, send email

---

**Configuration Notes**

- Email: `mail()` must be configured; update sender in `update_status_ajax.php`
- Uploads: attachments stored in `uploads/`; ensure the web user has write permission
- Security: never commit real credentials; use environment‑specific `db.php`

---

**Testing**

- Verify DB connectivity via `test_connection.php`
- Log in using seeded users and exercise flows above

---

**License**

MIT License
