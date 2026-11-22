<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if ((int)($_SESSION['is_admin'] ?? 0) !== 1) { header('Location: ../books/index.php'); exit(); }
$activePage = 'Loans';
require_once __DIR__ . '/../config/database.php';

const LOAN_DAYS = 14;

$message = '';
$error = '';

$members = $conn->query('SELECT id, full_name, email FROM users WHERE is_admin = 0 AND is_active = 1 ORDER BY full_name')->fetch_all(MYSQLI_ASSOC);
$books = $conn->query('SELECT id, title, available_copies FROM books ORDER BY title')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int)($_POST['user_id'] ?? 0);
    $bookId   = (int)($_POST['book_id'] ?? 0);
    $issued   = trim($_POST['loan_date'] ?? '');

    $date = DateTime::createFromFormat('Y-m-d', $issued);
    $validDate = $date && $date->format('Y-m-d') === $issued && $issued <= date('Y-m-d');

    if ($memberId < 1 || $bookId < 1) {
        $error = 'Choose a member and a book.';
    } elseif (!$validDate) {
        $error = 'Enter a valid date issued (not in the future).';
    } else {
        $due = (clone $date)->modify('+' . LOAN_DAYS . ' days')->format('Y-m-d');
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare('SELECT title, available_copies FROM books WHERE id = ? FOR UPDATE');
            $stmt->bind_param('i', $bookId);
            $stmt->execute();
             $book = $stmt->get_result()->fetch_assoc();

             $stmt = $conn->prepare('SELECT id FROM users WHERE id = ? AND is_admin = 0 AND is_active = 1');
            $stmt->bind_param('i', $memberId);
            $stmt->execute();
          $member = $stmt->get_result()->fetch_assoc();

            if (!$book || !$member) {
                $conn->rollback();
                $error = 'The selected member or book was not found, or the member is not active.';
            } elseif ($book['available_copies'] < 1) {
                $conn->rollback();
                $error = 'This book cannot be issued: ' . $book['title'] . ' has 0 copies available.';
            } else {
                 $adminId = (int)$_SESSION['user_id'];
                $stmt = $conn->prepare(
                    'INSERT INTO book_loans (user_id, book_id, issued_by, loan_date, due_date, status)
                     VALUES (?, ?, ?, ?, ?, \'borrowed\')'
                );
                 $stmt->bind_param('iiiss', $memberId, $bookId, $adminId, $issued, $due);
                $stmt->execute();

                 $stmt = $conn->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE id = ?');
                    $stmt->bind_param('i', $bookId);
                    $stmt->execute();

                $conn->commit();
                $message = 'Book issued successfully. Due date: ' . $due . '.';
                $books = $conn->query('SELECT id, title, available_copies FROM books ORDER BY title')->fetch_all(MYSQLI_ASSOC);
            }
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $error = 'Could not issue the book.';
        }
    }
}

$val = function ($key, $default = '') use ($message) {
    return htmlspecialchars($message === '' ? ($_POST[$key] ?? $default) : $default);
};
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Issue book</title>
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
      <h2 class="fw-bold mb-0">Issue book</h2>
      <p class="text-secondary">Lend a book to a member</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">Back to loans</a>
  </div>

  <?php if ($message !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Member</label>
          <select name="user_id" class="form-select" required>
            <option value="">-- choose --</option>
            <?php foreach ($members as $m): ?>
              <option value="<?= (int)$m['id'] ?>" <?= ($message === '' && (int)($_POST['user_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['full_name'] . ' · ' . $m['email']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Only active members can borrow.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Book</label>
          <select name="book_id" class="form-select" required>
            <option value="">-- choose --</option>
            <?php foreach ($books as $b): ?>
              <option value="<?= (int)$b['id'] ?>" <?= ($message === '' && (int)($_POST['book_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['title'] . ' · ' . $b['available_copies'] . ' available') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Date issued</label>
          <input type="date" name="loan_date" id="loan_date" max="<?= $today ?>" class="form-control" value="<?= $val('loan_date', $today) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Due date</label>
          <input type="date" id="due_date" class="form-control" disabled>
          <div class="form-text">Set automatically: <?= LOAN_DAYS ?> days after the issue date.</div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Issue book</button>
          <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <p class="text-center text-secondary small mt-4 mb-0">Library Management System · Database II CAT 2</p>
</div>
</div>
<script>
  const issued = document.getElementById('loan_date');
  const due = document.getElementById('due_date');
  function updateDue() {
    if (!issued.value) { due.value = ''; return; }
    const d = new Date(issued.value + 'T00:00:00');
    d.setDate(d.getDate() + <?= LOAN_DAYS ?>);
    due.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  issued.addEventListener('input', updateDue);
  updateDue();
</script>
</body>
</html>
