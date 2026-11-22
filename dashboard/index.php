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

$activePage = 'Dashboard';

function countRows($conn, $sql) {
    return (int)$conn->query($sql)->fetch_row()[0];
}

$totalBooks = countRows($conn, 'SELECT COUNT(*) FROM books');
$totalMembers = countRows($conn, 'SELECT COUNT(*) FROM users WHERE is_admin = 0');

$borrowedBooks = countRows($conn, 'SELECT COUNT(*) FROM book_loans WHERE return_date IS NULL');
$overdueBooks = countRows($conn, 'SELECT COUNT(*) FROM book_loans WHERE return_date IS NULL AND due_date < CURDATE()');

$recentLoans = $conn->query(
    'SELECT users.full_name AS member, books.title, book_loans.due_date, book_loans.return_date
     FROM book_loans
     JOIN users ON users.id = book_loans.user_id
     JOIN books ON books.id = book_loans.book_id
     ORDER BY book_loans.loan_date DESC, book_loans.id DESC
     LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Dashboard</title>

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
  </style>
</head>
<body>
  <div class="row">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="col-lg-10 main-content">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <div class="dashboard-section-one mt-3">
      <div>
        <h2>Dashboard</h2>
        <span>Library Overview</span>
      </div>
      <div>
        <button class="btn btn-primary">Issue a Book</button>
      </div>
    </div>
     <?php if (!empty($_SESSION['user_name'])): ?>
    <div class="alert alert-success">
      Welcome back, <?php echo $_SESSION['user_name']; ?>. You are signed in
    </div>
    <?php endif; ?>

    <div class="dashboard-section-two row">
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #0d6efd">
          <i class="bi bi-book"></i>
          <h5>Total Books</h5>
          <span class="fs-2 fw-bold"><?= $totalBooks ?></span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #14c696">
          <i class="bi bi-people"></i>
          <h5>Total Members</h5>
          <span class="fs-2 fw-bold"><?= $totalMembers ?></span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #f0810a">
          <i class="bi bi-arrow-left-right"></i>
          <h5>Borrowed Books</h5>
          <span class="fs-2 fw-bold"><?= $borrowedBooks ?></span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #e81818">
          <i class="bi bi-exclamation-circle"></i>
          <h5>Overdue Books</h5>
          <span class="fs-2 fw-bold"><?= $overdueBooks ?></span>
        </div>
      </div>
    </div>
    <div class="dashboard-section-three mt-3 row g-4">
      <div class="col-lg-8 mb-3">
        <div class="dashboard-card overflow-hidden">
          <div class="d-flex justify-content-between align-items-center px-4 py-3">
            <h3 class="fw-bold mb-0">Recent loans</h3>
            <a href="../loans/index.php" class="fw-bold text-decoration-none">View all</a>
          </div>

          <div class="table-responsive">
            <table class="table recent-loans-table align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">MEMBER</th>
                  <th scope="col">BOOK</th>
                  <th scope="col">DUE DATE</th>
                  <th scope="col">STATUS</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$recentLoans): ?>
                  <tr><td colspan="4" class="text-center text-muted py-4">No loans yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($recentLoans as $loan): ?>
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
                    <td><?= htmlspecialchars($loan['member']) ?></td>
                    <td><?= htmlspecialchars($loan['title']) ?></td>
                    <td><?= date('d M Y', strtotime($loan['due_date'])) ?></td>
                    <td><span class="badge rounded-pill text-<?= $badge[1] ?> bg-<?= $badge[1] ?>-subtle px-3 py-2"><?= $badge[0] ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mb-3">
        <div class="dashboard-card p-4 mb-4">
          <h3 class="fw-bold mb-3">Library notice</h3>
          <p class=" mb-0">Return books on time to avoid fines.</p>
        </div>

        <div class="dashboard-card p-4">
          <h3 class="fw-bold mb-4">Loans per category</h3>

          <div class="d-flex align-items-center mb-3">
            <span style="width: 35%">Computer Sci.</span>
            <strong>18</strong>
          </div>

          <div class="d-flex align-items-center mb-3">
            <span style="width: 35%">Fiction</span>
            <strong>12</strong>
          </div>

          <div class="d-flex align-items-center mb-3">
            <span style="width: 35%">Science</span>
            <strong>7</strong>
          </div>

          <div class="d-flex align-items-center">
            <span style="width: 35%">History</span>
            <strong>4</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
