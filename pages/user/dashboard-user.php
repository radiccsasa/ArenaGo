<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] != 'user') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


$reservations_query = "SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC";
    
$stmt = $conn->prepare($reservations_query);
if($stmt === false) {
    die("Greška u upitu: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Korisnički Dashboard - ArenaGo</title>
    
    
    <link href="../../__bootstrap_packages/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <?php require_once '../../utils/header-user.php'; ?>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="bi bi-person-circle text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <div class="col-md-8">
                                <h4 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                                <?php if($user['status'] == 'active'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktivan nalog</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-ban"></i> Blokiran nalog</span>
                                <?php endif; ?>
                                <span class="badge bg-info ms-2"><i class="bi bi-person"></i> Korisnik</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history text-primary"></i> Istorija rezervacija
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if($reservations_result && $reservations_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sportski centar</th>
                                            <th>Sport</th>
                                            <th>Datum</th>
                                            <th>Vreme</th>
                                            <th>Cena</th>
                                            <th>Status</th>
                                            <th>Rezervisano</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($reservation = $reservations_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($reservation['center_name'] ?? 'N/A'); ?></strong><br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($reservation['location'] ?? 'N/A'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    
                                                    $sportIcons = [
                                                        'Fudbal' => 'bi bi-football',
                                                        'fudbal' => 'bi bi-football',
                                                        'Košarka' => 'bi bi-basketball',
                                                        'Košarka' => 'bi bi-basketball',
                                                        'kosarka' => 'bi bi-basketball',
                                                        'Tenis' => 'bi bi-tennis',
                                                        'tenis' => 'bi bi-tennis',
                                                        'Odbojka' => 'bi bi-volleyball',
                                                        'odbojka' => 'bi bi-volleyball',
                                                        'Rukomet' => 'bi bi-handball',
                                                        'rukomet' => 'bi bi-handball',
                                                        'Teretana' => 'bi bi-dumbbell',
                                                        'teretana' => 'bi bi-dumbbell',
                                                        'Vaterpolo' => 'bi bi-water',
                                                        'vaterpolo' => 'bi bi-water',
                                                        'Bazen' => 'bi bi-water',
                                                        'bazen' => 'bi bi-water'
                                                    ];
                                                    
                                                    $sportName = $reservation['sport_type'] ?? 'Nepoznato';
                                                    $icon = isset($sportIcons[$sportName]) ? $sportIcons[$sportName] : 'bi bi-trophy';
                                                    ?>
                                                    <i class="<?php echo $icon; ?> text-primary"></i>
                                                    <?php echo htmlspecialchars($sportName); ?>
                                                </td>
                                                <td><?php echo isset($reservation['date']) ? date('d.m.Y.', strtotime($reservation['date'])) : 'N/A'; ?></td>
                                                <td><?php echo isset($reservation['time']) ? date('H:i', strtotime($reservation['time'])) : 'N/A'; ?></td>
                                                <td>
                                                    <strong><?php echo isset($reservation['price']) ? number_format($reservation['price'], 0, ',', '.') . ' RSD' : 'N/A'; ?></strong>
                                                    <?php if(!empty($reservation['action_discount'])): ?>
                                                        <br><span class="badge bg-danger">-<?php echo $reservation['action_discount']; ?>%</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    switch($reservation['status'] ?? '') {
                                                        case 'confirmed':
                                                            echo '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Potvrđeno</span>';
                                                            break;
                                                        case 'pending':
                                                            echo '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Na čekanju</span>';
                                                            break;
                                                        case 'cancelled':
                                                            echo '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Otkazano</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge bg-secondary">' . htmlspecialchars($reservation['status'] ?? 'Nepoznato') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo isset($reservation['created_at']) ? date('d.m.Y. H:i', strtotime($reservation['created_at'])) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                                <h5 class="mt-3">Nemate nijednu rezervaciju</h5>
                                <p class="text-muted">Pregledajte dostupne termine i rezervišite vaš termin.</p>
                                <a href="../terms/terms.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Pregledaj termine
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</body>
</html>