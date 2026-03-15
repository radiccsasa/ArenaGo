<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// Dohvatanje svih centara sa statistikom
$query = "
    SELECT 
        sc.*,
        u.email,
        u.status as user_status,
        u.id as user_id,
        COUNT(DISTINCT t.id) as total_terms,
        COUNT(DISTINCT r.id) as total_reservations,
        COUNT(DISTINCT c.id) as total_comments,
        COALESCE(AVG(rt.rating), 0) as avg_rating
    FROM sports_centers sc
    JOIN users u ON sc.user_id = u.id
    LEFT JOIN terms t ON sc.id = t.center_id
    LEFT JOIN reservations r ON t.id = r.term_id
    LEFT JOIN comments c ON sc.id = c.center_id
    LEFT JOIN ratings rt ON sc.id = rt.center_id
    WHERE 1=1
";

$params = [];
$types = "";

if(!empty($search)) {
    $query .= " AND (sc.name LIKE ? OR sc.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$query .= " GROUP BY sc.id ORDER BY sc.name";

$stmt = $conn->prepare($query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$centers_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportski centri - Admin Panel</title>
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
                        <a class="nav-link" href="dashboard-admin.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Korisnici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="centers.php">
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
        <!-- Pretraga -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <label class="form-label">Pretraga po nazivu ili lokaciji</label>
                        <input type="text" name="search" class="form-control" placeholder="Unesite naziv centra ili grad..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Pretraži
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kartice centara -->
        <div class="row">
            <?php if($centers_result->num_rows > 0): ?>
                <?php while($center = $centers_result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-primary">
                                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($center['name']); ?>
                                    </h5>
                                    <?php if($center['user_status'] == 'active'): ?>
                                        <span class="badge bg-success">Aktivan</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Blokiran</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt text-primary"></i> 
                                    <?php echo htmlspecialchars($center['location'] ?? 'Lokacija nije navedena'); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-envelope text-primary"></i> 
                                    <?php echo htmlspecialchars($center['email']); ?>
                                </p>
                                
                                <!-- Statistika u brojevima -->
                                <div class="row text-center mt-3 mb-3">
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="text-primary mb-0"><?php echo $center['total_terms']; ?></h6>
                                            <small class="text-muted">Termina</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="text-success mb-0"><?php echo $center['total_reservations']; ?></h6>
                                            <small class="text-muted">Rezervacija</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="text-info mb-0"><?php echo $center['total_comments']; ?></h6>
                                            <small class="text-muted">Komentara</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="text-warning mb-0"><?php echo number_format($center['avg_rating'], 1); ?></h6>
                                            <small class="text-muted">Ocena</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kratak opis ako postoji -->
                                <?php if(!empty($center['description'])): ?>
                                    <p class="text-muted small">
                                        <?php echo substr(htmlspecialchars($center['description']), 0, 100) . '...'; ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Akcije -->
                                <div class="d-flex gap-2 mt-3">
                                    <a href="center-details.php?id=<?php echo $center['id']; ?>" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-eye"></i> Detalji centra
                                    </a>
                                    <?php if($center['user_status'] == 'active'): ?>
                                        <button class="btn btn-outline-danger" onclick="toggleCenterStatus(<?php echo $center['user_id']; ?>, 'block')" title="Blokiraj centar">
                                            <i class="bi bi-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success" onclick="toggleCenterStatus(<?php echo $center['user_id']; ?>, 'unblock')" title="Odblokiraj centar">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nema sportskih centara za prikaz.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function toggleCenterStatus(userId, action) {
        if(confirm(`Da li ste sigurni da želite da ${action == 'block' ? 'blokirate' : 'odblokirate'} ovaj centar?`)) {
            $.ajax({
                url: '../../api/admin/toggle-user-status.php',
                method: 'POST',
                data: { user_id: userId, action: action },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Došlo je do greške.');
                }
            });
        }
    }
    </script>
</body>
</html>