<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

$users_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$centers_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='center'")->fetch_assoc()['count'];
$terms_count = $conn->query("SELECT COUNT(*) as count FROM terms")->fetch_assoc()['count'];
$reservations_count = $conn->query("SELECT COUNT(*) as count FROM reservations")->fetch_assoc()['count'];
$comments_count = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

$sports_stats = $conn->query("
    SELECT s.name, COUNT(t.id) as term_count 
    FROM sports s 
    LEFT JOIN terms t ON s.id = t.sport_id 
    GROUP BY s.id 
    ORDER BY term_count DESC 
    LIMIT 5
");

$centers_stats = $conn->query("
    SELECT sc.name, COUNT(r.id) as reservation_count 
    FROM sports_centers sc 
    LEFT JOIN terms t ON sc.id = t.center_id 
    LEFT JOIN reservations r ON t.id = r.term_id 
    GROUP BY sc.id 
    ORDER BY reservation_count DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ArenaGo</title>
    <link href="../../__bootstrap_packages/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard-admin.php">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard-admin.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Korisnici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="centers.php">
                            <i class="bi bi-building"></i> Sportski centri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-dark ms-2" href="/ArenaGo/api/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Odjavi se
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Korisnici</h6>
                                <h2 class="mb-0"><?php echo $users_count; ?></h2>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Sportski centri</h6>
                                <h2 class="mb-0"><?php echo $centers_count; ?></h2>
                            </div>
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Termini</h6>
                                <h2 class="mb-0"><?php echo $terms_count; ?></h2>
                            </div>
                            <i class="bi bi-calendar-week fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Rezervacije</h6>
                                <h2 class="mb-0"><?php echo $reservations_count; ?></h2>
                            </div>
                            <i class="bi bi-ticket-perforated fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Najpopularniji sportovi</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sport</th>
                                    <th>Broj termina</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sport = $sports_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sport['name']); ?></td>
                                    <td><?php echo $sport['term_count']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-trophy"></i> Najaktivniji centri</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Centar</th>
                                    <th>Broj rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($center = $centers_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($center['name']); ?></td>
                                    <td><?php echo $center['reservation_count']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Brze akcije</h5>
                    </div>
                    <div class="card-body">
                        <a href="users.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-people"></i> Upravljaj korisnicima
                        </a>
                        <a href="centers.php" class="btn btn-outline-success me-2">
                            <i class="bi bi-building"></i> Upravljaj centrima
                        </a>
                        <a href="comments.php" class="btn btn-outline-info">
                            <i class="bi bi-chat"></i> Pregled komentara
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</body>
</html>