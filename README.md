<h1>Grading System (PHP + MySQL)<h1>

A lightweight grading system with Admin and Student modules, Subjects management, and CSV Import/Export tools. Runs locally via XAMPP (Apache + MySQL).

📁 Project Structure
/admin
  index.php
  login.php
  logout.php

/students
  add.php
  edit.php
  delete.php
  index.php
  view.php

/subjects
  add.php
  edit.php
  delete.php
  index.php

/includes
  admin_header.php
  student_header.php
  footer.php
  (plus: dashboard.php, login.php, logout.php if present)

/config
  auth.php
  config.php
  db.php

/assets
  custom.css
  custom.js

/database
  schema.sql

/uploads
  .htaccess (see note below)
  
/ (project root)
  index.php
  manage.php
  import.php
  export.php
  logout.php


Styling/scripts live in assets/custom.css and assets/custom.js.
Database schema & seed data: database/schema.sql.

🧰 Requirements

XAMPP 8.x (includes Apache, PHP, MySQL) – Windows/macOS/Linux

Browser: Chrome/Firefox/Edge

(Optional) Git

XAMPP defaults

MySQL user: root

MySQL password: (empty)

Host: localhost

⬇️ Get the Code

Option A — Clone

cd ~
git clone https://github.com/<your-username>/<your-repo>.git


Option B — ZIP

On GitHub: Code → Download ZIP

Extract locally

Move the project into XAMPP web root:

Windows: C:\xampp\htdocs\GradingSystem

macOS: /Applications/XAMPP/htdocs/GradingSystem

Linux: /opt/lampp/htdocs/GradingSystem

(You can rename the folder; remember it for the URL below.)

▶️ Start XAMPP

Open XAMPP Control Panel

Click Start for Apache and MySQL

Open phpMyAdmin: http://localhost/phpmyadmin/

🗄️ Create & Import the Database

In phpMyAdmin → Databases → Create, name it: grading_system

Click the new DB → Import

Choose /database/schema.sql → Go

What schema.sql creates (summary):

Tables: admins, students, semesters, subjects, grades, audit_logs

Sample data for admins, semesters, students, subjects

Grade calculation (30% prelim, 30% midterm, 40% final) with letter/remarks updates

The script uses grading_system and seeds default rows (you can edit later).

⚙️ Configure the App

Open /config/db.php (or config.php if that’s your actual connection file) and set your local DB credentials:

<?php
$DB_HOST = 'localhost';
$DB_NAME = 'grading_system';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default: empty

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('MySQL connection failed: ' . $mysqli->connect_error);
}


If your app needs a base URL for redirects:

$BASE_URL = 'http://localhost/GradingSystem/';


Check config/auth.php if there are session/role settings to adjust.

🌐 Run the App

Open your browser and go to:

http://localhost/GradingSystem/


Typical flow:

Admin logs in via /admin/login.php (or /admin/ if it redirects)

Manage students, subjects, and grades

Use import.php/export.php for CSV workflows (if included in your root)

🔐 Default Logins (from seed data)

Admins

Username: admin — Password: Admin123!

Username: principal — Password: Admin123!

Students

Example students are seeded (e.g., 2024-0001, 2024-0002, etc.).
If your app uses student login by email/ID, the seeded password is: Student123!

After first login, change passwords in your DB or via any available UI.

🧭 Modules Overview

Admin Module (/admin)

Login/Logout

Dashboard, manage Students/Subjects

Grades overview & actions (depending on your UI)

Students Module (/students)

CRUD for student profiles (add/edit/delete/view)

Subjects Module (/subjects)

CRUD for subjects (add/edit/delete/list)

Utilities

/import.php — import (e.g., CSV → database)

/export.php — export table data to CSV

Shared layout/partials in /includes (e.g., headers & footer).

🖼️ Assets & UI

/assets/custom.css — project theme (maroon/yellow/white), Bootstrap-friendly overrides

/assets/custom.js — UI helpers:

Auto-hide alerts (5s)

Delete confirmation

Client-side form validation

Grade calculator (prelim/midterm/final → final grade, letter, remarks)

CSV export helper

Toast utility

Basic loading state helper

📤 Uploads

Files go under /uploads/

Ensure the folder is writable by the web server

Windows: usually fine inside htdocs

macOS/Linux: chmod -R 755 uploads (or 775/777 for local dev if needed)

Note: You included a file named .htacess (missing one “c”). If you intended to use Apache rules, rename to .htaccess. If it’s for download protection (e.g., deny from all), add rules accordingly and make sure Apache AllowOverride is enabled for your vhost (XAMPP has it on by default).

🧪 Quick Test Checklist

✅ Apache + MySQL running

✅ grading_system DB exists

✅ schema.sql imported without errors

✅ config/db.php matches your DB name & credentials

✅ Visit /admin/login.php → login as admin / Admin123!

✅ Add a subject, add a student, add/edit some grades

✅ Try export to CSV and import (if UI exists)

🚑 Troubleshooting

Blank page / HTTP 500

// Add to index.php (top) temporarily for debugging:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


MySQL “Access denied (1045)”

Use XAMPP defaults: user root, no password

Make sure DB name in db.php is exactly grading_system

Confirm MySQL service is running

SQL import fails / large file

In php.ini, bump:

upload_max_filesize

post_max_size

max_execution_time

Restart Apache after changes

Apache won’t start (port conflict)

Stop IIS/Skype/VMware etc.

Or change Apache ports in XAMPP → Apache Config → httpd.conf (Listen 80) and httpd-ssl.conf (Listen 443)

GitHub Pages shows 404

Pages doesn’t run PHP. Use XAMPP locally or deploy to a PHP host.

🌍 Deploying Online (Optional)

Use a PHP host that supports MySQL:

Free: 000webhost, InfinityFree

Paid: Hostinger, Namecheap, GoDaddy

Steps:

Upload project files to /public_html (or the host’s web root)

Create a MySQL database in hosting control panel

Import database/schema.sql via host’s phpMyAdmin

Update /config/db.php with host credentials

If using .htaccess, ensure AllowOverride is on and rules are supported

🤝 Contributing

Fork the repo

Create a feature branch

Commit with clear messages

Open a Pull Request
