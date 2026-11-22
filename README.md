# Library Management System

https://github.com/kenneth051/Library-management-system

Ddumba Kenneth 289-841
SIMON AYUEL KUOL PIOK 198-322
sifa KPADHIGO Evodie ID 277 -719
Abubakar Umar Banye 277-968
Salama Mave Marianna 277-186
Oketayot George William 224-649
Ababiku Brenda 273-318
Zakariye mohamed ali 276- 884

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

## Test Accounts

`database/library_db.sql` creates two accounts you can log in with:

| Role  | Email             | Password      |
|-------|-------------------|---------------|
| Admin | admin@library.com | testadmin123  |
| User  | user@library.com  | testuser123   |

## Main Pages

All pages are under `http://localhost/Library-management-system/`.

| Page | URL | Who can see it |
|------|-----|----------------|
| Login | `auth/login.php` | Everyone |
| Books | `books/index.php` | Admins see every book; users see only the books they have been loaned |
| Add book | `books/create.php` | Admins |
| Dashboard | `dashboard/index.php` | Admins |
| Members | `members/index.php` | Admins |
| Add member | `members/create.php` | Admins |
| Loans | `loans/index.php` | Admins |
| Issue book | `loans/issue.php` | Admins |

Non-admin users are redirected to the Books page if they open an admin-only page.

## Using the Application

1. Log in with one of the test accounts above.
2. As an admin, add books, add members (with a password) and issue books to members from the Loans page.
3. Log in as a member to see the books that have been loaned to them.

## Not Built Yet

These files exist but are still empty: editing books and members, returning a loan, and the Authors and Categories pages. The Edit buttons on the Books and Members lists therefore do not work yet.
