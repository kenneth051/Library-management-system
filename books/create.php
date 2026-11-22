<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if ((int)($_SESSION['is_admin'] ?? 0) !== 1) { header('Location: ../books/index.php'); exit(); }
$activePage = 'Books';
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

$authors = $conn->query('SELECT id, name FROM authors ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn        = trim($_POST['isbn'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $author_id   = (int)($_POST['author_id'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $publisher   = trim($_POST['publisher'] ?? '');
    $year        = (int)($_POST['year_published'] ?? 0);
    $copies      = (int)($_POST['total_copies'] ?? 0);

    if ($isbn === '' || $title === '' || $author_id < 1 || $category_id < 1) {
        $error = 'ISBN, title, author and category are required.';
    } elseif ($copies < 1) {
        $error = 'Total copies must be at least 1.';
    } elseif ($year !== 0 && ($year < 1000 || $year > (int)date('Y'))) {
        $error = 'Enter a valid year published.';
    } else {
        $year = $year === 0 ? null : $year;
        $publisher = $publisher === '' ? null : $publisher;
        try {
            $stmt = $conn->prepare(
                'INSERT INTO books (isbn, title, author_id, category_id, publisher,
                 year_published, total_copies, available_copies)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssiisiii', $isbn, $title, $author_id,
                $category_id, $publisher, $year, $copies, $copies);
            $stmt->execute();
            $message = 'Book added successfully.';
        } catch (mysqli_sql_exception $e) {
            $error = ($e->getCode() == 1062)
                ? 'A book with that ISBN already exists.'
                : 'Could not save the book.';
        }
    }
}
?>
<?php
$val = function ($key, $default = '') use ($message) {
    return htmlspecialchars($message === '' ? ($_POST[$key] ?? $default) : $default);
};
$dupErr  = str_contains($error, 'already exists');
$yearErr = str_contains($error, 'year');
$copyErr = str_contains($error, 'copies');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Add book</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    .main-content { min-height: 100vh; background: #eef1f8; }
    .account-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; background: #0d6efd; }
  </style>
</head>
<body>
<div class="row">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-lg-10 main-content">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h2 class="fw-bold mb-0">Add book</h2>
      <p class="text-secondary">Enter the details of the new book</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">Back to books</a>
  </div>

  <?php if ($message !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">ISBN</label>
          <input type="text" name="isbn" class="form-control <?= $dupErr ? 'is-invalid' : '' ?>" value="<?= $val('isbn') ?>" required>
          <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Title</label>
          <input type="text" name="title" class="form-control" value="<?= $val('title') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Author</label>
          <select name="author_id" class="form-select" required>
            <option value="">-- choose --</option>
            <?php foreach ($authors as $a): ?>
              <option value="<?= (int)$a['id'] ?>" <?= ($message === '' && (int)($_POST['author_id'] ?? 0) === (int)$a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Not listed? <a href="../authors/index.php">Add an author first.</a></div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Category</label>
          <select name="category_id" class="form-select" required>
            <option value="">-- choose --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ($message === '' && (int)($_POST['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Publisher</label>
          <input type="text" name="publisher" class="form-control" value="<?= $val('publisher') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Year published</label>
          <input type="text" name="year_published" class="form-control <?= $yearErr ? 'is-invalid' : '' ?>" value="<?= $val('year_published') ?>">
          <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Total copies</label>
          <input type="number" min="1" name="total_copies" class="form-control <?= $copyErr ? 'is-invalid' : '' ?>" value="<?= $val('total_copies', '1') ?>" required>
          <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Available copies</label>
          <input type="text" class="form-control" value="<?= $val('total_copies', '1') ?>" disabled>
          <div class="form-text">Set automatically to match total copies on a new book.</div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save book</button>
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