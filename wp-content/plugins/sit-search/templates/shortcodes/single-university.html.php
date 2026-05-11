<?php
/**
 * Single University Page Template — Redesigned (up-* namespace)
 * Matches the pp-* program page design system.
 */

// Safely get values with defaults
$title           = !empty($program['title']) ? $program['title'] : 'University';
$city            = !empty($program['city']) ? $program['city'] : '';
$country         = !empty($program['pro_country']) ? $program['pro_country'] : 'Turkey';
$description     = !empty($program['description']) ? $program['description'] : '';
$year_founded    = !empty($program['Year_Founded']) ? $program['Year_Founded'] : '';
$total_students  = !empty($program['total_students']) ? $program['total_students'] : '';
$ranking         = !empty($program['ranking']) ? $program['ranking'] : '--';
$image_url       = !empty($program['image_url']) ? $program['image_url'] : '';
$uni_logo        = !empty($program['uni_logo']) ? $program['uni_logo'] : '';
$brochure_url    = !empty($program['University_brochure']) ? $program['University_brochure'] : '';
$uni_id          = !empty($program['unic_id']) ? $program['unic_id'] : '';
$location_str    = $city ? "$city, $country" : $country;
?>

<!-- ══════════════════════════════════════════════
     HERO SECTION
     ══════════════════════════════════════════════ -->
<div class="up-hero">
  <div class="up-hero-inner">
    <!-- Logo & Text Row -->
    <div class="up-hero-profile">
      <?php if ($uni_logo): ?>
      <div class="up-hero-logo">
        <img src="<?= esc_url($uni_logo) ?>" alt="<?= esc_attr($title) ?> Logo" onerror="this.onerror=null; this.src='https://placehold.co/128x128?text=U';">
      </div>
      <?php endif; ?>

      <div class="up-hero-text">
        <h1 class="up-hero-title"><?= esc_html($title) ?></h1>

      <?php if ($location_str): ?>
      <div class="up-hero-location">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <?= esc_html($location_str) ?>
      </div>
      <?php endif; ?>

      <!-- Pill Badges -->
      <div class="up-hero-pills">
        <?php if ($year_founded): ?>
        <span class="up-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> Founded <?= esc_html($year_founded) ?></span>
        <?php endif; ?>
        <?php if ($total_students): ?>
        <span class="up-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> <?= esc_html($total_students) ?> Students</span>
        <?php endif; ?>
        <span class="up-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg> QS #<?= esc_html($ranking) ?></span>
      </div>
      </div>
    </div> <!-- /up-hero-profile -->

    <div class="up-hero-actions">
      <button class="up-btn-apply trigger-modal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Apply Now
      </button>
      <?php if ($brochure_url): ?>
      <a href="<?= esc_url($brochure_url) ?>" class="up-btn-secondary" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Brochure
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Breadcrumb -->
<div class="up-breadcrumb">
  <div class="up-breadcrumb-inner">
    <a href="/">🏠 Home</a>
    <span class="up-bc-sep">›</span>
    <span class="up-bc-current"><?= esc_html($title) ?></span>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     MAIN LAYOUT — Two columns
     ══════════════════════════════════════════════ -->
<div class="up-layout">

  <!-- ──── LEFT COLUMN ──── -->
  <div class="up-main">

    <!-- GEO Summary — Entity-rich, citable by AI engines -->
    <div class="up-geo-summary">
      <div class="up-geo-summary-bar"></div>
      <div class="up-geo-summary-body">
        <p><strong><?= esc_html($title) ?></strong> is a university located in <?= esc_html($location_str) ?>.<?php if ($year_founded): ?> Founded in <?= esc_html($year_founded) ?>.<?php endif; ?> QS World Ranking: #<?= esc_html($ranking) ?>.<?php if ($total_students): ?> The university has <?= esc_html($total_students) ?> students enrolled across its programs.<?php endif; ?></p>
        <time datetime="<?= get_the_modified_date('c', $uni_id) ?>" class="up-geo-date">Updated <?= get_the_modified_date('M j, Y', $uni_id) ?></time>
      </div>
    </div>

    <!-- ── University Details ── -->
    <div class="up-card" id="key-information">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        University Details
      </div>

      <div class="up-stats-grid">
        <!-- Type -->
        <div class="up-stat-tile up-stat-blue">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">TYPE</span>
            <span class="up-stat-value">University</span>
          </div>
        </div>

        <!-- Founded -->
        <?php if ($year_founded): ?>
        <div class="up-stat-tile up-stat-purple">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">FOUNDED</span>
            <span class="up-stat-value"><?= esc_html($year_founded) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Location -->
        <div class="up-stat-tile up-stat-orange">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">LOCATION</span>
            <span class="up-stat-value"><?= esc_html($location_str) ?></span>
          </div>
        </div>

        <!-- Students -->
        <?php if ($total_students): ?>
        <div class="up-stat-tile up-stat-green">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">STUDENTS</span>
            <span class="up-stat-value"><?= esc_html($total_students) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Ranking -->
        <div class="up-stat-tile up-stat-gold">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">QS RANKING</span>
            <span class="up-stat-value">#<?= esc_html($ranking) ?></span>
          </div>
        </div>

        <!-- Accommodation -->
        <div class="up-stat-tile up-stat-teal">
          <div class="up-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
          <div class="up-stat-info">
            <span class="up-stat-label">ACCOMMODATION</span>
            <span class="up-stat-value">Available</span>
          </div>
        </div>
      </div>

      <div class="up-card-cta">
        <button class="up-check-btn trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Check Eligibility
        </button>
      </div>
    </div>

    <!-- ── University Overview ── -->
    <?php if ($description): ?>
    <div class="up-card" id="course-overview">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        About <?= esc_html($title) ?>
      </div>
      <div class="up-card-body">
        <div class="up-overview-text"><?= $description ?></div>
      </div>
      <div class="up-card-actions">
        <button class="up-btn-apply trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Help Me Apply
        </button>
        <?php if ($brochure_url): ?>
        <a href="<?= esc_url($brochure_url) ?>" class="up-btn-outline" target="_blank">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Download Brochure
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── CTA Banner ── -->
    <div class="up-cta-banner">
      <div class="up-cta-decor"></div>
      <div class="up-cta-decor up-cta-decor-2"></div>
      <div class="up-cta-content">
        <div class="up-cta-text">
          <h3>Start Your Journey</h3>
          <p>Study at <?= esc_html($title) ?> in <?= esc_html($country) ?></p>
        </div>
        <button class="up-cta-btn trigger-modal">Apply Now <span>→</span></button>
      </div>
    </div>

    <!-- ── QS Ranking Card ── -->
    <div class="up-card" id="university-detail">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        World Rankings
      </div>
      <div class="up-card-body">
        <div class="up-ranking-row">
          <div class="up-ranking-logo">
            <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/qs-top-universities-vector-logo-2022-1.png" alt="QS Ranking Logo">
          </div>
          <div class="up-ranking-info">
            <span class="up-ranking-label">QS World University Rankings</span>
            <span class="up-ranking-value">#<?= esc_html($ranking) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── University Programs ── -->
    <?php if(!empty($other_uni)): ?>
    <div class="up-card" id="other-university">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Programs at <?= esc_html($title) ?>
        <a href="/university/?uni-id=<?= esc_attr($uni_id) ?>" class="up-view-all">View All →</a>
      </div>

      <div class="up-card-body">
        <!-- Keyword Search & Level Filter -->
        <div class="uni-filters">
          <div class="uni-search-input">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="programKeyword" placeholder="Search programs by keyword...">
            <button id="clearSearchBtn" class="uni-clear-search">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="uni-level-filter">
             <select id="programLevel">
               <option value="">All Levels</option>
               <option value="associate">Associate</option>
               <option value="bachelor">Bachelor</option>
               <option value="master">Master</option>
               <option value="phd">PhD / Doctorate</option>
             </select>
          </div>
        </div>

        <div class="uni-programs-results">
          <div class="uni-programs-count">
            Showing <span id="visibleCount">0</span> out of <span id="totalCount">0</span> programs
          </div>
        </div>

        <!-- Programs List -->
        <div id="programsContainer" class="uni-programs-list">
          <?php
            $program_counter = 0;
            $items_per_page = 10;
            foreach ($other_uni as $index => $university):
              $page_number = ceil(($index + 1) / $items_per_page);
              $display_style = ($page_number == 1) ? '' : 'style="display: none;"';
              $level = isset($university['level']) ? $university['level'] : 'N/A';
              $level_class = 'level-default';
              $level_lower = strtolower($level);
              if (strpos($level_lower, 'bachelor') !== false) $level_class = 'level-bachelor';
              elseif (strpos($level_lower, 'master') !== false) $level_class = 'level-master';
              elseif (strpos($level_lower, 'phd') !== false || strpos($level_lower, 'doctor') !== false) $level_class = 'level-phd';
              elseif (strpos($level_lower, 'associate') !== false) $level_class = 'level-associate';
          ?>

          <div class="uni-program-row" data-page="<?= $page_number ?>" <?= $display_style ?>>
            <!-- Left: Level Badge + Program Info -->
            <div class="upr-main">
              <div class="upr-level-badge <?= $level_class ?>">
                <?= $level ?>
              </div>
              <div class="upr-info">
                <a href="<?= isset($university['guid']) ? $university['guid'] : $university['link'] ?>" class="upr-title">
                  <?= $university['title'] ?>
                </a>
                <div class="upr-meta-tags">
                  <span class="upr-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php
                      $dur = isset($university['duration']) ? $university['duration'] : '';
                      echo $dur ? $dur . ($dur == 1 ? ' Year' : ' Years') : 'N/A';
                    ?>
                  </span>
                  <span class="upr-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                    <?= isset($university['language']) ? $university['language'] : 'English' ?>
                  </span>
                  <span class="upr-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <?= isset($university['study_mode']) ? $university['study_mode'] : 'Full-time' ?>
                  </span>
                  <span class="upr-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <?= isset($university['intake']) ? $university['intake'] : 'September' ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right: Tuition + CTA -->
            <div class="upr-actions">
              <div class="upr-tuition">
                <span class="upr-tuition-label">Tuition</span>
                <span class="upr-tuition-value"><?php
                  if(isset($university['discounted_fee']) && $university['discounted_fee'] != '' && $university['discounted_fee'] != '0'){
                    echo esc_html($university['Tuition_Currency']) . ' ' . esc_html($university['discounted_fee']);
                  } else {
                    echo esc_html($university['Tuition_Currency']) . ' ' . esc_html($university['fee']);
                  }
                ?></span>
              </div>
              <a href="<?= isset($university['guid']) ? $university['guid'] : $university['link'] ?>" class="upr-view-btn">
                View
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </a>
            </div>
          </div>
          <?php
            endforeach;
            $total_pages = ceil(count($other_uni) / $items_per_page);
          ?>

          <div id="noResultsMessage" class="uni-no-results" style="display: none;">
            <div class="uni-no-results-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3>No matching programs found</h3>
            <p>Try a different keyword or clear your search.</p>
            <button id="clearSearch" class="uni-clear-button">Clear Search</button>
          </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="uni-pagination">
          <button class="uni-pagination-button" id="prevPage" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Previous
          </button>
          <div class="uni-pagination-pages">
            <?php
            $max_buttons = 8;
            if ($total_pages <= $max_buttons) {
              $start_page = 1; $end_page = $total_pages;
            } else {
              $start_page = max(1, min($total_pages - $max_buttons + 1, 1));
              $end_page = min($total_pages, $max_buttons);
            }
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <button class="uni-pagination-page <?= ($i == 1) ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></button>
            <?php endfor; ?>
            <?php if ($total_pages > $max_buttons): ?>
            <span class="uni-pagination-dots">...</span>
            <button class="uni-pagination-page" data-page="<?= $total_pages ?>"><?= $total_pages ?></button>
            <?php endif; ?>
          </div>
          <button class="uni-pagination-button" id="nextPage" <?= ($total_pages <= 1) ? 'disabled' : '' ?>>
            Next
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════
         GEO: COMPARISON TABLE — Structured data for AI engines
         ══════════════════════════════════════════════ -->
    <div class="up-card" id="comparison-table">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect><line x1="3" y1="9" x2="21" y2="9" stroke-width="2"></line><line x1="3" y1="15" x2="21" y2="15" stroke-width="2"></line><line x1="12" y1="3" x2="12" y2="21" stroke-width="2"></line></svg>
        <?= esc_html($title) ?> — At a Glance
      </div>
      <div class="up-card-body">
        <table class="up-compare-table" role="table">
          <thead>
            <tr><th scope="col">Attribute</th><th scope="col">Details</th></tr>
          </thead>
          <tbody>
            <tr><td>University Name</td><td><strong><?= esc_html($title) ?></strong></td></tr>
            <tr><td>Location</td><td><?= esc_html($location_str) ?></td></tr>
            <?php if ($year_founded): ?><tr><td>Year Founded</td><td><?= esc_html($year_founded) ?></td></tr><?php endif; ?>
            <?php if ($total_students): ?><tr><td>Total Students</td><td><?= esc_html($total_students) ?></td></tr><?php endif; ?>
            <tr><td>QS World Ranking</td><td>#<?= esc_html($ranking) ?></td></tr>
            <tr><td>Accommodation</td><td>Available</td></tr>
            <?php if (!empty($other_uni)): ?><tr><td>Number of Programs</td><td><?= count($other_uni) ?></td></tr><?php endif; ?>
            <?php
            $geo_fees = []; $geo_langs = [];
            if (!empty($other_uni)) {
                foreach ($other_uni as $p) {
                    $f = !empty($p['discounted_fee']) ? (float)$p['discounted_fee'] : (float)($p['fee'] ?? 0);
                    if ($f > 0) $geo_fees[] = $f;
                    if (!empty($p['language'])) $geo_langs[$p['language']] = true;
                }
                if (!empty($geo_fees)) {
                    echo '<tr><td>Tuition Range (USD/yr)</td><td>$' . number_format(min($geo_fees)) . ' – $' . number_format(max($geo_fees)) . '</td></tr>';
                }
                if (!empty($geo_langs)) {
                    echo '<tr><td>Teaching Languages</td><td>' . esc_html(implode(', ', array_keys($geo_langs))) . '</td></tr>';
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         GEO: FAQ SECTION — Schema.org microdata
         ══════════════════════════════════════════════ -->
    <div class="up-card" id="university-faq">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Frequently Asked Questions
      </div>
      <div class="up-card-body">
        <div class="up-faq-list" itemscope itemtype="https://schema.org/FAQPage">
          <?php
          $uni_faqs = [];
          $uni_faqs[] = ['q' => "Where is {$title} located?", 'a' => "{$title} is located in {$location_str}." . ($year_founded ? " It was founded in {$year_founded}." : "")];
          $uni_faqs[] = ['q' => "What is the QS ranking of {$title}?", 'a' => "{$title} is ranked #{$ranking} in the QS World University Rankings."];
          if ($total_students) {
              $uni_faqs[] = ['q' => "How many students study at {$title}?", 'a' => "{$title} has approximately {$total_students} students enrolled across its undergraduate and graduate programs."];
          }
          if (!empty($other_uni)) {
              $uni_faqs[] = ['q' => "How many programs does {$title} offer?", 'a' => "{$title} offers " . count($other_uni) . " academic programs across various faculties and disciplines."];
          }
          if (!empty($geo_fees)) {
              $uni_faqs[] = ['q' => "What are the tuition fees at {$title}?", 'a' => "Tuition fees at {$title} range from $" . number_format(min($geo_fees)) . " to $" . number_format(max($geo_fees)) . " USD per year, depending on the program and degree level."];
          }
          if (!empty($geo_langs)) {
              $uni_faqs[] = ['q' => "What languages are programs taught in at {$title}?", 'a' => "Programs at {$title} are taught in " . implode(', ', array_keys($geo_langs)) . "."];
          }
          $uni_faqs[] = ['q' => "Does {$title} offer student accommodation?", 'a' => "Yes, {$title} provides on-campus accommodation options for both domestic and international students."];

          foreach ($uni_faqs as $i => $faq):
          ?>
          <article class="up-faq-item" itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
            <button class="up-faq-trigger" onclick="this.closest('.up-faq-item').classList.toggle('open')" itemprop="name">
              <span><?= esc_html($faq['q']) ?></span>
              <svg class="up-faq-chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline></svg>
            </button>
            <div class="up-faq-answer" itemscope itemtype="https://schema.org/Answer">
              <div itemprop="text"><p><?= esc_html($faq['a']) ?></p></div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── Campuses ── -->
    <?php if(!empty($campuses)): ?>
    <div class="up-card" id="university-campus">
      <div class="up-card-heading">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <?= esc_html($title) ?> Campuses
      </div>
      <div class="up-card-body">
        <div class="uni-carousel">
          <?php foreach ($campuses as $campus):
            \SIT\Search\Services\Template::render('shortcodes/uni-campus', ['university' => $campus]);
          endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Data Correction Disclaimer ── -->
    <div class="up-disclaimer" style="margin-top: 30px; padding: 20px; background: #fdfdfd; border: 1px solid #e9ecef; border-left: 4px solid #E20A17; border-radius: 8px; font-size: 14px; color: #6c757d; line-height: 1.6;">
      <strong>Information for University Management:</strong> The data presented on this profile is gathered from public sources and our partner network. If you represent the administration or management of <strong><?= esc_html($title) ?></strong> and notice any outdated or incorrect information, please contact us immediately at <a href="mailto:webmaster@studyinturkiye.com" style="color: #E20A17; font-weight: 500; text-decoration: none;">webmaster@studyinturkiye.com</a>. We are committed to maintaining accurate, high-quality university profiles and will process your requested corrections promptly to protect your institution's representation.
    </div>

    <!-- ══════════════════════════════════════════════
         OFFICIAL LEGAL DISCLAIMER
         ══════════════════════════════════════════════ -->
    <div class="sit-legal-disclaimer">
      <div class="sit-legal-disclaimer-inner">
        <div class="sit-legal-disclaimer-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        </div>
        <div class="sit-legal-disclaimer-text">
          <strong>OFFICIAL LEGAL DISCLAIMER:</strong> Studyinturkiye.com is a private educational consultancy operated by SIT Consultancy LLC. It is not affiliated with, endorsed by, or part of the Turkish Council of Higher Education (YÖK) or any government authority.
        </div>
      </div>
    </div>

  </div>

  <!-- ──── SIDEBAR ──── -->
  <aside class="up-sidebar">

    <!-- Contents Navigation -->
    <div class="up-sidebar-nav">
      <div class="up-sidebar-nav-title">CONTENTS</div>
      <ul class="up-sidebar-links">
        <li><a href="#key-information" class="up-sidebar-link active">University Details</a></li>
        <?php if ($description): ?>
        <li><a href="#course-overview" class="up-sidebar-link">About <?= esc_html($title) ?></a></li>
        <?php endif; ?>
        <li><a href="#university-detail" class="up-sidebar-link">World Rankings</a></li>
        <?php if(!empty($other_uni)): ?>
        <li><a href="#other-university" class="up-sidebar-link">Programs</a></li>
        <?php endif; ?>
        <li><a href="#comparison-table" class="up-sidebar-link">At a Glance</a></li>
        <li><a href="#university-faq" class="up-sidebar-link">FAQ</a></li>
        <?php if(!empty($campuses)): ?>
        <li><a href="#university-campus" class="up-sidebar-link">Campuses</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Help Card -->
    <div class="up-sidebar-help">
      <div class="up-sidebar-help-icon">❓</div>
      <h4>Unsure where to start?</h4>
      <p>Get end-to-end study abroad assistance, for FREE!</p>
      <button class="up-sidebar-help-btn trigger-modal">Help me Decide</button>
    </div>

    <!-- Callback Card -->
    <div class="up-sidebar-callback">
      <div class="up-sidebar-help-icon">📞</div>
      <h4>Need help applying?</h4>
      <p>Our counselors will guide you through the process.</p>
      <button class="up-sidebar-callback-btn trigger-modal">Request Callback</button>
    </div>

  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Program search functionality
  const programCards = document.querySelectorAll('.uni-program-row');
  const keywordInput = document.getElementById('programKeyword');
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  const clearSearch = document.getElementById('clearSearch');
  const noResultsMessage = document.getElementById('noResultsMessage');
  const visibleCount = document.getElementById('visibleCount');
  const totalCount = document.getElementById('totalCount');
  const paginationContainer = document.querySelector('.uni-pagination-pages');
  const prevPageBtn = document.getElementById('prevPage');
  const nextPageBtn = document.getElementById('nextPage');

  if (totalCount) totalCount.textContent = programCards.length;

  let maxPage = 1;
  programCards.forEach(card => {
    const cardPage = parseInt(card.dataset.page);
    if (cardPage > maxPage) maxPage = cardPage;
  });

  let currentPage = 1;
  const maxVisibleButtons = 5;

  function createPageButton(pageNum, isActive = false) {
    const button = document.createElement('button');
    button.className = `uni-pagination-page${isActive ? ' active' : ''}`;
    button.dataset.page = pageNum;
    button.textContent = pageNum;
    button.addEventListener('click', function() {
      changePage(parseInt(this.dataset.page));
    });
    return button;
  }

  function updatePaginationButtons() {
    if (!paginationContainer) return;
    paginationContainer.innerHTML = '';
    let startPage, endPage;
    if (maxPage <= maxVisibleButtons) {
      startPage = 1; endPage = maxPage;
    } else {
      const half = Math.floor(maxVisibleButtons / 2);
      if (currentPage <= half + 1) { startPage = 1; endPage = maxVisibleButtons; }
      else if (currentPage >= maxPage - half) { startPage = maxPage - maxVisibleButtons + 1; endPage = maxPage; }
      else { startPage = currentPage - half; endPage = currentPage + half; }
    }
    if (startPage > 1) {
      paginationContainer.appendChild(createPageButton(1, currentPage === 1));
      if (startPage > 2) {
        const el = document.createElement('span');
        el.className = 'uni-pagination-dots'; el.textContent = '...';
        paginationContainer.appendChild(el);
      }
    }
    for (let i = startPage; i <= endPage; i++) paginationContainer.appendChild(createPageButton(i, i === currentPage));
    if (endPage < maxPage) {
      if (endPage < maxPage - 1) {
        const el = document.createElement('span');
        el.className = 'uni-pagination-dots'; el.textContent = '...';
        paginationContainer.appendChild(el);
      }
      paginationContainer.appendChild(createPageButton(maxPage, currentPage === maxPage));
    }
  }

  function updateProgramDisplay() {
    const keyword = keywordInput ? keywordInput.value.toLowerCase().trim() : '';
    const levelSelect = document.getElementById('programLevel');
    const selectedLevel = levelSelect ? levelSelect.value : '';
    
    let vis = 0;
    programCards.forEach(card => {
      const title = card.querySelector('.upr-title').textContent.toLowerCase();
      const badgeElem = card.querySelector('.upr-level-badge');
      const cardLevel = badgeElem ? badgeElem.textContent.toLowerCase().trim() : '';
      const pg = parseInt(card.dataset.page);
      
      const keywordMatch = keyword === '' || title.includes(keyword);
      
      let levelMatch = true;
      if (selectedLevel !== '') {
        if (selectedLevel === 'phd' && (cardLevel.includes('phd') || cardLevel.includes('doctor'))) {
            levelMatch = true;
        } else {
            levelMatch = cardLevel.includes(selectedLevel);
        }
      }

      if (keywordMatch && levelMatch) {
        if ((keyword !== '' || selectedLevel !== '') || pg === currentPage) { card.style.display = ''; vis++; }
        else { card.style.display = 'none'; }
      } else { card.style.display = 'none'; }
    });
    if (visibleCount) visibleCount.textContent = vis;
    if (programCards.length > 0 && vis === 0) { if (noResultsMessage) noResultsMessage.style.display = 'block'; }
    else { if (noResultsMessage) noResultsMessage.style.display = 'none'; }
    const pagination = document.querySelector('.uni-pagination');
    if (pagination) pagination.style.display = (keyword !== '' || selectedLevel !== '') ? 'none' : '';
    updatePaginationState();
  }

  function updatePaginationState() {
    updatePaginationButtons();
    if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
    if (nextPageBtn) nextPageBtn.disabled = currentPage === maxPage;
  }

  function changePage(pageNum) {
    currentPage = pageNum;
    updateProgramDisplay();
    const sec = document.getElementById('other-university');
    if (sec) window.scrollTo({ top: sec.offsetTop - 50, behavior: 'smooth' });
  }

  if (keywordInput) {
    keywordInput.addEventListener('input', updateProgramDisplay);
    keywordInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault(); updateProgramDisplay(); } });
  }
  
  const levelSelect = document.getElementById('programLevel');
  if (levelSelect) {
      levelSelect.addEventListener('change', function() {
          updateProgramDisplay();
      });
  }

  if (clearSearchBtn) clearSearchBtn.addEventListener('click', function() { 
      keywordInput.value = ''; 
      if(levelSelect) levelSelect.value = '';
      updateProgramDisplay(); 
  });
  if (clearSearch) clearSearch.addEventListener('click', function() { 
      keywordInput.value = ''; 
      if(levelSelect) levelSelect.value = '';
      updateProgramDisplay(); 
  });
  if (prevPageBtn) prevPageBtn.addEventListener('click', function() { if (currentPage > 1) changePage(currentPage - 1); });
  if (nextPageBtn) nextPageBtn.addEventListener('click', function() { if (currentPage < maxPage) changePage(currentPage + 1); });

  // Initial
  let initVis = 0;
  programCards.forEach(card => { if (parseInt(card.dataset.page) === 1) initVis++; });
  if (visibleCount) visibleCount.textContent = initVis;
  updatePaginationButtons();
  updateProgramDisplay();

  // Sticky sidebar nav highlight
  const sidebarLinks = document.querySelectorAll('.up-sidebar-link');
  const sections = [];
  sidebarLinks.forEach(link => {
    const id = link.getAttribute('href').replace('#', '');
    const sec = document.getElementById(id);
    if (sec) sections.push({ el: sec, link: link });
  });
  window.addEventListener('scroll', function() {
    let cur = sections[0];
    sections.forEach(s => { if (s.el.offsetTop - 120 <= window.scrollY) cur = s; });
    sidebarLinks.forEach(l => l.classList.remove('active'));
    if (cur) cur.link.classList.add('active');
  });
});
</script>