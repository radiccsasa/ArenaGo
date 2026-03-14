<?php

function renderCenterCard($center) {
    $rating = $center['avg_rating'] ?? 0;
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    ?>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-2"><?php echo htmlspecialchars($center['name'] ?? 'Sportski centar'); ?></h5>
                
                <p class="card-text mb-2">
                    <i class="bi bi-geo-alt text-primary"></i> 
                    <?php echo htmlspecialchars($center['location'] ?? 'Lokacija'); ?>
                </p>
                
                <div class="mb-3">
                    <?php for($i = 1; $i <= $fullStars; $i++): ?>
                        <i class="bi bi-star-fill text-warning"></i>
                    <?php endfor; ?>
                    
                    <?php if($halfStar): ?>
                        <i class="bi bi-star-half text-warning"></i>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $emptyStars; $i++): ?>
                        <i class="bi bi-star text-warning"></i>
                    <?php endfor; ?>
                    
                    <small class="text-muted ms-2">(<?php echo number_format($rating, 1); ?>)</small>
                </div>
                
                <a href="/ArenaGo/pages/centers/centers-profile.php?id=<?php echo $center['id'] ?? 0; ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-info-circle"></i> Detaljnije
                </a>
            </div>
        </div>
    </div>
<?php } ?>