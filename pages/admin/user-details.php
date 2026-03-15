<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    header("Location: users.php");
    exit();
}

$reservations_query = "
    SELECT r.*, t.date, t.time, t.price, sc.name as center_name, sc.id as center_id
    FROM reservations r
    JOIN terms t ON r.term_id = t.id
    JOIN sports_centers sc ON t.center_id = sc.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($reservations_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();

$comments_query = "
    SELECT c.*, sc.name as center_name, sc.id as center_id
    FROM comments c
    JOIN sports_centers sc ON c.center_id = sc.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalji korisnika - <?php echo htmlspecialchars($user['name']); ?></title>
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
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p>
                            <?php if($user['role'] == 'center'): ?>
                                <span class="badge bg-success">Sportski centar</span>
                            <?php else: ?>
                                <span class="badge bg-info">Korisnik</span>
                            <?php endif; ?>
                            
                            <?php if($user['status'] == 'active'): ?>
                                <span class="badge bg-success">Aktivan</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Blokiran</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Nazad
                        </a>
                        <?php if($user['status'] == 'active'): ?>
                            <button class="btn btn-danger ms-2" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'block')">
                                <i class="bi bi-ban"></i> Blokiraj
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success ms-2" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'unblock')">
                                <i class="bi bi-check-circle"></i> Odblokiraj
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Rezervacije korisnika</h5>
            </div>
            <div class="card-body">
                <?php if($reservations->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sportski centar</th>
                                    <th>Datum</th>
                                    <th>Vreme</th>
                                    <th>Cena</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($res = $reservations->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="center-details.php?id=<?php echo $res['center_id']; ?>">
                                            <?php echo htmlspecialchars($res['center_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d.m.Y', strtotime($res['date'])); ?></td>
                                    <td><?php echo $res['time']; ?></td>
                                    <td><?php echo number_format($res['price'], 0, ',', '.'); ?> RSD</td>
                                    <td>
                                        <?php
                                        switch($res['status']) {
                                            case 'confirmed': echo '<span class="badge bg-success">Potvrđeno</span>'; break;
                                            case 'pending': echo '<span class="badge bg-warning">Na čekanju</span>'; break;
                                            case 'cancelled': echo '<span class="badge bg-danger">Otkazano</span>'; break;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Korisnik nema rezervacija.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-chat"></i> Komentari korisnika</h5>
            </div>
            <div class="card-body">
                <?php if($comments->num_rows > 0): ?>
                    <?php while($comment = $comments->fetch_assoc()): ?>
                        <div class="border-bottom mb-3 pb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>
                                    <a href="/ArenaGo/pages/admin/center-details.php?id=<?php echo $comment['center_id']; ?>">
                                        <?php echo htmlspecialchars($comment['center_name']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></small>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            <button class="btn btn-sm btn-danger" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                <i class="bi bi-trash"></i> Obriši komentar
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Korisnik nema komentara.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function toggleUserStatus(userId, action) {
        if(confirm(`Da li ste sigurni da želite da ${action == 'block' ? 'blokirate' : 'odblokirate'} ovog korisnika?`)) {
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

    function deleteComment(commentId) {
        if(confirm('Da li ste sigurni da želite da obrišete ovaj komentar?')) {
            $.ajax({
                url: '../../api/admin/delete-comment.php',
                method: 'POST',
                data: { comment_id: commentId },
                success: function() {
                    location.reload();
                }
            });
        }
    }
    </script>
</body>
</html>