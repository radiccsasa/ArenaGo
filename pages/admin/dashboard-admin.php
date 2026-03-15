<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

// Osnovna statistika
$users_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$centers_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='center'")->fetch_assoc()['count'];
$terms_count = $conn->query("SELECT COUNT(*) as count FROM terms")->fetch_assoc()['count'];
$reservations_count = $conn->query("SELECT COUNT(*) as count FROM reservations")->fetch_assoc()['count'];
$comments_count = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

// Statistika po sportovima
$sports_stats = $conn->query("
    SELECT 
        s.name, 
        COUNT(t.id) as term_count,
        COUNT(r.id) as reservation_count,
        ROUND(COUNT(r.id) / COUNT(t.id) * 100, 1) as popularity_percentage
    FROM sports s 
    LEFT JOIN terms t ON s.id = t.sport_id 
    LEFT JOIN reservations r ON t.id = r.term_id
    GROUP BY s.id 
    ORDER BY reservation_count DESC 
    LIMIT 5
");

// Statistika po centrima
$centers_stats = $conn->query("
    SELECT 
        sc.name,
        sc.location,
        COUNT(DISTINCT t.id) as term_count,
        COUNT(DISTINCT r.id) as reservation_count,
        COUNT(DISTINCT c.id) as comment_count,
        COALESCE(AVG(rt.rating), 0) as avg_rating
    FROM sports_centers sc 
    LEFT JOIN terms t ON sc.id = t.center_id 
    LEFT JOIN reservations r ON t.id = r.term_id
    LEFT JOIN comments c ON sc.id = c.center_id
    LEFT JOIN ratings rt ON sc.id = rt.center_id
    GROUP BY sc.id 
    ORDER BY reservation_count DESC 
    LIMIT 5
");

// Statistika po lokacijama (gradovima)
$location_stats = $conn->query("
    SELECT 
        sc.location,
        COUNT(DISTINCT sc.id) as centers_count,
        COUNT(DISTINCT t.id) as terms_count,
        COUNT(DISTINCT r.id) as reservations_count,
        ROUND(COUNT(DISTINCT r.id) / COUNT(DISTINCT t.id) * 100, 1) as occupancy_rate
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    LEFT JOIN reservations r ON t.id = r.term_id
    WHERE sc.location IS NOT NULL AND sc.location != ''
    GROUP BY sc.location
    ORDER BY reservations_count DESC
    LIMIT 5
");

// Mesečna statistika rezervacija (poslednjih 6 meseci)
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%M') as month,
        COUNT(*) as reservation_count
    FROM reservations
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at DESC
    LIMIT 6
");

// Status rezervacija (za pie chart)
$reservation_status = $conn->query("
    SELECT 
        status,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reservations), 1) as percentage
    FROM reservations
    GROUP BY status
");

// Najaktivniji korisnici
$active_users = $conn->query("
    SELECT 
        u.name,
        u.email,
        COUNT(DISTINCT r.id) as reservation_count,
        COUNT(DISTINCT c.id) as comment_count
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    LEFT JOIN comments c ON u.id = c.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    HAVING reservation_count > 0 OR comment_count > 0
    ORDER BY reservation_count DESC
    LIMIT 5
");

// Popularnost sportova po gradovima
$city_sports = $conn->query("
    SELECT 
        sc.location,
        s.name as sport_name,
        COUNT(t.id) as term_count
    FROM sports_centers sc
    JOIN terms t ON sc.id = t.center_id
    JOIN sports s ON t.sport_id = s.id
    WHERE sc.location IS NOT NULL AND sc.location != ''
    GROUP BY sc.location, s.name
    ORDER BY sc.location, term_count DESC
    LIMIT 10
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="col-md-2">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Korisnici</h6>
                        <h2 class="mb-0"><?php echo $users_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Centri</h6>
                        <h2 class="mb-0"><?php echo $centers_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Termini</h6>
                        <h2 class="mb-0"><?php echo $terms_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Rezervacije</h6>
                        <h2 class="mb-0"><?php echo $reservations_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-secondary mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Komentari</h6>
                        <h2 class="mb-0"><?php echo $comments_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Ocene</h6>
                        <h2 class="mb-0"><?php echo $conn->query("SELECT COUNT(*) as count FROM ratings")->fetch_assoc()['count']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prvi red - Sportovi i Centri -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart text-primary"></i> Najpopularniji sportovi</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sport</th>
                                    <th>Termina</th>
                                    <th>Rezervacija</th>
                                    <th>Popunjenost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sport = $sports_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sport['name']); ?></strong></td>
                                    <td><?php echo $sport['term_count']; ?></td>
                                    <td><?php echo $sport['reservation_count']; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $sport['popularity_percentage']; ?>%">
                                                <?php echo $sport['popularity_percentage']; ?>%
                                            </div>
                                        </div>
                                    </td>
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
                        <h5 class="mb-0"><i class="bi bi-trophy text-warning"></i> Top 5 sportskih centara</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Centar</th>
                                    <th>Lokacija</th>
                                    <th>Termina</th>
                                    <th>Rezervacija</th>
                                    <th>Ocena</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($center = $centers_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($center['location']); ?></td>
                                    <td><?php echo $center['term_count']; ?></td>
                                    <td><?php echo $center['reservation_count']; ?></td>
                                    <td>
                                        <?php 
                                        $rating = round($center['avg_rating'], 1);
                                        for($i = 1; $i <= 5; $i++) {
                                            if($i <= $rating) {
                                                echo '<i class="bi bi-star-fill text-warning"></i>';
                                            } elseif($i - 0.5 <= $rating) {
                                                echo '<i class="bi bi-star-half text-warning"></i>';
                                            } else {
                                                echo '<i class="bi bi-star text-warning"></i>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drugi red - Lokacije i Status rezervacija -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-danger"></i> Najpopularnije lokacije</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Grad</th>
                                    <th>Centara</th>
                                    <th>Termina</th>
                                    <th>Rezervacija</th>
                                    <th>Popunjenost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($location = $location_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($location['location']); ?></strong></td>
                                    <td><?php echo $location['centers_count']; ?></td>
                                    <td><?php echo $location['terms_count']; ?></td>
                                    <td><?php echo $location['reservations_count']; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $location['occupancy_rate']; ?>%</span>
                                    </td>
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
                        <h5 class="mb-0"><i class="bi bi-pie-chart text-info"></i> Status rezervacija</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            $status_colors = [
                                'confirmed' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger'
                            ];
                            while($status = $reservation_status->fetch_assoc()): 
                            ?>
                            <div class="col-4 text-center mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="text-<?php echo $status_colors[$status['status']] ?? 'secondary'; ?> mb-0">
                                        <?php echo $status['count']; ?>
                                    </h3>
                                    <small class="text-muted">
                                        <?php 
                                        switch($status['status']) {
                                            case 'confirmed': echo 'Potvrđeno'; break;
                                            case 'pending': echo 'Na čekanju'; break;
                                            case 'cancelled': echo 'Otkazano'; break;
                                            default: echo $status['status'];
                                        }
                                        ?>
                                    </small>
                                    <div class="mt-2">
                                        <span class="badge bg-<?php echo $status_colors[$status['status']] ?? 'secondary'; ?>">
                                            <?php echo $status['percentage']; ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treći red - Mesečna statistika i Aktivni korisnici -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up text-success"></i> Mesečni trend rezervacija</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mesec</th>
                                    <th>Broj rezervacija</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($month = $monthly_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $month['month']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2"><?php echo $month['reservation_count']; ?></span>
                                            <div class="progress flex-grow-1" style="height: 10px;">
                                                <?php 
                                                $max = $monthly_stats->num_rows > 0 ? $month['reservation_count'] : 10;
                                                $width = ($month['reservation_count'] / 50) * 100;
                                                ?>
                                                <div class="progress-bar bg-success" style="width: <?php echo min($width, 100); ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
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
                        <h5 class="mb-0"><i class="bi bi-people-fill text-primary"></i> Najaktivniji korisnici</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Korisnik</th>
                                    <th>Rezervacija</th>
                                    <th>Komentara</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = $active_users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $user['reservation_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $user['comment_count']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Četvrti red - Sportovi po gradovima -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-steps text-primary"></i> Popularnost sportova po gradovima</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Grad</th>
                                        <th>Sport</th>
                                        <th>Broj termina</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $current_city = '';
                                    while($city_sport = $city_sports->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            if($current_city != $city_sport['location']) {
                                                $current_city = $city_sport['location'];
                                                echo '<strong>' . htmlspecialchars($city_sport['location']) . '</strong>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($city_sport['sport_name']); ?></td>
                                        <td><?php echo $city_sport['term_count']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</body>
</html>