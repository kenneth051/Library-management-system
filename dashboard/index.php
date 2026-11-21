<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$activePage = 'Dashboard';
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
    .account-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: bold;
    }
    .logged-in-user {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: bold;
    }
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

  <div class="col-lg-10 main-content p-4">
    <nav class="navbar bg-1 fg-2 border-bottom">
      <div class="container-fluid">
        <button class="btn-icon navbar-toggler" type="button" data-bs-toggle="drawer" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon" aria-hidden="true"></span>
        </button>
        <span class="logged-in-user">
          <span class="account-circle bg-primary">AD </span>
          <span class="ms-2"><?php echo $_SESSION['user_name']; ?></span>
        </span>
        <dialog class="drawer drawer-end" tabindex="-1" id="navbarTogglerDemo03" aria-labelledby="navbarTogglerDemo03Label">
          <!-- <div class="drawer-header">
            <h5 class="drawer-title" id="navbarTogglerDemo03Label">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
          </div>
          <div class="drawer-body">
            <ul class="navbar-nav me-auto mb-2">
              <li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="#">Link</a></li>
              <li class="nav-item"><a class="nav-link disabled" aria-disabled="true">Disabled</a></li>
            </ul>
            <form class="d-flex" role="search">
              <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
              <button class="btn-outline theme-success" type="submit">Search</button>
            </form>
          </div> -->
        </dialog>
      </div>
    </nav>
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
          <span class="fs-2 fw-bold">1,234</span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #14c696">
          <i class="bi bi-people"></i>
          <h5>Total Members</h5>
          <span class="fs-2 fw-bold">567</span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #f0810a">
          <i class="bi bi-arrow-left-right"></i>
          <h5>Borrowed Books</h5>
          <span class="fs-2 fw-bold">89</span>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="card p-3" style="background-color: #e81818">
          <i class="bi bi-exclamation-circle"></i>
          <h5>Overdue Books</h5>
          <span class="fs-2 fw-bold">12</span>
        </div>
      </div>
    </div>
    <div class="dashboard-section-three mt-3 row g-4">
      <div class="col-lg-8 mb-3">
        <div class="dashboard-card overflow-hidden">
          <div class="d-flex justify-content-between align-items-center px-4 py-3">
            <h3 class="fw-bold mb-0">Recent loans</h3>
            <a href="#" class="fw-bold text-decoration-none">View all</a>
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
                <tr>
                  <td>Okello David</td>
                  <td>Introduction to Algorithms</td>
                  <td>20 Oct 2026</td>
                  <td><span class="badge rounded-pill text-danger bg-danger-subtle px-3 py-2">Overdue</span></td>
                </tr>
                <tr>
                  <td>Achieng Grace</td>
                  <td>Clean Code</td>
                  <td>27 Oct 2026</td>
                  <td><span class="badge rounded-pill text-danger bg-danger-subtle px-3 py-2">Overdue</span></td>
                </tr>
                <tr>
                  <td>Opio Samuel</td>
                  <td>Learning SQL</td>
                  <td>16 Nov 2026</td>
                  <td><span class="badge rounded-pill text-success bg-success-subtle px-3 py-2">On time</span></td>
                </tr>
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
