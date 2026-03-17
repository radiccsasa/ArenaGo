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


// 1. Najbolje ocenjeni centri (prosečna ocena)
$top_rated_centers = $conn->query("
    SELECT 
        sc.name,
        sc.location,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as rating_count
    FROM sports_centers sc
    LEFT JOIN ratings r ON sc.id = r.center_id
    GROUP BY sc.id
    HAVING rating_count > 0
    ORDER BY avg_rating DESC
    LIMIT 5
");

// 2. Najpopularniji centri (najviše rezervacija)
$most_popular_centers = $conn->query("
    SELECT 
        sc.name,
        sc.location,
        COUNT(DISTINCT r.id) as reservation_count
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    LEFT JOIN reservations r ON t.id = r.term_id
    GROUP BY sc.id
    ORDER BY reservation_count DESC
    LIMIT 5
");

// Centri sa najviše termina
$most_terms_centers = $conn->query("
    SELECT 
        sc.name,
        sc.location,
        COUNT(DISTINCT t.id) as term_count
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    GROUP BY sc.id
    ORDER BY term_count DESC
    LIMIT 5
");


//  Najpopularniji sportovi po broju termina
$sports_by_terms = $conn->query("
    SELECT 
        s.name,
        COUNT(t.id) as term_count
    FROM sports s
    LEFT JOIN terms t ON s.id = t.sport_id
    GROUP BY s.id
    ORDER BY term_count DESC
    LIMIT 5
");

//  Najpopularniji sportovi po broju rezervacija
$sports_by_reservations = $conn->query("
    SELECT 
        s.name,
        COUNT(r.id) as reservation_count
    FROM sports s
    LEFT JOIN terms t ON s.id = t.sport_id
    LEFT JOIN reservations r ON t.id = r.term_id
    GROUP BY s.id
    ORDER BY reservation_count DESC
    LIMIT 5
");


//  Gradovi sa najviše termina
$cities_by_terms = $conn->query("
    SELECT 
        sc.location,
        COUNT(DISTINCT t.id) as term_count
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    WHERE sc.location IS NOT NULL AND sc.location != ''
    GROUP BY sc.location
    ORDER BY term_count DESC
    LIMIT 5
");

//  Gradovi sa najviše rezervacija
$cities_by_reservations = $conn->query("
    SELECT 
        sc.location,
        COUNT(DISTINCT r.id) as reservation_count
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    LEFT JOIN reservations r ON t.id = r.term_id
    WHERE sc.location IS NOT NULL AND sc.location != ''
    GROUP BY sc.location
    ORDER BY reservation_count DESC
    LIMIT 5
");


//  Korisnici sa najviše rezervacija
$users_by_reservations = $conn->query("
    SELECT 
        u.name,
        u.email,
        COUNT(r.id) as reservation_count
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
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
                            <i class="bi bi-speedometer2"></i> Profil
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
        <!-- Glavni brojevi -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Korisnici</h6>
                        <h2 class="mb-0"><?php echo $users_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Sportski centri</h6>
                        <h2 class="mb-0"><?php echo $centers_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Termini</h6>
                        <h2 class="mb-0"><?php echo $terms_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Rezervacije</h6>
                        <h2 class="mb-0"><?php echo $reservations_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red 1: Statistika za centre -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-star-fill text-warning"></i> Najbolje ocenjeni centri</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Centar</th>
                                    <th>Lokacija</th>
                                    <th>Ocena</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($center = $top_rated_centers->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($center['location']); ?></td>
                                    <td>
                                        <?php echo number_format($center['avg_rating'], 1); ?> 
                                        <small class="text-muted">(<?php echo $center['rating_count']; ?>)</small>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-ticket-perforated text-success"></i> Najpopularniji centri (po rezervacijama)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Centar</th>
                                    <th>Lokacija</th>
                                    <th>Rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($center = $most_popular_centers->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($center['location']); ?></td>
                                    <td><span class="badge bg-success"><?php echo $center['reservation_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-week text-primary"></i> Centri sa najviše termina</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Centar</th>
                                    <th>Lokacija</th>
                                    <th>Termina</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($center = $most_terms_centers->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($center['location']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $center['term_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red 2: Statistika za sportove -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart text-primary"></i> Najpopularniji sportovi (po broju termina)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sport</th>
                                    <th>Broj termina</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sport = $sports_by_terms->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sport['name']); ?></strong></td>
                                    <td><span class="badge bg-info"><?php echo $sport['term_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart text-success"></i> Najpopularniji sportovi (po broju rezervacija)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sport</th>
                                    <th>Broj rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sport = $sports_by_reservations->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sport['name']); ?></strong></td>
                                    <td><span class="badge bg-success"><?php echo $sport['reservation_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red 3: Statistika za gradove -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-danger"></i> Gradovi sa najviše termina</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Grad</th>
                                    <th>Broj termina</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($city = $cities_by_terms->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($city['location']); ?></strong></td>
                                    <td><span class="badge bg-info"><?php echo $city['term_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-success"></i> Gradovi sa najviše rezervacija</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Grad</th>
                                    <th>Broj rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($city = $cities_by_reservations->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($city['location']); ?></strong></td>
                                    <td><span class="badge bg-success"><?php echo $city['reservation_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red 4: Statistika za korisnike -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-people-fill text-primary"></i> Korisnici sa najviše rezervacija</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Korisnik</th>
                                    <th>Email</th>
                                    <th>Broj rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = $users_by_reservations->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $user['reservation_count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</body>
</html>