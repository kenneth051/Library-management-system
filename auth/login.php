<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
    <style>
        .container {
            min-height: 100vh;
            max-width: 100%;
            min-width: 100%;
        }
        .left-section {
            background-color: #0a58ca;
            color:white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-form {
            max-width: 70%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-self: center;
            margin: 0 auto;
        }
        .right-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
        }
        .login-heading {
            text-align: center;
            margin-bottom: 30px;
        }
        .new-librarian {
            text-align: center;
            margin-top: 20px;

            a {
                text-decoration: none;
            }
        }
        .bi-book { font-size: 6rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
      <div class="col-lg-6 left-section">
        <i class="bi bi-book brand-icon"></i>
        <h1>Library</h1>
        <h1> Management System</h1>
        <p class="fs-5 mt-3">Read &bull; Borrow &bull; Learn</p>
      </div>
      <div class="col-lg-6 right-section">
        <div class="login-heading">
          <h2>Welcome Back!</h2>
          <p class="text-secondary mb-4">Please login to continue</p>
        </div>
        <form method="post" action="" class="login-form">
            <div class="input-group mb-3">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="new-librarian">
            <span class="text-secondary">New librarian? </span><a href="">Sign up</a>
        </p>
      </div>
    </div>
</div>
</body>
</html>
