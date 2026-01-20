<div class="country-wrapper">
    <?php
    foreach ($countries as $country) {
        $featured = isset($country['featured']) && $country['featured'] ? 'featured' : '';
        ?>
        <div class="country-item <?php echo $featured; ?>">
            <img src="<?php echo $country['flag']; ?>" alt="<?php echo $country['name']; ?>">
            <a href="/sit-country/<?php echo $country['slug']; ?>">
                <div>
                    <h3><?php echo $country['name']; ?></h3>
                    <?php if(isset($country['program_count'])): ?>
                    <div class="program-count"><?php echo $country['program_count']; ?> Programs</div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php
    }
    ?>
</div>