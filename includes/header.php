<style>
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
  .navbar{
    background-color: white;
    position: sticky;
    top: 0;
    padding-top: 20px;
    margin-bottom: 20px;
  }
</style>
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
