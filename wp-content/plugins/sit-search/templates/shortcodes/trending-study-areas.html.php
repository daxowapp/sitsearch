<div class="trending-areas-wrapper">
    <?php
    foreach ($areas as $area) {
        ?>
        <div class="trending-item">
            <span class="count-badge"><?php echo $area['count']; ?></span>
            <h3><?php echo $area['name']; ?></h3>
            <p><?php echo $area['count']; ?> Program<?php echo $area['count'] > 1 ? 's' : ''; ?> in this Area</p>
            <img src="<?php echo $area['image_url']; ?>" alt="<?php echo $area['name']; ?>" onerror="this.onerror=null;this.src='<?= STI_SEARCH_URL ?>assets/images/graduation-gown.png';">
            <a href="/results/?speciality=<?php echo $area['id']; ?>">Explore <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php
    }
    ?>
</div>