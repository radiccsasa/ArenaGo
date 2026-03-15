<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: centers.php");
    exit();
}

$center_id = $_GET['id'];

// Dohvatanje informacija o centru
$center_query = "
    SELECT 
        sc.*,
        u.email,
        u.status as user_status,
        u.id as user_id,
        u.created_at as user_created_at
    FROM sports_centers sc
    JOIN users u ON sc.user_id = u.id
    WHERE sc.id = ?
";
$stmt = $conn->prepare($center_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$center = $stmt->get_result()->fetch_assoc();

if(!$center) {
    header("Location: centers.php");
    exit();
}

// Detaljna statistika
$stats_query = "
    SELECT 
        COUNT(DISTINCT t.id) as total_terms,
        COUNT(DISTINCT CASE WHEN t.date >= CURDATE() THEN t.id END) as upcoming_terms,
        COUNT(DISTINCT r.id) as total_reservations,
        COUNT(DISTINCT CASE WHEN r.status = 'confirmed' THEN r.id END) as confirmed_reservations,
        COUNT(DISTINCT CASE WHEN r.status = 'pending' THEN r.id END) as pending_reservations,
        COUNT(DISTINCT c.id) as total_comments,
        COALESCE(AVG(rt.rating), 0) as avg_rating,
        COUNT(DISTINCT rt.id) as rating_count
    FROM sports_centers sc
    LEFT JOIN terms t ON sc.id = t.center_id
    LEFT JOIN reservations r ON t.id = r.term_id
    LEFT JOIN comments c ON sc.id = c.center_id
    LEFT JOIN ratings rt ON sc.id = rt.center_id
    WHERE sc.id = ?
    GROUP BY sc.id
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Sportovi po zastupljenosti
$sports_stats = "
    SELECT 
        s.name,
        COUNT(t.id) as term_count,
        COUNT(r.id) as reservation_count
    FROM sports s
    LEFT JOIN terms t ON s.id = t.sport_id AND t.center_id = ?
    LEFT JOIN reservations r ON t.id = r.term_id
    GROUP BY s.id
    HAVING term_count > 0
    ORDER BY term_count DESC
";
$stmt = $conn->prepare($sports_stats);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$sports_result = $stmt->get_result();

// Termini centra
$terms_query = "
    SELECT 
        t.*,
        s.name as sport_name,
        COUNT(r.id) as reservation_count,
        SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
    FROM terms t
    JOIN sports s ON t.sport_id = s.id
    LEFT JOIN reservations r ON t.id = r.term_id
    WHERE t.center_id = ?
    GROUP BY t.id
    ORDER BY t.date DESC, t.time DESC
";
$stmt = $conn->prepare($terms_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$terms_result = $stmt->get_result();

// Komentari za centar
$comments_query = "
    SELECT 
        c.*,
        u.name as user_name,
        u.id as user_id,
        u.status as user_status
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.center_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($center['name']); ?> - Admin Panel</title>
    <link href="../../__bootstrap_packages/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard-admin.php">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Navigacija -->
        <div class="mb-3">
            <a href="centers.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Nazad na centre
            </a>
        </div>

        <!-- Info kartica -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="text-primary mb-3"><?php echo htmlspecialchars($center['name']); ?></h2>
                        <p><i class="bi bi-geo-alt"></i> <strong>Lokacija:</strong> <?php echo htmlspecialchars($center['location'] ?? 'Nije navedeno'); ?></p>
                        <p><i class="bi bi-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($center['email']); ?></p>
                        <?php if(!empty($center['description'])): ?>
                            <p><i class="bi bi-info-circle"></i> <strong>Opis:</strong><br><?php echo nl2br(htmlspecialchars($center['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if($center['user_status'] == 'active'): ?>
                            <span class="badge bg-success p-3 fs-6">Aktivan</span>
                        <?php else: ?>
                            <span class="badge bg-danger p-3 fs-6">Blokiran</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Glavna statistika -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Ukupno termina</h6>
                        <h3><?php echo $stats['total_terms']; ?></h3>
                        <small><?php echo $stats['upcoming_terms']; ?> predstojećih</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Ukupno rezervacija</h6>
                        <h3><?php echo $stats['total_reservations']; ?></h3>
                        <small><?php echo $stats['confirmed_reservations']; ?> potvrđenih</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Komentari</h6>
                        <h3><?php echo $stats['total_comments']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Prosečna ocena</h6>
                        <h3><?php echo number_format($stats['avg_rating'], 1); ?></h3>
                        <small>(<?php echo $stats['rating_count']; ?> ocena)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sportovi po zastupljenosti -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Zastupljenost sportova</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sport</th>
                                <th>Broj termina</th>
                                <th>Broj rezervacija</th>
                                <th>Popunjenost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($sport = $sports_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sport['name']); ?></td>
                                    <td><?php echo $sport['term_count']; ?></td>
                                    <td><?php echo $sport['reservation_count']; ?></td>
                                    <td>
                                        <?php 
                                        if($sport['term_count'] > 0) {
                                            $percentage = round(($sport['reservation_count'] / $sport['term_count']) * 100);
                                            echo $percentage . '%';
                                        } else {
                                            echo '0%';
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

        <!-- Termini -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Termini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sport</th>
                                <th>Datum</th>
                                <th>Vreme</th>
                                <th>Cena</th>
                                <th>Kapacitet</th>
                                <th>Rezervacije</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($term = $terms_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($term['sport_name']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($term['date'])); ?></td>
                                    <td><?php echo $term['time']; ?></td>
                                    <td><?php echo number_format($term['price'], 0, ',', '.'); ?> RSD</td>
                                    <td><?php echo $term['capacity'] ?? 'N/A'; ?></td>
                                    <td><?php echo $term['confirmed_count']; ?>/<?php echo $term['reservation_count']; ?></td>
                                    <td>
                                        <?php if(strtotime($term['date']) < time()): ?>
                                            <span class="badge bg-secondary">Prošao</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aktivan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Komentari -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-chat"></i> Komentari</h5>
            </div>
            <div class="card-body">
                <?php if($comments_result->num_rows > 0): ?>
                    <?php while($comment = $comments_result->fetch_assoc()): ?>
                        <div class="border-bottom mb-3 pb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>
                                    <a href="user-details.php?id=<?php echo $comment['user_id']; ?>" class="text-decoration-none">
                                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($comment['user_name']); ?>
                                    </a>
                                    <?php if($comment['user_status'] != 'active'): ?>
                                        <span class="badge bg-danger">Blokiran</span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></small>
                            </div>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            <button class="btn btn-sm btn-danger" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                <i class="bi bi-trash"></i> Obriši komentar
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Nema komentara za ovaj centar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function deleteComment(commentId) {
        if(confirm('Da li ste sigurni da želite da obrišete ovaj komentar?')) {
            $.ajax({
                url: '../../api/admin/delete-comment.php',
                method: 'POST',
                data: { comment_id: commentId },
                success: function() {
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