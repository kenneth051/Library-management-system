# Library Management System

A simple PHP and MySQL library management application that runs on XAMPP.

## Requirements

- XAMPP
- PHP
- MySQL
- A web browser

## Setup Instructions

### 1. Import the SQL file

1. Open XAMPP and start **Apache** and **MySQL**.
2. Open phpMyAdmin at:

   ```text
   http://localhost/phpmyadmin
   ```

3. Click **Import**.
4. Choose `database/library_db.sql` from this project.
5. Click **Go**.

This creates the `library_db` database and its tables.

### 2. Place the project in XAMPP

Make sure this project is inside XAMPP's `htdocs` folder:

```text
/Applications/XAMPP/xamppfiles/htdocs/Library-management-system
```

### 3. Check the database connection

Open `config/database.php` and confirm that these settings match your MySQL setup:

```php
$host = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'library_db';
```

The default XAMPP MySQL username is usually `root`, and it has no password.

### 4. Open the project

Start Apache and MySQL in XAMPP, then visit:

```text
http://localhost/Library-management-system/auth/login.php
```

## Main Pages

- Login: `http://localhost/Library-management-system/auth/login.php`
- Sign up: `http://localhost/Library-management-system/auth/sign_up.php`
- Dashboard: `http://localhost/Library-management-system/dashboard/index.php`

## Using the Application

1. Open the sign-up page.
2. Enter a full name, email, password, password confirmation, and staff code.
3. Create an account.
4. You will be redirected to the login page.
5. Log in with the email and password you registered.