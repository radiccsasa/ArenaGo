
<!doctype html>
<?php
session_start();

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    switch($_SESSION['user']['role']) {
        case 'admin':
            header('Location: /ArenaGo/pages/admin/dashboard-admin.php');
            break;
        case 'center':
            header('Location: /ArenaGo/pages/center/dashboard-center.php');
            break;
        default:
            header('Location: /ArenaGo/pages/index/index.php');
    }
    exit();
}

?>
<html lang="sr">
  <head>
    <meta charset="UTF-8" />
    <title>Registracija</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="stylesheet"
      href="../../__bootstrap_packages/css/bootstrap.min.css"
    />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
  </head>
  <body>
    <nav class="navbar navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="/pages/index/index.html">SportBook</a>
      </div>
    </nav>

    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <h3 class="text-center mb-4">Registracija</h3>

          <form>
            <input
              id="name"
              type="text"
              class="form-control mb-3"
              placeholder="Ime i prezime"
              required
            />
            <input
              id="email"
              type="email"
              class="form-control mb-3"
              placeholder="Email"
              required
            />
            <input
              id="password"
              type="password"
              class="form-control mb-3"
              placeholder="Lozinka"
              required
            />

            <select id="role" class="form-select mb-3">
              <option value="user">Korisnik</option>
              <option value="center">Sportski centar</option>
            </select>

            <button id="regBtn" type="button" class="btn btn-success w-100">Registruj se</button>
          </form>
        </div>
      </div>
    </div>

    <script src="register.js"></script>
  </body>
</html>