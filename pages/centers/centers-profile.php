<?php
session_start();
require_once "../../DB/db.config.php";
require_once "../../utils/term-card.php";
require_once "../../utils/toast/toast.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /ArenaGo/pages/index/index.php");
    exit();
}

$center_id = $_GET['id'];

$center_query = "
    SELECT 
        sc.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as rating_count
    FROM sports_centers sc
    LEFT JOIN ratings r ON sc.id = r.center_id
    WHERE sc.id = ?
    GROUP BY sc.id
";
$stmt = $conn->prepare($center_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$center_result = $stmt->get_result();

if ($center_result->num_rows == 0) {
    header("Location: /ArenaGo/pages/index/index.php");
    exit();
}

$center = $center_result->fetch_assoc();

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
    WHERE t.center_id = ?
    AND t.date >= CURDATE() 
    ORDER BY t.date, t.time
";
$stmt = $conn->prepare($terms_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$terms_result = $stmt->get_result();

$comments_query = "
    SELECT 
        c.*,
        u.name as user_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.center_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$comments_result = $stmt->get_result();

$user_rating = 0;
if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user') {
    $user_id = $_SESSION['user']['id'];
    $rating_check = "SELECT rating FROM ratings WHERE center_id = ? AND user_id = ?";
    $stmt = $conn->prepare($rating_check);
    $stmt->bind_param("ii", $center_id, $user_id);
    $stmt->execute();
    $rating_result = $stmt->get_result();
    if ($rating_result->num_rows > 0) {
        $user_rating = $rating_result->fetch_assoc()['rating'];
    }
}

$avg_rating = $center['avg_rating'] ?? 0;
$fullStars = floor($avg_rating);
$halfStar = ($avg_rating - $fullStars) >= 0.5;
$emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
?>

<!DOCTYPE html>
<html lang="sr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($center['name']); ?> - ArenaGo</title>

    <link href="../../__bootstrap_packages/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/ArenaGo/pages/index/index.php">
                <i class="bi bi-calendar-check"></i> ArenaGo
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user']['role'] == 'center'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/ArenaGo/pages/center/dashboard-center.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] == 'user'): ?>
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
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h2 class="card-title text-primary mb-3"><?php echo htmlspecialchars($center['name']); ?></h2>

                                <p class="mb-2">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong>Lokacija:</strong> <?php echo htmlspecialchars($center['location'] ?? 'Nije navedeno'); ?>
                                </p>

                                <?php if (!empty($center['description'])): ?>
                                    <p class="mb-3">
                                        <i class="bi bi-info-circle text-primary"></i>
                                        <strong>Opis:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($center['description'])); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <strong>Prosečna ocena:</strong>
                                    <div class="d-flex align-items-center">
                                        <?php for ($i = 1; $i <= $fullStars; $i++): ?>
                                            <i class="bi bi-star-fill text-warning fs-5"></i>
                                        <?php endfor; ?>

                                        <?php if ($halfStar): ?>
                                            <i class="bi bi-star-half text-warning fs-5"></i>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $emptyStars; $i++): ?>
                                            <i class="bi bi-star text-warning fs-5"></i>
                                        <?php endfor; ?>

                                        <span class="ms-2">
                                            <strong><?php echo number_format($avg_rating, 1); ?></strong>
                                            (<?php echo $center['rating_count']; ?> ocena)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user'): ?>
                                <div class="col-md-4 border-start">
                                    <h5 class="mb-3">Ocenite centar</h5>
                                    <div class="rating-stars mb-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo ($i <= $user_rating) ? '-fill' : ''; ?> text-warning fs-3"
                                                style="cursor: pointer;"
                                                onclick="rateCenter(<?php echo $center_id; ?>, <?php echo $i; ?>)"
                                                onmouseover="hoverStar(<?php echo $i; ?>)"
                                                onmouseout="resetStars()"
                                                id="star-<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                    </div>

                                    <h5 class="mb-3">Dodaj komentar</h5>
                                    <form id="commentForm">
                                        <input type="hidden" name="center_id" value="<?php echo $center_id; ?>">
                                        <div class="mb-3">
                                            <textarea name="comment" class="form-control" rows="3" placeholder="Vaš komentar..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-chat"></i> Postavi komentar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3">
            <i class="bi bi-clock text-primary"></i> Termini sportskog centra <?php echo htmlspecialchars($center['name']); ?>
        </h3>

        <div class="row mb-5">
            <?php if ($terms_result->num_rows > 0): ?>
                <?php while ($term = $terms_result->fetch_assoc()): ?>
                    <?php renderTermCard($term); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Ovaj sportski centar trenutno nema dostupnih termina.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="mb-3">
            <i class="bi bi-chat text-primary"></i> Komentari
        </h3>

        <div class="row mb-4">
            <div class="col-md-12">
                <?php if ($comments_result->num_rows > 0): ?>
                    <?php while ($comment = $comments_result->fetch_assoc()): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="card-subtitle text-primary">
                                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($comment['user_name']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y. H:i', strtotime($comment['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Još uvek nema komentara za ovaj centar.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>

    <script>
        // Funkcija za zakazivanje termina
        function zakaziTermin(termId) {
            <?php if (!isset($_SESSION['user'])): ?>
                if (confirm('Morate biti prijavljeni da biste zakazali termin. Idite na login?')) {
                    window.location.href = '/ArenaGo/pages/login/login.php';
                }
            <?php else: ?>
                $.ajax({
                    url: '../../api/zakazi-termin.php',
                    method: 'POST',
                    data: {
                        term_id: termId
                    },
                    success: function(response) {
                        showToast('Termin je uspešno zakazan! Čeka potvrdu centra.');
                        location.reload();
                    },
                    error: function() {
                        showToast('Došlo je do greške prilikom zakazivanja.', "error");
                    }
                });
            <?php endif; ?>
        }

        // Funkcije za ocenjivanje
        function rateCenter(centerId, rating) {
            $.ajax({
                url: '../../api/rate-center.php',
                method: 'POST',
                data: {
                    center_id: centerId,
                    rating: rating
                },
                success: function(response) {
                    showToast('Uspešno ste ocenili centar!');
                    location.reload();
                },
                error: function(xhr) {
                    showToast('Došlo je do greške prilikom ocenjivanja.', "error");
                }
            });
        }

        function hoverStar(starNum) {
            for (let i = 1; i <= 5; i++) {
                if (i <= starNum) {
                    $('#star-' + i).removeClass('bi-star').addClass('bi-star-fill');
                }
            }
        }

        function resetStars() {
            <?php for ($i = 1; $i <= 5; $i++): ?>
                $('#star-<?php echo $i; ?>').removeClass('bi-star-fill').addClass('bi-star<?php echo ($i <= $user_rating) ? '-fill' : ''; ?>');
            <?php endfor; ?>
        }

        $('#commentForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: '../../api/add-comment.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showToast('Komentar je uspešno dodat!', "success");
                    location.reload();
                },
                error: function() {
                    showToast('Došlo je do greške prilikom dodavanja komentara.', "error");
                }
            });
        });
    </script>
</body>

</html>