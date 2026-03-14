<?php
function renderTermCard($term) {
    $sportIcons = [
        'Fudbal' => 'bi-football',
        'fudbal' => 'bi-football',
        'Košarka' => 'bi-basketball',
        'kosarka' => 'bi-basketball',
        'Tenis' => 'bi-tennis',
        'tenis' => 'bi-tennis',
        'Odbojka' => 'bi-volleyball',
        'odbojka' => 'bi-volleyball',
        'Rukomet' => 'bi-handball',
        'rukomet' => 'bi-handball',
        'Teretana' => 'bi-dumbbell',
        'teretana' => 'bi-dumbbell',
        'Vaterpolo' => 'bi-water',
        'vaterpolo' => 'bi-water',
        'Bazen' => 'bi-water',
        'bazen' => 'bi-water'
    ];
    
    $sportName = $term['sport_name'] ?? $term['sport_type'] ?? 'Sport';
    $icon = isset($sportIcons[$sportName]) ? $sportIcons[$sportName] : 'bi-trophy';
    

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
                        <i class="bi <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($sportName); ?>
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
                
                <button class="btn btn-primary w-100" onclick="zakaziTermin(<?php echo $term['id'] ?? 0; ?>)">
                    <i class="bi bi-calendar-check"></i> Zakaži
                </button>
            </div>
        </div>
    </div>
<?php } ?>