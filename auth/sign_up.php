<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $staffCode = trim($_POST['staff_code'] ?? '');

    $error = 'This is an error message. Please try again later.';
    $success = '';

    if ($fullname === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');

        if (!$stmt) {
            $error = 'Internal server error. Please try again later.';
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'This email is in use';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $new_user = $conn->prepare('INSERT INTO users (full_name, email, password_hash, staff_code) VALUES (?, ?, ?, ?)');

                if (!$new_user) {
                    $error = 'An error occurred while creating your account.';
                } else {
                    $new_user->bind_param('ssss', $fullname, $email, $passwordHash, $staffCode);

                    if ($new_user->execute()) {
                        $success = 'true';
                        header('Location: login.php?success=' . urlencode($success));
                        exit();
                    } else {
                        $error = 'Unable to create account. Please try again.';
                    }

                    $new_user->close();
                }
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
    <style>
        .container {
            min-height: 100vh;
            max-width: 100%;
            min-width: 100%;
        }
        .left-section {
            background-color: #0a58ca;
            color:white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-form {
            max-width: 70%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-self: center;
            margin: 0 auto;
        }
        .right-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
        }
        .login-heading {
            text-align: center;
            margin-bottom: 30px;
        }
        .new-librarian {
            text-align: center;
            margin-top: 20px;

            a {
                text-decoration: none;
            }
        }
        .bi-book { font-size: 6rem; }
        .auth-message {
          width:70%;
          display:flex;
          justify-content:center;
          align-self:center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
      <div class="col-lg-6 left-section">
        <i class="bi bi-book brand-icon"></i>
        <h1>Library</h1>
        <h1> Management System</h1>
        <p class="fs-5 mt-3">Join the Library team</p>
      </div>
      <div class="col-lg-6 right-section">
        <div class="login-heading">
          <h2>Create Account</h2>
          <p class="text-secondary mb-4">Sign up as a librarian</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger auth-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="" class="login-form">
            <div class="input-group mb-3">
              <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
              <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
              <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
              <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-white"><i class="bi bi-shield"></i></span>
              <input type="password" name="staff_code" class="form-control" placeholder="Staff Code">
            </div>
            <p class="text-secondary mb-4">Ask the head librarian for the staff code. New accounts are created as librarians.</p>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
        <p class="new-librarian">
          <span class="text-secondary">Already have an account? </span><a href="login.php">Login</a>
        </p>
      </div>
    </div>
</div>
</body>
</html>
