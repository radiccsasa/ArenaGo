<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] != 'user') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$message = '';
$message_type = '';

// Procesiranje forme za ažuriranje profila
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $new_name = trim($_POST['name']);
        
        if(!empty($new_name)) {
            $update_query = "UPDATE users SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_name, $user_id);
            
            if($stmt->execute()) {
                $_SESSION['user']['name'] = $new_name;
                $message = "Ime je uspešno ažurirano!";
                $message_type = "success";
            } else {
                $message = "Greška pri ažuriranju imena.";
                $message_type = "danger";
            }
        }
    }
    
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Proveri trenutnu šifru
        $password_query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($password_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if(password_verify($current_password, $user_data['password'])) {
            if($new_password === $confirm_password) {
                if(strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if($stmt->execute()) {
                        $message = "Šifra je uspešno promenjena!";
                        $message_type = "success";
                    } else {
                        $message = "Greška pri promeni šifre.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Nova šifra mora imati najmanje 6 karaktera.";
                    $message_type = "warning";
                }
            } else {
                $message = "Nova šifra i potvrda se ne poklapaju.";
                $message_type = "warning";
            }
        } else {
            $message = "Trenutna šifra nije tačna.";
            $message_type = "danger";
        }
    }
}

// Dohvatanje podataka o korisniku
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
        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <!-- Leva strana - Informacije o korisniku -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-person-circle text-primary" style="font-size: 4rem;"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                        </p>
                                        <div>
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
                            
                            <!-- Desna strana - Opcije za izmenu profila -->
                            <div class="col-md-6 border-start">
                                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="name-tab" data-bs-toggle="tab" data-bs-target="#name" type="button" role="tab">
                                            <i class="bi bi-person"></i> Promeni ime
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                            <i class="bi bi-key"></i> Promeni šifru
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content p-3" id="profileTabsContent">
                                    <!-- Tab za promenu imena -->
                                    <div class="tab-pane fade show active" id="name" role="tabpanel">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Novo ime i prezime</label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                            </div>
                                            <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                                <i class="bi bi-check-circle"></i> Sačuvaj izmene
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Tab za promenu šifre -->
                                    <div class="tab-pane fade" id="password" role="tabpanel">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Trenutna šifra</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Nova šifra</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                                                <small class="text-muted">Minimalno 6 karaktera</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Potvrdi novu šifru</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <button type="submit" name="change_password" class="btn btn-primary w-100">
                                                <i class="bi bi-key"></i> Promeni šifru
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Istorija rezervacija (ostaje isto) -->
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
    
    <script>
    // Opciono: Dodati validaciju za šifre na klijentskoj strani
    document.getElementById('confirm_password')?.addEventListener('input', function() {
        var newPass = document.getElementById('new_password').value;
        var confirmPass = this.value;
        
        if(newPass !== confirmPass) {
            this.setCustomValidity('Šifre se ne poklapaju');
        } else {
            this.setCustomValidity('');
        }
    });
    </script>
</body>
</html>