<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if ((int)($_SESSION['is_admin'] ?? 0) !== 1) { header('Location: ../books/index.php'); exit(); }
$activePage = 'Members';
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';
$errorField = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $active   = ($_POST['status'] ?? 'active') === 'suspended' ? 0 : 1;
    $joined   = trim($_POST['date_joined'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
        $errorField = 'email';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
        $errorField = 'password';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $createdAt = ($joined === '' ? date('Y-m-d') : $joined) . ' 00:00:00';
        try {
            $stmt = $conn->prepare(
                'INSERT INTO users (email, password_hash, full_name, is_active, is_admin, created_at)
                 VALUES (?, ?, ?, ?, 0, ?)'
            );
            $stmt->bind_param('sssis', $email, $hash, $name, $active, $createdAt);
            $stmt->execute();
            $message = 'Member added successfully.';
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $errorField = 'email';
                $error = 'A member with that email already exists.';
            } else {
                $error = 'Could not save the member.';
            }
        }
    }
}

$val = function ($key, $default = '') use ($message) {
    return htmlspecialchars($message === '' ? ($_POST[$key] ?? $default) : $default);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Add member</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    .main-content { min-height: 100vh; background: #eef1f8; }
  </style>
</head>
<body>
<div class="row">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-lg-10 main-content">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h2 class="fw-bold mb-0">Add member</h2>
      <p class="text-secondary">Register a new library member</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">Back to members</a>
  </div>

  <?php if ($message !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error !== '' && $errorField === ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="post" class="row g-3">
         <div class="col-md-4">
           <label class="form-label fw-semibold">Full name</label>
           <input type="text" name="full_name" class="form-control" value="<?= $val('full_name') ?>" required>
        </div>
        <div class="col-md-4">
           <label class="form-label fw-semibold">Email</label>
           <input type="email" name="email" class="form-control <?= $errorField === 'email' ? 'is-invalid' : '' ?>" value="<?= $val('email') ?>" required>
           <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
          <div class="form-text">Must be unique.</div>
        </div>
        <div class="col-md-4">
           <label class="form-label fw-semibold">Password</label>
          <input type="password" name="password" class="form-control <?= $errorField === 'password' ? 'is-invalid' : '' ?>" required>
             <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
          <div class="form-text">At least 8 characters.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
              <option value="active" <?= $val('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
             <option value="suspended" <?= $val('status') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
          </select>
          <div class="form-text">Suspended members cannot borrow books.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Date joined</label>
          <input type="date" name="date_joined" class="form-control" value="<?= $val('date_joined', date('Y-m-d')) ?>" required>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save member</button>
          <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <p class="text-center text-secondary small mt-4 mb-0">Library Management System · Database II CAT 2</p>
</div>
</div>
</body>
</html>
