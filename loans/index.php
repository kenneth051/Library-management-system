<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
if ((int)($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: ../books/index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$activePage = 'Loans';

$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

$total = (int)$conn->query('SELECT COUNT(*) FROM book_loans')->fetch_row()[0];
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare(
    'SELECT book_loans.id, users.full_name AS member, books.title,
            book_loans.loan_date, book_loans.due_date, book_loans.return_date
     FROM book_loans
     JOIN users ON users.id = book_loans.user_id
     JOIN books ON books.id = book_loans.book_id
     ORDER BY book_loans.loan_date DESC, book_loans.id DESC
     LIMIT ? OFFSET ?'
);
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$loans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Loans</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    .main-content {
      min-height: 100vh;
    }
    .dashboard-section-one {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
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
        <h2>Loans</h2>
        <span>Books issued to members</span>
      </div>
      <div>
        <a class="btn btn-primary" href="./issue.php"><i class="bi bi-plus-lg"></i> Issue book</a>
      </div>
    </div>

    <div class="card">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>MEMBER</th>
            <th>BOOK</th>
            <th>ISSUED</th>
            <th>DUE DATE</th>
            <th>RETURNED</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$loans): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No loans yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($loans as $loan): ?>
            <?php
              if ($loan['return_date'] !== null) {
                  $badge = ['Returned', 'secondary'];
              } elseif ($loan['due_date'] < date('Y-m-d')) {
                  $badge = ['Overdue', 'danger'];
              } else {
                  $badge = ['On time', 'success'];
              }
            ?>
            <tr>
              <td><?= $loan['id'] ?></td>
              <td class="fw-bold"><?= htmlspecialchars($loan['member']) ?></td>
              <td><?= htmlspecialchars($loan['title']) ?></td>
              <td><?= date('d M Y', strtotime($loan['loan_date'])) ?></td>
              <td><?= date('d M Y', strtotime($loan['due_date'])) ?></td>
              <td><?= $loan['return_date'] ? date('d M Y', strtotime($loan['return_date'])) : '-' ?></td>
              <td><span class="badge rounded-pill text-<?= $badge[1] ?> bg-<?= $badge[1] ?>-subtle px-3 py-2"><?= $badge[0] ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="table-footer p-3">
        <span>
          <?php if ($total > 0): ?>
            Showing <?= $offset + 1 ?> to <?= $offset + count($loans) ?> of <?= $total ?> loans
          <?php else: ?>
            Showing 0 loans
          <?php endif; ?>
        </span>
        <ul class="pagination mb-0">
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
