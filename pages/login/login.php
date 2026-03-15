<!doctype html>
<html lang="sr">

<head>
    <meta charset="UTF-8" />
    <title>Prijava - ArenaGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../__bootstrap_packages/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <?php require_once "../../utils/toast/toast.php";
    require_once '../../utils/header-user.php';  ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div id="notificationArea"></div>

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <i class="bi bi-box-arrow-in-right fs-1"></i>
                        <h3 class="mb-0">Prijava</h3>
                    </div>
                    <div class="card-body p-4">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input id="email" type="email" class="form-control form-control-lg" placeholder="unesite@email.com" required />
                            </div>
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-lock"></i> Lozinka
                                </label>
                                <input id="password" type="password" class="form-control form-control-lg" placeholder="••••••••" required />
                            </div>
                            <button type="button" id="loginBtn" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Prijava
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Nemate nalog?
                                <a href="../register/register.php" class="text-primary text-decoration-none">
                                    Registrujte se <i class="bi bi-arrow-right"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="login.js?v=2"></script>
</body>

</html>