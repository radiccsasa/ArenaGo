<?php
session_start();
require_once "../../DB/db.config.php";
require_once "../../utils/term-card.php";
require_once "../../utils/center-card.php";

$filter_date = $_GET['date'] ?? '';
$filter_sport = $_GET['sport'] ?? '';
$filter_price_min = $_GET['price_min'] ?? '';
$filter_price_max = $_GET['price_max'] ?? '';
$filter_location = $_GET['location'] ?? '';
$filter_center = $_GET['center'] ?? '';


$sports_query = "SELECT * FROM sports ORDER BY name";
$sports_result = $conn->query($sports_query);


$centers_dropdown_query = "SELECT id, name FROM sports_centers ORDER BY name";
$centers_dropdown_result = $conn->query($centers_dropdown_query);


$terms_query = "
    SELECT 
        t.*,
        sc.name as center_name,
        sc.location,
        s.name as sport_name,
        s.id as sport_id
    FROM terms t
    JOIN sports_centers sc ON t.center_id = sc.id
    JOIN sports s ON t.sport_id = s.id
    WHERE 1=1
";

$params = [];
$types = "";

if(!empty($filter_date)) {
    $terms_query .= " AND t.date = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if(!empty($filter_sport)) {
    $terms_query .= " AND s.id = ?";
    $params[] = $filter_sport;
    $types .= "i";
}

if(!empty($filter_center)) {
    $terms_query .= " AND sc.name LIKE ?";
    $params[] = "%$filter_center%";
    $types .= "s";
}

if(!empty($filter_location)) {
    $terms_query .= " AND sc.location LIKE ?";
    $params[] = "%$filter_location%";
    $types .= "s";
}

if(!empty($filter_price_min)) {
    $terms_query .= " AND t.price >= ?";
    $params[] = $filter_price_min;
    $types .= "d";
}

if(!empty($filter_price_max)) {
    $terms_query .= " AND t.price <= ?";
    $params[] = $filter_price_max;
    $types .= "d";
}

$terms_query .= " ORDER BY t.date, t.time LIMIT 8";

$stmt = $conn->prepare($terms_query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$terms_result = $stmt->get_result();


// Dohvatanje sportskih centara sa prosečnim rejtingom - SA FILTERIMA
$centers_query = "
    SELECT 
        sc.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as rating_count
    FROM sports_centers sc
    LEFT JOIN ratings r ON sc.id = r.center_id
    WHERE 1=1
";

$center_params = [];
$center_types = "";

// Dodajemo filter za lokaciju (grad)
if(!empty($filter_location)) {
    $centers_query .= " AND sc.location LIKE ?";
    $center_params[] = "%$filter_location%";
    $center_types .= "s";
}

// Dodajemo filter za naziv centra
if(!empty($filter_center)) {
    $centers_query .= " AND sc.name LIKE ?";
    $center_params[] = "%$filter_center%";
    $center_types .= "s";
}

$centers_query .= " GROUP BY sc.id ORDER BY avg_rating DESC, sc.name LIMIT 8";

$stmt_centers = $conn->prepare($centers_query);
if(!empty($center_params)) {
    $stmt_centers->bind_param($center_types, ...$center_params);
}
$stmt_centers->execute();
$centers_result = $stmt_centers->get_result();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArenaGo - Sportski termini i centri</title>
    
    <link href="../../__bootstrap_packages/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-calendar-check"></i> ArenaGo
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user'])): ?>
                        <?php if($_SESSION['user']['role'] == 'center'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/ArenaGo/pages/center/dashboard-center.php">
                                    <i class="bi bi-speedometer2"></i> Profil
                                </a>
                            </li>
                        <?php elseif($_SESSION['user']['role'] == 'user'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/ArenaGo/pages/user/dashboard-user.php">
                                    <i class="bi bi-speedometer2"></i> Profil
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-primary ms-2" href="/ArenaGo/api/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Odjavi se
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/ArenaGo/pages/login/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Prijavi se
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-primary ms-2" href="/ArenaGo/pages/register/register.php">
                                <i class="bi bi-person-plus"></i> Registracija
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-3">
            <i class="bi bi-clock text-primary"></i> Dostupni termini
        </h3>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="index.php" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Datum</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sport</label>
                        <select name="sport" class="form-select">
                            <option value="">Svi sportovi</option>
                            <?php while($sport = $sports_result->fetch_assoc()): ?>
                                <option value="<?php echo $sport['id']; ?>" <?php echo $filter_sport == $sport['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sport['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Lokacija</label>
                        <input type="text" name="location" class="form-control" placeholder="Grad" value="<?php echo htmlspecialchars($filter_location); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sportski centar</label>
                        <input type="text" name="center" class="form-control" placeholder="Naziv centra" value="<?php echo htmlspecialchars($filter_center); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cena od</label>
                        <input type="number" name="price_min" class="form-control" placeholder="Min" value="<?php echo $filter_price_min; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cena do</label>
                        <input type="number" name="price_max" class="form-control" placeholder="Max" value="<?php echo $filter_price_max; ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtriraj
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-eraser"></i> Resetuj filtere
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-5">
            <?php if($terms_result->num_rows > 0): ?>
                <?php while($term = $terms_result->fetch_assoc()): ?>
                    <?php renderTermCard($term); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nema dostupnih termina za zadate filtere.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sekcija sa sportskim centrima -->
        <h3 class="mb-3">
            <i class="bi bi-trophy text-primary"></i> Najbolje ocenjeni Sportski Centri
        </h3>
        <div class="row">
            <?php if($centers_result->num_rows > 0): ?>
                <?php while($center = $centers_result->fetch_assoc()): ?>
                    <?php renderCenterCard($center); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nema sportskih centara za zadate filtere.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function zakaziTermin(termId) {
        <?php if(!isset($_SESSION['user'])): ?>
            if(confirm('Morate biti prijavljeni da biste zakazali termin. Idite na login?')) {
                window.location.href = '/ArenaGo/pages/login/login.php';
            }
        <?php else: ?>
            $.ajax({
                url: '../../api/zakazi-termin.php',
                method: 'POST',
                data: { term_id: termId },
                success: function(response) {
                    alert('Termin je uspešno zakazan! Čeka potvrdu centra.');
                    location.reload();
                },
                error: function() {
                    alert('Došlo je do greške prilikom zakazivanja.');
                }
            });
        <?php endif; ?>
    }
    </script>
</body>
</html>