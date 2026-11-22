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

$activePage = 'Members';

$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

$total = (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare(
    "SELECT id, full_name, email, is_active, is_admin, created_at
     FROM users
     ORDER BY id LIMIT ? OFFSET ?"
);
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LMS | Members</title>

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
        <h2>Members management</h2>
        <span>Manage library members</span>
      </div>
      <div>
        <a class="btn btn-primary" href="./create.php"><i class="bi bi-plus-lg"></i> Add member</a>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= $flash['text'] ?></div>
    <?php endif; ?>

    <div class="card">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>NAME</th>
            <th>EMAIL</th>
            <th>ROLE</th>
            <th>STATUS</th>
            <th>JOINED</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$members): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No members found.</td></tr>
          <?php endif; ?>
          <?php foreach ($members as $i => $member): ?>
            <tr>
              <td><?= $member['id'] ?></td>
              <td class="fw-bold"><?= $member['full_name'] ?></td>
              <td><?= $member['email'] ?></td>
              <td>
                <?php if ($member['is_admin']): ?>
                  <span class="badge rounded-pill text-primary bg-primary-subtle px-3 py-2">Admin</span>
                <?php else: ?>
                  <span class="badge rounded-pill text-secondary bg-secondary-subtle px-3 py-2">Member</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($member['is_active']): ?>
                  <span class="badge rounded-pill text-success bg-success-subtle px-3 py-2">Active</span>
                <?php else: ?>
                  <span class="badge rounded-pill text-danger bg-danger-subtle px-3 py-2">Suspended</span>
                <?php endif; ?>
              </td>
              <td><?= date('d M Y', strtotime($member['created_at'])) ?></td>
              <td>
                <a href="edit.php?id=<?= $member['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <?php if (!$member['is_admin']): ?>
                <form action="delete.php" method="post" class="d-inline"
                      onsubmit="return confirm('Delete this member?');">
                  <input type="hidden" name="id" value="<?= $member['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="table-footer p-3">
        <span>
          <?php if ($total > 0): ?>
            Showing <?= $offset + 1 ?> to <?= $offset + count($members) ?> of <?= $total ?> users
          <?php else: ?>
            Showing 0 users
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
