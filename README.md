🎓 Grading System (PHP + MySQL)

A simple web-based Grading System built with PHP and MySQL.
It lets admins manage students, subjects, and grades — all running locally using XAMPP.

🧰 What You Need

XAMPP (for Apache + PHP + MySQL) → Download Here

A browser (Chrome, Firefox, etc.)

(Optional) Git, if you want to clone instead of downloading ZIP.

📥 How to Set Up
1️⃣ Download or Clone the Project

Option 1 — ZIP

Click Code → Download ZIP

Extract the folder

Option 2 — Git

git clone https://github.com/<your-username>/<your-repo>.git


Move the folder into:

C:\xampp\htdocs\GradingSystem

2️⃣ Start XAMPP

Open XAMPP Control Panel

Start Apache and MySQL

3️⃣ Import the Database

Go to http://localhost/phpmyadmin/

Click Databases → Create database → name it grading_system

Open it → Import → Choose File → select database/schema.sql

Click Go

4️⃣ Configure the Connection

Open this file:

config/db.php


Set these values:

$DB_HOST = 'localhost';
$DB_NAME = 'grading_system';
$DB_USER = 'root';
$DB_PASS = '';


Save it.

5️⃣ Run the App

Go to your browser and open:

http://localhost/GradingSystem/

🔐 Default Logins

Admin

Username: admin

Password: Admin123!

Students

Password: Student123!

📂 Folder Overview
Folder	Purpose
admin	Admin dashboard, login/logout
students	Manage students & profiles
subjects	Manage subject info
includes	Shared header/footer files
config	DB & auth setup
assets	CSS & JS files
database	SQL schema file
uploads	File uploads folder
🎨 UI & Scripts

assets/custom.css – Styles (maroon, yellow, white theme)

assets/custom.js – JS features (form validation, alert timer, grade calculator, CSV export)

⚠️ Common Problems

Blank page or error?
→ Add this at the top of index.php:

ini_set('display_errors', 1);
error_reporting(E_ALL);


Can’t log in?
→ Re-import schema.sql and use the default credentials.

Apache won’t start?
→ Something’s using port 80. Stop Skype/IIS or change Apache port in XAMPP config.

🌍 Optional: Upload Online

If you want to host this:

Get free PHP hosting (e.g., 000webhost.com
)

Upload all files

Create a MySQL DB on the host

Import schema.sql

Update config/db.php with the new credentials

👨‍💻 Credits

Made by Patrick John Fajardo
For educational and local testing purposes.
