<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success_message = '';

if (($_GET['success'] ?? '') === 'true') {
    $success_message = 'You have successfully created an account. Please login to continue.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name, email, password_hash, is_active, is_admin FROM users WHERE email = ? LIMIT 1');

        if (!$stmt) {
            $error = 'Database error. Please try again later.';
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = 'Invalid email or password.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'Invalid email or password.';
            } elseif ((int) $user['is_active'] !== 1) {
                $error = 'Your account is inactive. Please contact the admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (int) $user['is_admin'] === 1;

                header('Location: ../dashboard/index.php');
                exit();
            }
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
        <p class="fs-5 mt-3">Read &bull; Borrow &bull; Learn</p>
      </div>
      <div class="col-lg-6 right-section">
        <div class="login-heading">
          <h2>Welcome Back!</h2>
          <p class="text-secondary mb-4">Please login to continue</p>
        </div>

        <?php if (!empty($success_message)): ?>
          <div class="alert alert-success auth-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger auth-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="" class="login-form">
            <div class="input-group mb-3">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
</div>
</body>
</html>
