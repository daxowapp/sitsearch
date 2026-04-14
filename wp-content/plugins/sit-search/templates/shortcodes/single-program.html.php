<?php
// Resolve taxonomy terms
$degree_terms = get_the_terms($program['pro_id'], 'sit-degree');
$language_terms = get_the_terms($program['pro_id'], 'sit-language');
$geo_degree = (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '';
$geo_language = (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '';
$location_parts = array_filter([$program['city'], $program['country']]);
$location_string = !empty($location_parts) ? implode(', ', $location_parts) : '';

// Compute savings for tuition card
$has_discount = !empty($program['discounted_fee']) && !empty($program['fee']) && (float)$program['discounted_fee'] < (float)$program['fee'];
$savings_amount = $has_discount ? (float)$program['fee'] - (float)$program['discounted_fee'] : 0;
$savings_pct = ($has_discount && (float)$program['fee'] > 0) ? round(($savings_amount / (float)$program['fee']) * 100) : 0;
?>

<!-- ═══════════════════════════════════════════════════════════════
     HERO SECTION — Clean modern layout with accent bar
     ═══════════════════════════════════════════════════════════════ -->
<div class="pp-hero">
  <div class="pp-hero-accent" style="background-image: url('<?= $program['image_url'] ?>');"></div>
  <div class="pp-container">
    <div class="pp-hero-inner">
      <div class="pp-hero-content">
        <?php if (!empty($program['uni_title'])): ?>
        <a href="<?= $program['uni_link'] ?: '#' ?>" class="pp-hero-uni-link">
          <?= esc_html($program['uni_title']) ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        </a>
        <?php endif; ?>

        <h1 class="pp-hero-title"><?= $program['title'] ?></h1>

        <?php if (!empty($location_string)): ?>
        <div class="pp-hero-location">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
          <?= esc_html($location_string) ?>
        </div>
        <?php endif; ?>

        <div class="pp-hero-pills">
          <?php if ($program['duration']): ?>
          <span class="pp-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> <?= $program['duration'] ?> Years</span>
          <?php endif; ?>
          <?php if ($geo_degree): ?>
          <span class="pp-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 10 3 12 0v-5"></path></svg> <?= esc_html($geo_degree) ?></span>
          <?php endif; ?>
          <?php if ($geo_language): ?>
          <span class="pp-pill"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg> <?= esc_html($geo_language) ?></span>
          <?php endif; ?>
          <?php if ($program['fee']): ?>
          <span class="pp-pill pp-pill-accent"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg> <?= $program['Tuition_Currency'] ?> <?= $has_discount ? $program['discounted_fee'] : $program['fee'] ?>/yr</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="pp-hero-actions">
        <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="pp-btn pp-btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Apply Now
        </a>
        <a href="#" class="pp-btn pp-btn-outline" id="share-course-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
          Share
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Breadcrumb -->
<div class="pp-breadcrumb">
  <div class="pp-container">
    <a href="/" class="pp-breadcrumb-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      Home
    </a>
    <svg class="pp-breadcrumb-sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    <?php if (!empty($program['uni_title']) && !empty($program['uni_link'])): ?>
    <a href="<?= $program['uni_link'] ?>" class="pp-breadcrumb-item"><?= esc_html($program['uni_title']) ?></a>
    <?php elseif (!empty($program['uni_title'])): ?>
    <span class="pp-breadcrumb-item"><?= esc_html($program['uni_title']) ?></span>
    <?php endif; ?>
    <svg class="pp-breadcrumb-sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    <span class="pp-breadcrumb-item pp-breadcrumb-current"><?= $program['title'] ?></span>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MAIN CONTENT — 2 column grid
     ═══════════════════════════════════════════════════════════════ -->
<div class="pp-container pp-layout">
  <div class="pp-main">

    <!-- GEO: TL;DR Summary — Citable by AI engines -->
    <div class="pp-summary" id="program-summary">
      <div class="pp-summary-bar"></div>
      <div class="pp-summary-body">
        <p class="pp-summary-text">
          <strong><?= esc_html($program['title']) ?></strong> is a
          <?php if ($program['duration']): ?><?= $program['duration'] ?>-year<?php endif; ?>
          <?= $geo_degree ? esc_html($geo_degree) . ' program' : 'program' ?>
          <?php if (!empty($program['uni_title'])): ?>offered by <strong><?= esc_html($program['uni_title']) ?></strong><?php endif; ?>
          <?php if (!empty($location_string)): ?> in <?= esc_html($location_string) ?>.<?php else: ?>.<?php endif; ?>
          <?php if ($geo_language): ?> Taught in <?= esc_html($geo_language) ?>.<?php endif; ?>
          <?php if ($program['fee']): ?>
            Annual tuition is <?= esc_html($program['Tuition_Currency']) ?> <?= esc_html($program['fee']) ?><?php if ($has_discount): ?> (discounted: <?= esc_html($program['Tuition_Currency']) ?> <?= esc_html($program['discounted_fee']) ?>)<?php endif; ?>.
          <?php endif; ?>
        </p>
        <time datetime="<?= get_the_modified_date('c', $program['pro_id']) ?>" class="pp-summary-date">
          Updated <?= get_the_modified_date('M j, Y', $program['pro_id']) ?>
        </time>
      </div>
    </div>

    <!-- ═══════ UNIFIED PROGRAM DETAILS CARD ═══════ -->
    <section class="pp-card" id="key-information">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="12" y1="3" x2="12" y2="21"></line></svg>
          Program Details
        </h3>
      </div>
      <div class="pp-card-body">
        <div class="pp-stats-grid">
          <?php if ($program['duration']): ?>
          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-blue">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">Duration</span>
              <strong class="pp-stat-value"><?= $program['duration'] ?> Years</strong>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($geo_degree): ?>
          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-purple">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 10 3 12 0v-5"></path></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">Degree</span>
              <strong class="pp-stat-value"><?= esc_html($geo_degree) ?></strong>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($geo_language): ?>
          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-green">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">Language</span>
              <strong class="pp-stat-value"><?= esc_html($geo_language) ?></strong>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($location_string)): ?>
          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-orange">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">Location</span>
              <strong class="pp-stat-value"><?= esc_html($location_string) ?></strong>
            </div>
          </div>
          <?php endif; ?>

          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-teal">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">Intake</span>
              <strong class="pp-stat-value">January, August</strong>
            </div>
          </div>

          <?php if ($program['ranking']): ?>
          <div class="pp-stat-tile">
            <div class="pp-stat-icon pp-stat-gold">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <div class="pp-stat-info">
              <span class="pp-stat-label">QS Ranking</span>
              <strong class="pp-stat-value">#<?= esc_html($program['ranking']) ?></strong>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="pp-card-footer">
        <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="pp-btn pp-btn-primary pp-btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Check Eligibility
        </a>
      </div>
    </section>

    <!-- ═══════ GEO: COMPARISON TABLE — At a Glance ═══════ -->
    <section class="pp-card" id="program-comparison">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="12" y1="3" x2="12" y2="21"></line></svg>
          <?= esc_html($program['title']) ?> — At a Glance
        </h3>
      </div>
      <div class="pp-card-body">
        <table class="pp-compare-table" role="table">
          <thead>
            <tr><th scope="col">Attribute</th><th scope="col">Details</th></tr>
          </thead>
          <tbody>
            <tr><td>Program Name</td><td><strong><?= esc_html($program['title']) ?></strong></td></tr>
            <?php if (!empty($program['uni_title'])): ?>
            <tr><td>University</td><td><a href="<?= !empty($program['uni_link']) ? esc_url($program['uni_link']) : '#' ?>"><?= esc_html($program['uni_title']) ?></a></td></tr>
            <?php endif; ?>
            <?php if ($geo_degree): ?>
            <tr><td>Degree Level</td><td><?= esc_html($geo_degree) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($location_string)): ?>
            <tr><td>Location</td><td><?= esc_html($location_string) ?></td></tr>
            <?php endif; ?>
            <?php if ($program['duration']): ?>
            <tr><td>Duration</td><td><?= esc_html($program['duration']) ?> Years (Full-time)</td></tr>
            <?php endif; ?>
            <?php if ($geo_language): ?>
            <tr><td>Language of Instruction</td><td><?= esc_html($geo_language) ?></td></tr>
            <?php endif; ?>
            <?php if ($program['fee']): ?>
            <tr><td>Annual Tuition Fee</td><td><?= esc_html($program['Tuition_Currency']) ?> <?= number_format((float)$program['fee']) ?></td></tr>
            <?php endif; ?>
            <?php if ($has_discount): ?>
            <tr><td>Discounted Fee (SIT)</td><td><strong><?= esc_html($program['Tuition_Currency']) ?> <?= number_format((float)$program['discounted_fee']) ?></strong></td></tr>
            <?php endif; ?>
            <tr><td>Intake Periods</td><td>January, August</td></tr>
            <?php if ($program['ranking']): ?>
            <tr><td>QS World Ranking</td><td>#<?= esc_html($program['ranking']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($program['ielts'])): ?>
            <tr><td>IELTS Requirement</td><td><?= esc_html($program['ielts']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($program['toefl'])): ?>
            <tr><td>TOEFL Requirement</td><td><?= esc_html($program['toefl']) ?></td></tr>
            <?php endif; ?>
            <tr><td>Study Mode</td><td>Full-time, On-campus</td></tr>
            <tr><td>Accommodation</td><td>Available</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ═══════ TUITION CARD ═══════ -->
    <?php if ($program['fee']): ?>
    <section class="pp-tuition-card" id="tuition-info">
      <div class="pp-tuition-header">
        <h3 class="pp-tuition-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          Tuition & Fees
        </h3>
        <?php if ($has_discount && $savings_pct > 0): ?>
        <span class="pp-savings-badge">Save <?= $savings_pct ?>%</span>
        <?php endif; ?>
      </div>
      <div class="pp-tuition-body">
        <div class="pp-tuition-prices">
          <div class="pp-tuition-item <?= $has_discount ? 'pp-tuition-original' : 'pp-tuition-current' ?>">
            <span class="pp-tuition-label"><?= $has_discount ? 'Official Fee' : 'Annual Fee' ?></span>
            <span class="pp-tuition-amount <?= $has_discount ? 'pp-strikethrough' : '' ?>"><?= $program['Tuition_Currency'] ?> <?= number_format((float)$program['fee']) ?></span>
          </div>
          <?php if ($has_discount): ?>
          <div class="pp-tuition-arrow">→</div>
          <div class="pp-tuition-item pp-tuition-current">
            <span class="pp-tuition-label">SIT Discount</span>
            <span class="pp-tuition-amount pp-tuition-highlight"><?= $program['Tuition_Currency'] ?> <?= number_format((float)$program['discounted_fee']) ?></span>
          </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($program['Advanced_Discount'])): ?>
        <div class="pp-tuition-extra">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
          Early bird price: <strong><?= $program['Tuition_Currency'] ?> <?= number_format((float)$program['Advanced_Discount']) ?></strong>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ═══════ PROGRAM OVERVIEW ═══════ -->
    <section class="pp-card" id="course-overview">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
          Program Overview
        </h3>
      </div>
      <div class="pp-card-body">
        <h4 class="pp-overview-title"><?= $program['title'] ?><?= !empty($program['uni_title']) ? ', ' . esc_html($program['uni_title']) : '' ?></h4>
        <div class="pp-overview-wrapper" id="overview-wrapper">
          <div class="pp-overview-content" id="overview-content">
            <p><?= $program['description'] ?></p>
          </div>
          <div class="pp-overview-fade" id="overview-fade"></div>
        </div>
        <button type="button" class="pp-overview-toggle" id="overview-toggle" style="display:none;">
          <span class="programPage-overview-toggle-text">Read More</span>
          <svg class="programPage-overview-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
      </div>
    </section>

    <!-- ═══════ CTA — APPLY SECTION ═══════ -->
    <section class="pp-cta">
      <div class="pp-cta-content">
        <div class="pp-cta-text">
          <?php if (!empty($program['total_students'])): ?>
          <h2 class="pp-cta-title">Join <?= number_format((int)$program['total_students']) ?>+ students</h2>
          <?php else: ?>
          <h2 class="pp-cta-title">Start Your Journey</h2>
          <?php endif; ?>
          <p class="pp-cta-subtitle">Study <?= esc_html($program['title']) ?><?= !empty($program['uni_title']) ? ' at ' . esc_html($program['uni_title']) : '' ?></p>
        </div>
        <a class="pp-btn pp-btn-white" href="/apply/?prog_id=<?= $program['pro_id'] ?>">
          Apply Now
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
      <div class="pp-cta-accent"></div>
      <div class="pp-cta-accent-2"></div>
    </section>

    <!-- ═══════ ADMISSION REQUIREMENTS ═══════ -->
    <?php if(!empty($program['ielts'] || $program['pte'] || $program['toefl'])): ?>
    <section class="pp-card" id="admission-requirment">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
          Admission Requirements
        </h3>
      </div>
      <div class="pp-card-body">
        <div class="pp-requirements">
          <div class="pp-req-item">
            <div class="pp-req-logo">
              <img src="https://studyinturkiye.com/wp-content/uploads/2025/04/toefl-logo-periwinkle.svg" alt="TOEFL">
            </div>
            <div class="pp-req-score"><?= $program['toefl'] ?: 'Not Required' ?></div>
            <a href="https://studyinturkiye.com/exclusive-15-discount-on-the-toefl-ibt-test/" class="pp-req-link">Get 15% Discount →</a>
          </div>
          <div class="pp-req-item">
            <div class="pp-req-logo">
              <img src="https://studyinturkiye.com/wp-content/uploads/2025/05/IELTS_logo-1.svg" alt="IELTS">
            </div>
            <div class="pp-req-score"><?= $program['ielts'] ?: 'Not Required' ?></div>
            <a href="#" class="pp-req-link">Learn More →</a>
          </div>
          <div class="pp-req-item">
            <div class="pp-req-logo pp-req-pte"><span>PTE</span></div>
            <div class="pp-req-score"><?= $program['pte'] ?: 'Not Required' ?></div>
            <a href="#" class="pp-req-link">Learn More →</a>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ═══════ INSTITUTION DETAILS ═══════ -->
    <?php if (!empty($program['uni_title'])): ?>
    <section class="pp-card" id="university-detail">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 10 3 12 0v-5"></path></svg>
          About <?= esc_html($program['uni_title']) ?>
        </h3>
      </div>
      <div class="pp-card-body">
        <div class="pp-uni-meta">
          <?php if ($program['Year_Founded']): ?>
          <div class="pp-uni-meta-item">
            <span class="pp-uni-meta-label">Founded</span>
            <strong><?= esc_html($program['Year_Founded']) ?></strong>
          </div>
          <?php endif; ?>
          <?php if ($program['total_students']): ?>
          <div class="pp-uni-meta-item">
            <span class="pp-uni-meta-label">Students</span>
            <strong><?= number_format((int)$program['total_students']) ?>+</strong>
          </div>
          <?php endif; ?>
          <?php if ($program['ranking']): ?>
          <div class="pp-uni-meta-item">
            <span class="pp-uni-meta-label">QS Ranking</span>
            <strong>#<?= esc_html($program['ranking']) ?></strong>
          </div>
          <?php endif; ?>
          <div class="pp-uni-meta-item">
            <span class="pp-uni-meta-label">Type</span>
            <strong>University</strong>
          </div>
          <div class="pp-uni-meta-item">
            <span class="pp-uni-meta-label">Accommodation</span>
            <strong>Available</strong>
          </div>
        </div>

        <?php if (!empty($program['uni_description'])): ?>
        <div class="pp-uni-desc">
          <p><?= $program['uni_description'] ?></p>
        </div>
        <?php endif; ?>
      </div>
      <div class="pp-card-footer">
        <?php if (!empty($program['uni_link'])): ?>
        <a href="<?= $program['uni_link'] ?>" class="pp-btn pp-btn-secondary pp-btn-sm">View University Profile</a>
        <?php endif; ?>
        <?php if (!empty($program['University_brochure'])): ?>
        <a href="<?= $program['University_brochure'] ?>" class="pp-btn pp-btn-outline pp-btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          Download Brochure
        </a>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ═══════ OTHER UNIVERSITIES ═══════ -->
    <section class="pp-card" id="other-university">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Other Universities Offering This Program
        </h3>
      </div>
      <div class="pp-card-body">
        <div class="pp-campus-grid">
          <?php
          if (!empty($other_uni) && is_array($other_uni)) {
              foreach ($other_uni as $university) {
                  \SIT\Search\Services\Template::render('shortcodes/other-university', ['university' => $university]);
              }
          } else {
              echo '<p class="pp-empty-state">No other universities offering this program at the moment.</p>';
          }
          ?>
        </div>
      </div>
    </section>

    <!-- ═══════ CURRICULUM ═══════ -->
    <?php if(!empty($program['curriculum'])): ?>
    <section class="pp-card" id="curriculum">
      <div class="pp-card-header">
        <h3 class="pp-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          Curriculum
        </h3>
      </div>
      <div class="pp-card-body">
        <div class="pp-curriculum-toggle">
          <input type="checkbox" id="curriculum-toggle">
          <label for="curriculum-toggle" class="pp-curriculum-label">
            Show Course Subjects
            <svg class="pp-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </label>
          <div class="pp-curriculum-content">
            <div class="pp-curriculum-grid">
              <?php
              $curriculum = array_filter(array_map('trim', explode(',', $program['curriculum'])));
              foreach ($curriculum as $value) {
              ?>
              <div class="pp-curriculum-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?= esc_html($value) ?>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>
  </div>

  <!-- ═══════════════════════════════════════════════
       SIDEBAR
       ═══════════════════════════════════════════════ -->
  <div class="pp-sidebar">
    <div class="pp-sticky-nav">
      <h3 class="pp-nav-title">Contents</h3>
      <ul class="pp-nav-list">
        <li><a href="#key-information" class="pp-nav-link active">Program Details</a></li>
        <li><a href="#program-comparison" class="pp-nav-link">At a Glance</a></li>
        <?php if ($program['fee']): ?>
        <li><a href="#tuition-info" class="pp-nav-link">Tuition & Fees</a></li>
        <?php endif; ?>
        <li><a href="#course-overview" class="pp-nav-link">Program Overview</a></li>
        <li><a href="#admission-requirment" class="pp-nav-link">Entry Requirements</a></li>
        <?php if (!empty($program['uni_title'])): ?>
        <li><a href="#university-detail" class="pp-nav-link">University Details</a></li>
        <?php endif; ?>
        <li><a href="#other-university" class="pp-nav-link">Other Universities</a></li>
        <?php if(!empty($program['curriculum'])): ?>
        <li><a href="#curriculum" class="pp-nav-link">Curriculum</a></li>
        <?php endif; ?>
        <li><a href="#faq-section" class="pp-nav-link">FAQ</a></li>
      </ul>
    </div>

    <div class="pp-sidebar-card pp-sidebar-help">
      <div class="pp-sidebar-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      </div>
      <h4>Unsure where to start?</h4>
      <p>Get end-to-end study abroad assistance, for FREE!</p>
      <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="pp-sidebar-btn">Help me Decide</a>
    </div>

    <div class="pp-sidebar-card pp-sidebar-callback">
      <div class="pp-sidebar-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
      </div>
      <h4>Need help applying?</h4>
      <p>Our counselors will guide you through the process.</p>
      <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="pp-sidebar-btn">Request Callback</a>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     FAQ SECTION — GEO Optimized
     ═══════════════════════════════════════════════════════════════ -->
<?php
// Use pre-loaded FAQ data from shortcode (first 10 items)
$faq_display = isset($faq_data) ? $faq_data : \SIT\Search\Services\GeoSchema::get_faq_data($program['pro_id'], 10);
$faq_total = isset($faq_count) ? $faq_count : 0;
$faq_cats = isset($faq_categories) ? $faq_categories : [];
$has_ai_faqs = $faq_total > 0;

// Category display names
$category_labels = [
    'tuition' => 'Tuition & Fees',
    'admission' => 'Admission',
    'academic' => 'Academic',
    'campus_life' => 'Campus Life',
    'career' => 'Career',
    'visa' => 'Visa & Travel',
    'scholarship' => 'Scholarships',
    'accommodation' => 'Housing',
    'comparison' => 'Comparison',
    'general' => 'General',
];

if (!empty($faq_display)):
?>
<div class="pp-container" style="margin-bottom: 40px;">
<section class="pp-card pp-faq-section" id="faq-section" data-post-id="<?= $program['pro_id'] ?>">
  <div class="pp-card-header">
    <h3 class="pp-card-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      Frequently Asked Questions
      <?php if ($faq_total > 0): ?>
        <span class="pp-faq-count"><?= $faq_total ?> Questions</span>
      <?php endif; ?>
    </h3>
  </div>
  <div class="pp-card-body">

    <?php if (!empty($faq_cats) && count($faq_cats) > 1): ?>
    <div class="programPage-faq-tabs" role="tablist">
      <button class="programPage-faq-tab active" data-category="all" role="tab" aria-selected="true">All</button>
      <?php foreach ($faq_cats as $cat): ?>
        <button class="programPage-faq-tab" data-category="<?= esc_attr($cat) ?>" role="tab" aria-selected="false">
          <?= esc_html($category_labels[$cat] ?? ucfirst(str_replace('_', ' ', $cat))) ?>
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="programPage-faq-list" id="faq-list">
      <?php foreach ($faq_display as $i => $faq): ?>
      <article class="programPage-faq-item" itemscope itemtype="https://schema.org/Question"
               data-category="<?= esc_attr($faq['category'] ?? 'general') ?>">
        <details>
          <summary class="programPage-faq-question">
            <?php if ($has_ai_faqs): ?>
              <span class="programPage-faq-cat-badge"><?= esc_html($category_labels[$faq['category']] ?? ucfirst(str_replace('_', ' ', $faq['category'] ?? 'general'))) ?></span>
            <?php endif; ?>
            <h4 itemprop="name"><?= esc_html($faq['question']) ?></h4>
            <svg class="programPage-faq-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </summary>
          <div class="programPage-faq-answer" itemscope itemtype="https://schema.org/Answer">
            <p itemprop="text"><?= esc_html($faq['answer']) ?></p>
          </div>
        </details>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($faq_total > 10): ?>
    <div class="programPage-faq-load-more-wrapper">
      <button type="button" class="programPage-faq-load-more" id="faq-load-more"
              data-post-id="<?= $program['pro_id'] ?>"
              data-page="2"
              data-total="<?= $faq_total ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        Show More Questions
        <span class="programPage-faq-remaining">(<?= $faq_total - min(10, count($faq_display)) ?> more)</span>
      </button>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    var faqSection = document.getElementById('faq-section');
    if (!faqSection) return;

    var postId = faqSection.dataset.postId;
    var loadMoreBtn = document.getElementById('faq-load-more');
    var faqList = document.getElementById('faq-list');
    var apiBase = '<?= esc_url(rest_url('sit-search/v1/faqs/')) ?>';
    var activeCategory = 'all';
    var allPage = 2;

    function faqHtml(faq) {
      var catLabel = faq.category ? faq.category.replace(/_/g, ' ') : 'general';
      catLabel = catLabel.split(' ').map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
      var html = '<article class="programPage-faq-item" data-category="' + (faq.category || 'general') + '">';
      html += '<details><summary class="programPage-faq-question">';
      html += '<span class="programPage-faq-cat-badge">' + catLabel + '</span>';
      html += '<h4>' + faq.question + '</h4>';
      html += '<svg class="programPage-faq-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
      html += '</summary><div class="programPage-faq-answer"><p>' + faq.answer + '</p></div></details></article>';
      return html;
    }

    var tabs = faqSection.querySelectorAll('.programPage-faq-tab');
    tabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        tabs.forEach(function(t) { t.classList.remove('active'); t.setAttribute('aria-selected', 'false'); });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');
        activeCategory = this.dataset.category;

        if (activeCategory === 'all') {
          var items = faqList.querySelectorAll('.programPage-faq-item');
          items.forEach(function(item) { item.style.display = ''; });
          if (loadMoreBtn) loadMoreBtn.parentNode.style.display = '';
          return;
        }

        faqList.innerHTML = '<div style="text-align:center;padding:20px;"><span class="programPage-faq-loading-spinner"></span> Loading...</div>';
        if (loadMoreBtn) loadMoreBtn.parentNode.style.display = 'none';

        fetch(apiBase + postId + '?per_page=100&category=' + activeCategory)
          .then(function(r) { return r.json(); })
          .then(function(data) {
            faqList.innerHTML = '';
            if (data.faqs && data.faqs.length > 0) {
              data.faqs.forEach(function(faq) { faqList.insertAdjacentHTML('beforeend', faqHtml(faq)); });
            } else {
              faqList.innerHTML = '<p style="text-align:center;color:#999;padding:20px;">No questions in this category yet.</p>';
            }
          })
          .catch(function() {
            faqList.innerHTML = '<p style="text-align:center;color:#c00;padding:20px;">Error loading questions. Try again.</p>';
          });
      });
    });

    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', function() {
        var total = parseInt(this.dataset.total);
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="programPage-faq-loading-spinner"></span> Loading...';

        fetch(apiBase + postId + '?page=' + allPage + '&per_page=20')
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.faqs && data.faqs.length > 0) {
              data.faqs.forEach(function(faq) { faqList.insertAdjacentHTML('beforeend', faqHtml(faq)); });
              allPage++;
              var loaded = faqList.querySelectorAll('.programPage-faq-item').length;
              var remaining = total - loaded;
              if (!data.has_more || remaining <= 0) {
                btn.parentNode.style.display = 'none';
              } else {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg> Show More Questions <span class="programPage-faq-remaining">(' + remaining + ' more)</span>';
              }
            } else { btn.parentNode.style.display = 'none'; }
          })
          .catch(function() { btn.disabled = false; btn.innerHTML = 'Error loading. Try again.'; });
      });
    }
  });
})();
</script>
</div>
<?php endif; ?>


<div class="programPage-floating-share" id="floating-share-btn">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
</div>


<style>
/* Share dropdown styles */
.programPage-share-dropdown {
  position: relative;
  display: inline-block;
}

.programPage-share-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.programPage-share-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.programPage-share-option {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  text-decoration: none;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s ease;
}

.programPage-share-option:last-child {
  border-bottom: none;
}

.programPage-share-option:hover {
  background-color: #f8f9fa;
}

.programPage-share-option svg {
  margin-right: 12px;
  flex-shrink: 0;
}

.programPage-share-option.facebook { color: #1877f2; }
.programPage-share-option.twitter { color: #1da1f2; }
.programPage-share-option.linkedin { color: #0077b5; }
.programPage-share-option.whatsapp { color: #25d366; }
.programPage-share-option.copy { color: #666; }

/* Floating share button */
.programPage-floating-share {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 50%;
  width: 56px;
  height: 56px;
  display: none;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  z-index: 1000;
  transition: all 0.3s ease;
}

.programPage-floating-share:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scrolling for navigation links
  document.querySelectorAll('.pp-nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      const targetSection = document.querySelector(targetId);
      if (targetSection) {
        window.scrollTo({ top: targetSection.offsetTop - 20, behavior: 'smooth' });
        document.querySelectorAll('.pp-nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });

  // Highlight active section on scroll
  window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.pp-card, .pp-cta, .pp-tuition-card, .pp-faq-section');
    let currentSection = '';

    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      if (window.pageYOffset >= sectionTop - 100 && window.pageYOffset < sectionTop + sectionHeight - 100) {
        currentSection = '#' + section.getAttribute('id');
      }
    });

    document.querySelectorAll('.pp-nav-link').forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === currentSection) {
        link.classList.add('active');
      }
    });
  });

  // Floating share button on mobile
  window.addEventListener('scroll', function() {
    const floatingBtn = document.getElementById('floating-share-btn');
    if (window.innerWidth <= 768) {
      floatingBtn.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
    }
  });

  // Social Media Sharing
  function createShareMenu() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?= addslashes($program["title"]) ?> - <?= addslashes($program["uni_title"]) ?>');
    const shareMenu = document.createElement('div');
    shareMenu.className = 'programPage-share-menu';
    shareMenu.innerHTML = `
      <a href="https://www.facebook.com/sharer/sharer.php?u=${url}" target="_blank" class="programPage-share-option facebook">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        Facebook
      </a>
      <a href="https://twitter.com/intent/tweet?url=${url}&text=${title}" target="_blank" class="programPage-share-option twitter">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
        Twitter
      </a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url=${url}" target="_blank" class="programPage-share-option linkedin">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
        LinkedIn
      </a>
      <a href="https://wa.me/?text=${title}%20${url}" target="_blank" class="programPage-share-option whatsapp">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
        WhatsApp
      </a>
      <a href="#" class="programPage-share-option copy" data-action="copy">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        Copy Link
      </a>
    `;
    return shareMenu;
  }

  function handleShareClick(button) {
    const existingMenus = document.querySelectorAll('.programPage-share-dropdown');
    existingMenus.forEach(menu => menu.remove());
    const wrapper = document.createElement('div');
    wrapper.className = 'programPage-share-dropdown';
    wrapper.style.position = 'relative';
    wrapper.style.display = 'inline-block';
    const menu = createShareMenu();
    wrapper.appendChild(menu);
    button.parentNode.appendChild(wrapper);
    const rect = button.getBoundingClientRect();
    if (rect.right > window.innerWidth - 220) { menu.style.right = '0'; menu.style.left = 'auto'; }
    else { menu.style.left = '0'; menu.style.right = 'auto'; }
    setTimeout(() => menu.classList.add('show'), 10);
    menu.addEventListener('click', function(e) {
      const copyOption = e.target.closest('[data-action="copy"]');
      if (copyOption) { e.preventDefault(); copyToClipboard(copyOption); }
    });
    function closeMenu(e) {
      if (!wrapper.contains(e.target) && !button.contains(e.target)) {
        menu.classList.remove('show');
        setTimeout(() => wrapper.remove(), 300);
        document.removeEventListener('click', closeMenu);
      }
    }
    setTimeout(() => document.addEventListener('click', closeMenu), 100);
  }

  function copyToClipboard(element) {
    const url = window.location.href;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(function() {
        const orig = element.innerHTML;
        element.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';
        element.style.color = '#28a745';
        setTimeout(() => { element.innerHTML = orig; element.style.color = ''; }, 2000);
      });
    } else {
      prompt('Copy this link:', url);
    }
  }

  const shareButton = document.getElementById('share-course-btn');
  const floatingShareBtn = document.getElementById('floating-share-btn');
  if (shareButton) shareButton.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); handleShareClick(this); });
  if (floatingShareBtn) floatingShareBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); handleShareClick(this); });

  // Overview Read More / Read Less toggle
  const overviewContent = document.getElementById('overview-content');
  const overviewFade = document.getElementById('overview-fade');
  const overviewToggle = document.getElementById('overview-toggle');
  const COLLAPSED_HEIGHT = 120;

  if (overviewContent && overviewToggle) {
    const realHeight = overviewContent.scrollHeight;
    if (realHeight > COLLAPSED_HEIGHT + 40) {
      overviewContent.style.maxHeight = COLLAPSED_HEIGHT + 'px';
      overviewContent.style.overflow = 'hidden';
      overviewFade.style.display = 'block';
      overviewToggle.style.display = 'flex';
      let expanded = false;
      overviewToggle.addEventListener('click', function() {
        expanded = !expanded;
        if (expanded) {
          overviewContent.style.maxHeight = realHeight + 'px';
          overviewFade.style.display = 'none';
          overviewToggle.querySelector('.programPage-overview-toggle-text').textContent = 'Read Less';
          overviewToggle.querySelector('.programPage-overview-toggle-icon').style.transform = 'rotate(180deg)';
        } else {
          overviewContent.style.maxHeight = COLLAPSED_HEIGHT + 'px';
          overviewFade.style.display = 'block';
          overviewToggle.querySelector('.programPage-overview-toggle-text').textContent = 'Read More';
          overviewToggle.querySelector('.programPage-overview-toggle-icon').style.transform = 'rotate(0deg)';
        }
      });
    } else {
      overviewFade.style.display = 'none';
    }
  }
});
</script>