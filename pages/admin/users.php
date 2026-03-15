<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: /ArenaGo/pages/login/login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// Prikazuje SAMO obične korisnike (role = 'user')
$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];
$types = "";

if(!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje korisnicima - ArenaGo</title>
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
                        <a class="nav-link active" href="users.php">
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
        <!-- Samo search, bez filtera -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <label class="form-label">Pretraga po imenu</label>
                        <input type="text" name="search" class="form-control" placeholder="Unesite ime korisnika..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Pretraži
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kartice korisnika (samo obični korisnici) -->
        <div class="row">
            <?php if($users_result->num_rows > 0): ?>
                <?php while($user = $users_result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-person-circle fs-1 text-primary me-3"></i>
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <!-- Uklonjen badge za role jer su svi useri -->
                                    <?php if($user['status'] == 'active'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktivan</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-ban"></i> Blokiran</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detalji korisnika
                                    </a>
                                    <?php if($user['status'] == 'active'): ?>
                                        <button class="btn btn-outline-danger" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'block')">
                                            <i class="bi bi-ban"></i> Blokiraj
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'unblock')">
                                            <i class="bi bi-check-circle"></i> Odblokiraj
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
                        <i class="bi bi-info-circle"></i> Nema korisnika za prikaz.
                    </div>
                </div>
            <?php endif; ?>
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
    </script>
</body>
</html>