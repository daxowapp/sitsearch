<div class="sit-trending-areas">
    <?php foreach ($areas as $area): ?>
        <a href="/results/?speciality=<?php echo $area['id']; ?>" class="sit-trending-card sit-ui-card sit-reveal">
            <div class="sta-icon-wrapper">
                <img src="<?php echo isset($area['image_url']) && !empty($area['image_url']) ? $area['image_url'] : STI_SEARCH_URL . 'assets/images/graduation-gown.png'; ?>" alt="<?php echo $area['name']; ?>" onerror="this.onerror=null;this.src='<?= STI_SEARCH_URL ?>assets/images/graduation-gown.png';">
            </div>
            <div class="sta-content">
                <h3><?php echo $area['name']; ?></h3>
                <p><?php echo $area['count']; ?> Program<?php echo $area['count'] != 1 ? 's' : ''; ?></p>
            </div>
            <div class="sta-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </div>
        </a>
    <?php endforeach; ?>
</div>