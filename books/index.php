<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$activePage = 'Books';

$isAdmin = !empty($_SESSION['is_admin']);
$userId = (int)$_SESSION['user_id'];
$where = $isAdmin ? '' : 'WHERE EXISTS (SELECT * FROM book_loans WHERE book_loans.book_id = books.id AND book_loans.user_id = ?)';

$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

$countStmt = $conn->prepare("SELECT COUNT(*) FROM books $where");
if (!$isAdmin) {
    $countStmt->bind_param('i', $userId);
}
$countStmt->execute();
$total = (int)$countStmt->get_result()->fetch_row()[0];
$totalPages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare(
    "SELECT books.id, books.title, books.isbn, books.total_copies, books.available_copies, authors.name AS author, categories.name AS category
     FROM books
     JOIN authors ON authors.id = books.author_id
     JOIN categories ON categories.id = books.category_id
     $where
     ORDER BY books.id LIMIT ? OFFSET ?"
);
if ($isAdmin) {
    $stmt->bind_param('ii', $perPage, $offset);
} else {
    $stmt->bind_param('iii', $userId, $perPage, $offset);
}
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$message = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>

    .main-content {
      min-height: 100vh;
    }
    .ml-10{ margin-left: 10px;}
    .dashboard-section-one {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .dashboard-section-two{
      margin-top: 20px;
      margin-bottom: 20px;
    }

    .dashboard-section-two .bi {
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .dashboard-section-two .card {
      color: #fff;
      border: none;
    }

    .dashboard-card {
      border: 1px solid #dde3ea;
      border-radius: 12px;
      background: #fff;
    }

    .recent-loans-table thead {
      background: #0c3264;
      color: #fff;
    }

    .recent-loans-table th,
    .recent-loans-table td {
      padding: 14px 16px;
    }

    .category-bar {
      height: 20px;
      background: #e9eef5;
      border-radius: 5px;
      overflow: hidden;
    }

    .category-bar-fill {
      height: 100%;
      background: #0d6efd;
      border-radius: 5px;
    }
    .table-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>
<body>
  <div class="row">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="col-lg-10 main-content p-4">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <div class="dashboard-section-one mt-3">
      <div>
        <h2>Books Management</h2>
        <span>Manage your library books</span>
      </div>
      <?php if ($isAdmin): ?>
      <div>
        <a class="btn btn-primary" href="./create.php">Add a Book</a>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($message !== ''): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>TITLE</th>
            <th>AUTHOR</th>
            <th>CATEGORY</th>
            <th>ISBN</th>
            <?php if ($isAdmin): ?><th>AVAILABLE</th><?php endif; ?>
            <?php if ($isAdmin): ?><th>ACTIONS</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($books as $i => $book): ?>
            <tr>
              <td><?= $offset + $i + 1 ?></td>
              <td class="fw-bold"><?= $book['title'] ?></td>
              <td><?= $book['author'] ?></td>
              <td><?= $book['category'] ?></td>
              <td><?= $book['isbn'] ?></td>
              <?php if ($isAdmin): ?>
              <td>
                <?php if ($book['available_copies'] == 0): ?>
                  <span class="badge text-bg-danger"><?= $book['available_copies'] ?> / <?= $book['total_copies'] ?></span>
                <?php else: ?>
                  <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?>
                <?php endif; ?>
              </td>
              <td>
                <a href="edit.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                   <i class="bi bi-pencil"></i>
                </a>
                <form action="delete.php" method="post" class="d-inline"
                      onsubmit="return">
                  <input type="hidden" name="id" value="<?= $book['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="table-footer p-3">
        <span>
          <?php if ($total > 0): ?>
            Showing <?= $offset + 1 ?> to <?= $offset + count($books) ?> of <?= $total ?> books
          <?php else: ?>
            Showing 0 books
          <?php endif; ?>
        </span>
        <ul class="pagination">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">&laquo;</a></li>
          <?php endif; ?>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

  </div>
</div>
</body>
</html>
