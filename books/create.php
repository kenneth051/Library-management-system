<?php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

$authors = $conn->query('SELECT id, name FROM authors ORDER BY name');
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');

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
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Book</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; }
    .card { max-width:420px; margin:40px auto; background:#fff; padding:24px; border-radius:8px; }
    label { display:block; margin-top:12px; }
    input, select { width:100%; padding:8px; margin-top:5px; box-sizing:border-box; }
    button { margin-top:16px; padding:10px 16px; background:#174a72; color:#fff; border:none; cursor:pointer; }
    .ok { color:green; font-weight:bold; }
    .err { color:#b00020; font-weight:bold; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Add Book</h2>
    <?php if ($message): ?><p class="ok"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
      <label>ISBN <input type="text" name="isbn" required></label>
      <label>Title <input type="text" name="title" required></label>
      <label>Author
        <select name="author_id" required>
          <option value="">-- choose --</option>
          <?php while ($a = $authors->fetch_assoc()): ?>
            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </label>
      <label>Category
        <select name="category_id" required>
          <option value="">-- choose --</option>
          <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </label>
      <label>Publisher <input type="text" name="publisher"></label>
      <label>Year published <input type="number" name="year_published" min="1000" max="2100"></label>
      <label>Total copies <input type="number" name="total_copies" value="1" min="1" required></label>
      <button type="submit">Save Book</button>
    </form>
  </div>
</body>
</html>