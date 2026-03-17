
<?php
function renderTermCard($term) {
    // Provera da li je korisnik ulogovan i da li je običan korisnik
    $isUser = isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user';
    
    $sportName = $term['sport_name'] ?? $term['sport_type'] ?? 'Sport';
    $sportNameClean = strtolower(trim($sportName));

    $sportEmojis = [
        'Fudbal' => '⚽',
        'fudbal' => '⚽',
        'Košarka' => '🏀',
        'kosarka' => '🏀',
        'Tenis' => '🎾',
        'tenis' => '🎾',
        'Odbojka' => '🏐',
        'odbojka' => '🏐',
        'Rukomet' => '🤾',
        'rukomet' => '🤾',
        'Teretana' => '🏋️',
        'teretana' => '🏋️',
        'Vaterpolo' => '🤽',
        'vaterpolo' => '🤽',
        'Bazen' => '🏊',
        'bazen' => '🏊'
    ];
    
    $icon = '🏆'; // default trophy
    
    foreach ($sportEmojis as $key => $value) {
        if (strpos($sportNameClean, $key) !== false) {
            $icon = $value;
            break;
        }
    }
    
    $originalPrice = $term['price'] ?? 0;
    $discount = $term['action_discount'] ?? 0;
    $finalPrice = $originalPrice;
    $hasDiscount = false;
    
    if($discount > 0) {
        $finalPrice = $originalPrice * (1 - $discount/100);
        $hasDiscount = true;
    }
    ?>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title text-primary mb-0"><?php echo htmlspecialchars($term['center_name'] ?? 'Sportski centar'); ?></h5>
                    <span class="badge bg-primary">
                        <?php echo $icon; ?> <?php echo htmlspecialchars($sportName); ?>
                    </span>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($term['location'] ?? 'Lokacija'); ?>
                    </small>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="bi bi-calendar text-primary"></i> <?php echo date('d.m.Y', strtotime($term['date'] ?? 'now')); ?>
                        <br>
                        <i class="bi bi-clock text-primary"></i> <?php echo date('H:i', strtotime($term['time'] ?? 'now')); ?>
                    </div>
                    <div class="text-end">
                        <?php if($hasDiscount): ?>
                            <small class="text-muted text-decoration-line-through"><?php echo number_format($originalPrice, 0, ',', '.'); ?> RSD</small>
                            <br>
                            <span class="h5 text-danger"><?php echo number_format($finalPrice, 0, ',', '.'); ?> RSD</span>
                            <span class="badge bg-danger">-<?php echo $discount; ?>%</span>
                        <?php else: ?>
                            <span class="h5 text-primary"><?php echo number_format($originalPrice, 0, ',', '.'); ?> RSD</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($isUser): ?>
                    <!-- Dugme samo za obične korisnike -->
                    <button class="btn btn-primary w-100" onclick="zakaziTermin(<?php echo $term['id'] ?? 0; ?>)">
                        <i class="bi bi-calendar-check"></i> Zakaži
                    </button>
                <?php else: ?>
                    <!-- Poruka za neulogovane ili centre -->
                    <div class="alert alert-secondary text-center py-2 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            <?php if(!isset($_SESSION['user'])): ?>
                                <a href="/ArenaGo/pages/login/login.php" class="text-decoration-none">Prijavite se</a> da biste zakazali
                            <?php else: ?>
                                Samo korisnici mogu zakazati
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php } ?>