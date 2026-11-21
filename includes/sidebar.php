<?php
$activePage = $activePage ?? 'Dashboard';
$baseUrl = '/Library-management-system';

function sidebarLinkClass($label, $activePage) {
    return 'sidebar-link d-flex align-items-center gap-3' . ($label === $activePage ? ' active' : '');
}
?>
<style>
  .sidebar {
    min-height: 100vh;
    background: #0c3264;
  }
  .sidebar-link {
    padding: 0 38px;
    color: #fff;
    font-size: 20px;
    text-decoration: none;
  }

  .sidebar-link i {
     font-size: 25px;
  }

  .sidebar-link:hover,
  .sidebar-link.active {
    color: #fff;
    background: #0d6efd;
    border-radius: 5px;
  }
</style>

<div class="col-lg-2 sidebar top-0 start-0 flex-column text-white">
  <div class="d-flex align-items-center px-4" style="height: 126px;">
    <i class="bi bi-book fs-1 me-3"></i>
    <span class="fs-1 fw-bold">LMS</span>
  </div>

  <nav class="flex-grow-1 py-4">
    <ul class="list-unstyled mb-0">
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/dashboard/index.php" class="<?php echo sidebarLinkClass('Dashboard', $activePage); ?>">
          <i class="bi bi-house-door"></i><span>Dashboard</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/books/index.php" class="<?php echo sidebarLinkClass('Books', $activePage); ?>">
          <i class="bi bi-book"></i><span>Books</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/members/index.php" class="<?php echo sidebarLinkClass('Members', $activePage); ?>">
          <i class="bi bi-people"></i><span>Members</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/loans/index.php" class="<?php echo sidebarLinkClass('Loans', $activePage); ?>">
          <i class="bi bi-arrow-left-right"></i><span>Loans</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/authors/index.php" class="<?php echo sidebarLinkClass('Authors', $activePage); ?>">
          <i class="bi bi-pencil"></i><span>Authors</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/categories/index.php" class="<?php echo sidebarLinkClass('Categories', $activePage); ?>">
          <i class="bi bi-grid"></i><span>Categories</span>
        </a>
      </li>
      <li class="mb-3">
        <a href="<?php echo $baseUrl; ?>/members/index.php" class="<?php echo sidebarLinkClass('Users', $activePage); ?>">
          <i class="bi bi-shield"></i><span>Users</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="border-top border-white border-opacity-25 d-flex align-items-center" style="height: 126px;">
    <a href="<?php echo $baseUrl; ?>/auth/logout.php" class="sidebar-link d-flex align-items-center gap-3 w-100">
      <i class="bi bi-box-arrow-left"></i><span>Logout</span>
    </a>
  </div>
</div>
