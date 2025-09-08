# 📚 School Management System

[![PHP](https://img.shields.io/badge/PHP-8.2-blue)](https://www.php.net/) 
[![MySQL](https://img.shields.io/badge/MySQL-8-green)](https://www.mysql.com/) 
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)  
[![GitHub stars](https://img.shields.io/github/stars/your-username/School-Management-System?style=social)](https://github.com/your-username/School-Management-System/stargazers)

A **powerful, role-based platform** to streamline school operations. Effortlessly manage administrative, academic, and financial workflows with tailored access for **Admin, Teacher, Sub-Teacher, Student, and Staff** (e.g., Cashier, Clerk). Built for **real-world efficiency**.  


## ✨ Core Features

Streamline school operations with these robust features:

- 🔐 **Role-Based Access Control:** Custom permissions for Admin, Teacher, Sub-Teacher, Student, and Staff.  
- 🧑‍🎓 **User Onboarding:** Assign classes, subjects, or roles (e.g., Cashier, Clerk).  
- 📅 **Attendance Management:** Track attendance by class or subject.  
- 📝 **Exams & Results:** Create exams, record grades, and view results.  
- 📚 **Materials & Resources:** Share class or subject-specific resources.  
- 💸 **Fee Management:** Process payments and maintain transaction history.  
- 📢 **Notices & Messaging:** Broadcast announcements and manage inbox/outbox.  
- 🚫 **Blocking & Moderation:** Block users with stored reasons for transparency.  
- 📊 **Dashboards:** Role-specific dashboards with widgets for stats, results, events, and insights.  

💡 **Tip:** Dashboards provide quick insights tailored to each role, enhancing productivity.

**Tech Tags:** Authentication | Class & Subject Mapping | Timetables | Payments | Events Calendar | Reporting


## 🧩 Tech Stack

- **Backend:** PHP 8+  
- **Database:** MySQL 8+ / MariaDB 10.4+ (via XAMPP/LAMPP)  
- **Frontend:** HTML5, CSS3, JavaScript  
- **Server:** Apache (XAMPP)  
- **APIs:** REST-ish endpoints with PDO/MySQLi and session-based authentication  

**Tech Tags:** PDO/MySQLi | Session Auth | REST-ish Endpoints


## 👥 User Roles & Permissions

| Role         | Core Capabilities |
|--------------|-----------------|
| **Admin** 🌟 | Full control: Manage users, assign classes/subjects/roles, block/unblock users, manage timetables, notices, and settings. |
| **Teacher** 📚 | Manage assigned classes/subjects, mark attendance, upload materials, create exams, record grades, and track student progress. |
| **Sub-Teacher** 🧑‍🏫 | Same academic permissions as Teacher, assisting specific classes/subjects. |
| **Staff** 💼 | Task-based access (e.g., Cashier handles payments, Clerk manages admissions). |
| **Student** 👨‍🎓 | View materials, timetables, results, notices, and submit feedback where applicable. |


## 🖼️ Screenshots

Showcase your system by adding screenshots to `assets/screens/`:

- 🖥️ Admin Dashboard with widgets  
- 📋 Teacher Attendance & Materials  
- 💰 Cashier Fee Management  
- 📊 Student Results View  

**Example Paths:**  
`assets/screens/admin-dashboard.png` | `assets/screens/attendance.png` | `assets/screens/fees.png`  

🎨 **Tip:** High-quality screenshots attract contributors and showcase functionality!


## 🚀 Getting Started (Local Setup)

Set up the project locally with XAMPP:

1. Install **XAMPP** and start **Apache** and **MySQL**.  
2. Clone or copy the project to `htdocs/School-Management-System`.  
3. Create a database (e.g., `sms_db`) in **phpMyAdmin**.  
4. Import the SQL schema (e.g., `reliance(1).sql` or `tms.sql`).  
5. Configure database credentials in `database_connection.php`:

php
$host = '127.0.0.1';
$db   = 'sms_db';
$user = 'root';
$pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

## 🚀 Getting Started (Local Setup)

Visit [http://localhost/School-Management-System/login.php](http://localhost/SMS) to log in.

**Requirements:**  
PHP 8+ | MySQL 8+ / MariaDB 10.4+ | Apache  


## 📁 Suggested Folder Structure

/School-Management-System
├── assets/
│ ├── css/ # Global styles
│ ├── js/ # Scripts
│ └── screens/ # README screenshots
├── Teachers/ # Teacher dashboard & pages
├── Students/ # Student portal
├── Staffs/ # Staff portal (Cashier, Clerk, etc.)
├── Resources/ # Materials and uploads
├── Sub Teacher/ # Sub-Teacher views
├── Cashier/ # Fees & payment pages
├── database_connection.php # Database config
├── login.php # Login page
├── Admin_Dashboard.php # Admin dashboard
└── ... # Other modules

## 🔐 Security Notes

- 🛡️ **Prepared Statements:** Use PDO/MySQLi to prevent SQL injection.  
- 🔒 **Password Hashing:** Implement `password_hash()` in PHP.  
- 🚪 **Route Protection:** Secure role-based routes with session middleware.  
- 📂 **File Validation:** Validate uploaded files and set proper permissions.  
- 📜 **Audit Logs:** Store block reasons and moderation events.
