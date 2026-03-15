<!doctype html>
<?php
require_once "../../utils/toast/toast.php";

session_start();

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    switch ($_SESSION['user']['role']) {
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
    <title>Registracija - ArenaGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../__bootstrap_packages/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <?php require_once '../../utils/header-user.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <!-- Mesto za notifikacije -->
                <div id="notificationArea"></div>

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white text-center py-3">
                        <i class="bi bi-person-plus fs-1"></i>
                        <h3 class="mb-0">Registracija</h3>
                    </div>
                    <div class="card-body p-4">
                        <form id="registerForm">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person"></i> Ime i prezime
                                </label>
                                <input id="name" type="text" class="form-control form-control-lg" placeholder="Petar Petrović" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input id="email" type="email" class="form-control form-control-lg" placeholder="petar@email.com" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lock"></i> Lozinka
                                </label>
                                <input id="password" type="password" class="form-control form-control-lg" placeholder="••••••••" required />
                                <small class="text-muted">Minimalno 6 karaktera</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-person-badge"></i> Tip naloga
                                </label>
                                <select id="role" class="form-select form-select-lg">
                                    <option value="user">👤 Korisnik</option>
                                    <option value="center">🏢 Sportski centar</option>
                                </select>
                            </div>

                            <button id="regBtn" type="button" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-person-plus"></i> Registruj se
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Već imate nalog?
                                <a href="../login/login.php" class="text-success text-decoration-none">
                                    Prijavite se <i class="bi bi-arrow-right"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="register.js?v=2"></script>
</body>

</html>