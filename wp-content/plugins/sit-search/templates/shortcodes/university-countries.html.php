<div class="sit-countries-grid">
    <?php foreach ($countries as $country): 
        $featuredClass = (isset($country['featured']) && $country['featured']) ? 'sit-country-featured' : '';
    ?>
        <a href="/sit-country/<?php echo $country['slug']; ?>" class="sit-country-card sit-ui-card sit-reveal <?php echo $featuredClass; ?>">
            <div class="sc-flag-wrap">
                <img src="<?php echo $country['flag']; ?>" alt="<?php echo $country['name']; ?>">
            </div>
            <div class="sc-content">
                <h3><?php echo $country['name']; ?></h3>
                <?php if(isset($country['program_count'])): ?>
                    <span class="sc-count"><?php echo $country['program_count']; ?> Program<?php echo $country['program_count'] != 1 ? 's' : ''; ?></span>
                <?php endif; ?>
            </div>
            <div class="sc-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </div>
        </a>
    <?php endforeach; ?>
</div>