<?php
// At the beginning of your template or in your controller:
$program = array_merge([
    'discounted_fee' => '',
    'Advanced_Discount' => '',
    // other defaults
], $program);

// Then your existing code should work consistently
if(!empty($program['discounted_fee'])){ 
?>
    <p class="label">Discounted course fee</p>
    <p class="info"><?= $program['Tuition_Currency'] ?> <?= $program['discounted_fee'] ?></p>
<?php } ?>

<div class="uni-hero" style="background-image: url('<?= $program['image_url'] ?>');">
  <div class="uni-hero-container">
    <div class="uni-hero-content">
      <div class="uni-hero-badge">University Program</div>
      <h1 class="uni-hero-title"><?= $program['title'] ?></h1>
      
      <div class="uni-hero-location">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <?= $program['city'] ?>, <?= $program['pro_country'] ?>
      </div>
      
      <div class="uni-hero-stats">
        <div class="uni-hero-stat">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span class="uni-hero-stat-label">Founded <?= $program['Year_Founded'] ?></span>
        </div>
        
        <div class="uni-hero-stat">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="uni-hero-stat-label"><?= $program['total_students'] ?> Students</span>
        </div>
        
        <div class="uni-hero-stat">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
          </svg>
          <span class="uni-hero-stat-label">Ranking <?= $program['ranking'] ?></span>
        </div>
      </div>
    </div>
    
    <div class="uni-hero-actions">
      <button class="uni-hero-button primary trigger-modal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        Apply Now
      </button>
      <a href="#" class="uni-hero-button secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Learn More
      </a>
    </div>
  </div>
</div>

<div class="uni-breadcrumb">
  <div class="uni-breadcrumb-container">
    <a href="/" class="uni-breadcrumb-item">
      <span class="uni-breadcrumb-home">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-7-7v14" />
        </svg>
      </span>
    </a>
    <span class="uni-breadcrumb-separator">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </span>
    <span class="uni-breadcrumb-current"><?= $program['title'] ?></span>
  </div>
</div>

<div class="uni-layout">
  <!-- Main Content Area -->
  <div class="uni-content">
    <!-- Key Information Card -->
    <div class="uni-card" id="key-information">
      <div class="uni-card-header">
        <h2 class="uni-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Key Information about <?= $program['title'] ?>
        </h2>
      </div>
      
      <div class="uni-card-body">
        <div class="uni-card-row">
          <div class="uni-card-column">
            <p class="uni-card-label">Intake Months</p>
            <p class="uni-card-value">January, August</p>
          </div>
          
          <div class="uni-card-column">
            <p class="uni-card-label">Delivery Locations</p>
            <p class="uni-card-value"><?= $program['pro_country'] ?></p>
          </div>
          
          <div class="uni-card-column">
            <p class="uni-card-label">Program Type</p>
            <p class="uni-card-value">University Degree</p>
          </div>
        </div>
      </div>
      
      <div class="uni-card-footer">
        <button class="uni-button primary trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Check Eligibility
        </button>
      </div>
    </div>
    
    <!-- Program Overview Card -->
    <div class="uni-card" id="course-overview">
      <div class="uni-card-header">
        <h2 class="uni-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
          </svg>
          <?= $program['title'] ?> Overview
        </h2>
      </div>
      
      <div class="uni-card-body">
        <p class="uni-card-text"><?= $program['description'] ?></p>
      </div>
      
      <div class="uni-card-footer">
        <button class="uni-button primary trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Help Me Apply
        </button>
        
        <a href="<?= $program['University_brochure'] ?>" class="uni-button outline">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Download Brochure
        </a>
      </div>
    </div>
    
    <!-- CTA Card -->
    <div class="uni-cta-card">
      <h2 class="uni-cta-title">More Than <?= $program['total_students'] ?> Students Studying at <?= $program['title'] ?> – Join Them Now</h2>
      
      <div class="uni-cta-actions">
        <button class="uni-button secondary trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Apply Now
        </button>
      </div>
    </div>
    
    <!-- Institution Details Card -->
    <div class="uni-card" id="university-detail">
      <div class="uni-card-header">
        <h2 class="uni-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <?= $program['title'] ?> Details
        </h2>
      </div>
      
      <div class="uni-card-body">
        <div class="uni-card-row">
          <div class="uni-card-column">
            <p class="uni-card-label">Type of Institution</p>
            <p class="uni-card-value">University</p>
          </div>
          
          <div class="uni-card-column">
            <p class="uni-card-label">Year Founded</p>
            <p class="uni-card-value"><?= $program['Year_Founded'] ?></p>
          </div>
        </div>
        
        <div class="uni-card-row">
          <div class="uni-card-column">
            <p class="uni-card-label">Total Students</p>
            <p class="uni-card-value"><?= $program['total_students'] ?></p>
          </div>
          
          <div class="uni-card-column">
            <p class="uni-card-label">On-Campus Accommodation</p>
            <p class="uni-card-value">Available</p>
          </div>
        </div>
        
        <h3 class="uni-card-subtitle"><?= $program['title'] ?> Ranking</h3>
        
        <div class="uni-ranking">
          <div class="uni-ranking-logo">
            <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/qs-top-universities-vector-logo-2022-1.png" alt="QS Ranking Logo">
          </div>
          
          <div class="uni-ranking-info">
            <p class="uni-ranking-label">QS World University Rankings</p>
            <p class="uni-ranking-value"><?= $program['ranking'] ?></p>
          </div>
        </div>
      </div>
    </div>
    
    <?php if(!empty($other_uni)): ?>
    <!-- University Programs Card -->
<!-- University Programs Card with Simple Filter -->
<div class="uni-card" id="other-university">
  <div class="uni-card-header">
    <h2 class="uni-card-title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
      </svg>
      <?= $program['title'] ?> Programs
    </h2>
    
    <a href="/university/?uni-id=<?= $program['unic_id'] ?>" class="uni-card-link">
      View All
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </a>
  </div>
  
  <div class="uni-card-body">
    <!-- Simple Keyword Search -->
    <div class="uni-keyword-search">
      <div class="uni-search-input">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" id="programKeyword" placeholder="Search programs by keyword...">
        <button id="clearSearchBtn" class="uni-clear-search">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Programs Results Info -->
    <div class="uni-programs-results">
      <div class="uni-programs-count">
        Showing <span id="visibleCount">0</span> out of <span id="totalCount">0</span> programs
      </div>
    </div>
    
    <!-- Programs Grid (2 per row) - Preserving dynamic content -->
    <div id="programsContainer" class="uni-programs-grid">
      <?php 
      // Check if there are programs to display
      if(!empty($other_uni)): 
        // Initialize counter for pagination
        $program_counter = 0;
        $items_per_page = 2;
        
        // Loop through all programs
        foreach ($other_uni as $index => $university): 
          // Calculate page number (1-based)
          $page_number = ceil(($index + 1) / $items_per_page);
          
          // Set display style based on page number
          $display_style = ($page_number == 1) ? '' : 'style="display: none;"';
      ?>
      <!-- Program Card -->
      <div class="uni-program-card" data-page="<?= $page_number ?>" <?= $display_style ?>>
        <div class="uni-program-header">
          <img src="<?= $university['image_url'] ?>" alt="<?= $university['title'] ?>" class="uni-program-image">
          <div class="uni-program-overlay"></div>
          <div class="uni-program-logo">
            <img src="<?= isset($university['uni_logo']) ? $university['uni_logo'] : 'https://search.studyinturkiye.com/wp-content/uploads/2023/06/medipol-logo.png' ?>" alt="University Logo">
          </div>
          <div class="uni-program-language"><?= isset($university['language']) ? $university['language'] : 'English' ?></div>
        </div>
        
        <div class="uni-program-content">
          <h3 class="uni-program-title"><?= $university['title'] ?></h3>
          
          <div class="uni-program-location">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <?= isset($university['city']) ? $university['city'] : 'Istanbul' ?>, <?= isset($university['country']) ? $university['country'] : 'Turkey' ?>
          </div>
          
          <div class="uni-program-info">
            <div class="uni-program-detail">
              <span class="uni-program-detail-label">Duration</span>
<span class="uni-program-detail-value"><?php 
$duration = isset($university['duration']) ? $university['duration'] : '';
if ($duration) {
    echo $duration . ($duration == 1 ? ' Year' : ' Years');
} else {
    echo 'Years';
}
?></span>            </div>
            
            <div class="uni-program-detail">
              <span class="uni-program-detail-label">Study Mode</span>
              <span class="uni-program-detail-value"><?= isset($university['study_mode']) ? $university['study_mode'] : 'Full-time' ?></span>
            </div>
            
            <div class="uni-program-detail">
              <span class="uni-program-detail-label">Intake</span>
              <span class="uni-program-detail-value"><?= isset($university['intake']) ? $university['intake'] : 'September' ?></span>
            </div>
            
            <div class="uni-program-detail">
              <span class="uni-program-detail-label">Level</span>
              <span class="uni-program-detail-value"><?= $university['level'] ?></span>
            </div>
          </div>
        </div>
        
        <div class="uni-program-actions">
          <div class="uni-program-price">
            <span class="uni-program-price-label">Annual Tuition</span>
            <span class="uni-program-price-value"><?php if(isset($university['discounted_fee']) && $university['discounted_fee'] != '' && $university['discounted_fee'] != '0'){ ?>
    <p class="info"><?= $university['Tuition_Currency'] ?> <?= $university['discounted_fee'] ?></p>
<?php }
else{
    ?>
    <p class="info"><?= $university['Tuition_Currency'] ?> <?= $university['fee'] ?></p>
    <?php
}
?></span>
          </div>
          
          <a href="<?= isset($university['guid']) ? $university['guid'] : $university['link'] ?>" class="uni-program-button">
            View Program
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </a>
        </div>
      </div>
      <?php 
        endforeach;
        
        // Calculate total pages
        $total_pages = ceil(count($other_uni) / $items_per_page);
      ?>
      
      <!-- No Results Message (initially hidden) -->
      <div id="noResultsMessage" class="uni-no-results" style="display: none;">
        <div class="uni-no-results-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3>No matching programs found</h3>
        <p>Try a different keyword or clear your search.</p>
        <button id="clearSearch" class="uni-clear-button">Clear Search</button>
      </div>
    </div>
    
    <!-- Dynamic Pagination -->
<!-- Dynamic Pagination with Limited Page Buttons -->
<?php if ($total_pages > 1): ?>
<div class="uni-pagination">
  <button class="uni-pagination-button" id="prevPage" disabled>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
    Previous
  </button>
  
  <div class="uni-pagination-pages">
    <?php
    // Maximum number of page buttons to show
    $max_buttons = 8;
    
    // Calculate which page buttons to show
    if ($total_pages <= $max_buttons) {
      // If total pages are less than the max, show all pages
      $start_page = 1;
      $end_page = $total_pages;
    } else {
      // Calculate start and end page numbers with current page in the middle
      $start_page = max(1, min($total_pages - $max_buttons + 1, 1));
      $end_page = min($total_pages, $max_buttons);
    }
    
    // Generate page buttons
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
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
  </button>
</div>
<?php endif; ?>
    
    <?php else: ?>
    <!-- No Programs Message -->
    <div class="uni-no-programs">
      <div class="uni-no-results-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3>No programs available</h3>
      <p>There are currently no programs to display.</p>
    </div>
    <?php endif; ?>
  </div>
</div>
    <?php endif; ?>
    
    <?php if(!empty($campuses)): ?>
    <!-- University Campuses Card -->
    <div class="uni-card" id="university-campus">
      <div class="uni-card-header">
        <h2 class="uni-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <?= $program['title'] ?> Campuses
        </h2>
      </div>
      
      <div class="uni-card-body">
        <div class="uni-carousel">
            
            

          <?php foreach ($campuses as $campus): {

                        \SIT\Search\Services\Template::render('shortcodes/uni-campus', ['university' => $campus]);

                    }

                    ?> 
          


          <!-- <div class="uni-carousel-item">
            <div class="uni-carousel-image">
              <img src="<?= $campus['image_url'] ?>" alt="<?= $campus['title'] ?>">
            </div>
            
            <div class="uni-carousel-content">
              <h3 class="uni-carousel-title"><?= $campus['title'] ?></h3>
              
              <div class="uni-carousel-location">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <?= $campus['city'] ?>, <?= $campus['pro_country'] ?>
              </div>
              
              <div class="uni-carousel-details">
                <div class="uni-carousel-detail">
                  <span class="uni-carousel-detail-label">Facilities</span>
                  <span class="uni-carousel-detail-value">Modern Campus</span>
                </div>
                
                <div class="uni-carousel-detail">
                  <span class="uni-carousel-detail-label">Location</span>
                  <span class="uni-carousel-detail-value">City Center</span>
                </div>
              </div>
            </div>
            
            <div class="uni-carousel-footer">
              <a href="<?= $campus['link'] ?>" class="uni-carousel-link">View Campus</a>
            </div>
          </div> -->
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <!-- Sidebar -->
  <div class="uni-sidebar">
    <!-- Navigation Menu -->
    <div class="uni-nav">
      <div class="uni-nav-header">
        <h3 class="uni-nav-title">Quick Navigation</h3>
      </div>
      
      <ul class="uni-nav-list">
        <li class="uni-nav-item">
          <a href="#key-information" class="uni-nav-link active">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Key Information
          </a>
        </li>
        
        <li class="uni-nav-item">
          <a href="#course-overview" class="uni-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <?= $program['title'] ?> Overview
          </a>
        </li>
        
        <li class="uni-nav-item">
          <a href="#university-detail" class="uni-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <?= $program['title'] ?> Details
          </a>
        </li>
        
        <?php if(!empty($other_uni)): ?>
        <li class="uni-nav-item">
          <a href="#other-university" class="uni-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Programs
          </a>
        </li>
        <?php endif; ?>
        
        <?php if(!empty($campuses)): ?>
        <li class="uni-nav-item">
          <a href="#university-campus" class="uni-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Campuses
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
    
    <!-- Help Card -->
    <div class="uni-help-card">
      <div class="uni-help-content">
        <div class="uni-help-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
          </svg>
        </div>
        
        <h3 class="uni-help-title">Unsure where to start? Get end-to-end study abroad assistance, for FREE!</h3>
        
        <button class="uni-button primary uni-help-button trigger-modal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Help Me Decide
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Program search functionality
  const filterForm = document.querySelector('.uni-filter-form');
  const filterResetBtn = document.querySelector('.uni-filter-button.secondary');
  const filterSearchBtn = document.querySelector('.uni-filter-button.primary');
  
  if (filterForm && filterSearchBtn) {
    filterSearchBtn.addEventListener('click', function(e) {
      e.preventDefault();
      // In a real implementation, you would collect all form values and submit
      // For this demo, we'll just simulate filtering
      console.log('Searching programs...');
      
      // Example for collecting form data
      const keyword = filterForm.querySelector('input[type="text"]').value;
      const fieldOfStudy = filterForm.querySelector('select[name="field"]') ? 
                          filterForm.querySelector('select[name="field"]').value : '';
      const language = filterForm.querySelector('select[name="language"]') ? 
                      filterForm.querySelector('select[name="language"]').value : '';
      const studyMode = filterForm.querySelector('select[name="mode"]') ? 
                       filterForm.querySelector('select[name="mode"]').value : '';
      
      console.log(`Search params: keyword=${keyword}, field=${fieldOfStudy}, language=${language}, mode=${studyMode}`);
      
      // Here you would typically make an AJAX request or redirect with search parameters
      // For demo purposes, we'll update the results count only
      document.querySelector('.uni-programs-count span:first-child').textContent = '4';
    });
  }
  
  if (filterResetBtn) {
    filterResetBtn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Reset all form fields
      if (filterForm) {
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(input => {
          if (input.type === 'text') {
            input.value = '';
          } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
          }
        });
      }
      
      console.log('Filters reset');
    });
  }
  
  // Program sorting
  const sortSelect = document.querySelector('.uni-programs-sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', function() {
      const sortValue = this.value;
      console.log(`Sorting programs by: ${sortValue}`);
      
      // Here you would implement the actual sorting logic
      // For demo purposes, we'll just log the sort value
    });
  }
});


document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const programCards = document.querySelectorAll('.uni-program-card');
  const keywordInput = document.getElementById('programKeyword');
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  const clearSearch = document.getElementById('clearSearch');
  const noResultsMessage = document.getElementById('noResultsMessage');
  const visibleCount = document.getElementById('visibleCount');
  const totalCount = document.getElementById('totalCount');
  const paginationContainer = document.querySelector('.uni-pagination-pages');
  const prevPageBtn = document.getElementById('prevPage');
  const nextPageBtn = document.getElementById('nextPage');
  
  // Set total count
  if (totalCount) {
    totalCount.textContent = programCards.length;
  }
  
  // Get maximum page number from program cards
  let maxPage = 1;
  programCards.forEach(card => {
    const cardPage = parseInt(card.dataset.page);
    if (cardPage > maxPage) {
      maxPage = cardPage;
    }
  });
  
  // Current page
  let currentPage = 1;
  
  // Configuration for pagination
  const maxVisibleButtons = 5; // Number of page buttons to show
  
  // Function to create a page button
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
  
  // Function to update pagination buttons based on current page
  function updatePaginationButtons() {
    if (!paginationContainer) return;
    
    // Clear existing buttons
    paginationContainer.innerHTML = '';
    
    // Calculate range of buttons to show
    let startPage, endPage;
    
    if (maxPage <= maxVisibleButtons) {
      // If we have fewer pages than max buttons, show all pages
      startPage = 1;
      endPage = maxPage;
    } else {
      // Calculate the sliding window
      const halfButtons = Math.floor(maxVisibleButtons / 2);
      
      if (currentPage <= halfButtons + 1) {
        // Near the start
        startPage = 1;
        endPage = maxVisibleButtons;
      } else if (currentPage >= maxPage - halfButtons) {
        // Near the end
        startPage = maxPage - maxVisibleButtons + 1;
        endPage = maxPage;
      } else {
        // In the middle
        startPage = currentPage - halfButtons;
        endPage = currentPage + halfButtons;
      }
    }
    
    // Add first page button if not in range
    if (startPage > 1) {
      paginationContainer.appendChild(createPageButton(1, currentPage === 1));
      
      // Add ellipsis if there's a gap
      if (startPage > 2) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'uni-pagination-dots';
        ellipsis.textContent = '...';
        paginationContainer.appendChild(ellipsis);
      }
    }
    
    // Add page buttons in range
    for (let i = startPage; i <= endPage; i++) {
      paginationContainer.appendChild(createPageButton(i, i === currentPage));
    }
    
    // Add last page button if not in range
    if (endPage < maxPage) {
      // Add ellipsis if there's a gap
      if (endPage < maxPage - 1) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'uni-pagination-dots';
        ellipsis.textContent = '...';
        paginationContainer.appendChild(ellipsis);
      }
      
      paginationContainer.appendChild(createPageButton(maxPage, currentPage === maxPage));
    }
  }
  
  // Show programs based on current page and filter
  function updateProgramDisplay() {
    const keyword = keywordInput ? keywordInput.value.toLowerCase().trim() : '';
    let visibleItems = 0;
    let foundMatch = false;
    
    // Filter logic
    programCards.forEach(card => {
      // Get program title for filtering
      const title = card.querySelector('.uni-program-title').textContent.toLowerCase();
      const cardPage = parseInt(card.dataset.page);
      
      // Check if card matches keyword filter
      const matchesKeyword = keyword === '' || title.includes(keyword);
      
      // Show card if it matches keyword filter and is on current page (or if keyword filter is active)
      if (matchesKeyword) {
        if (keyword !== '' || cardPage === currentPage) {
          card.style.display = '';
          visibleItems++;
          foundMatch = true;
        } else {
          card.style.display = 'none';
        }
      } else {
        card.style.display = 'none';
      }
    });
    
    // Update visible count
    if (visibleCount) {
      visibleCount.textContent = visibleItems;
    }
    
    // Show/hide no results message
    if (programCards.length > 0 && visibleItems === 0) {
      if (noResultsMessage) noResultsMessage.style.display = 'block';
    } else {
      if (noResultsMessage) noResultsMessage.style.display = 'none';
    }
    
    // Update pagination visibility
    const pagination = document.querySelector('.uni-pagination');
    if (pagination) {
      if (keyword !== '') {
        // Hide pagination when filtering
        pagination.style.display = 'none';
      } else {
        // Show pagination when not filtering
        pagination.style.display = '';
      }
    }
    
    // Update pagination state
    updatePaginationState();
  }
  
  // Update pagination state
  function updatePaginationState() {
    // Update pagination buttons
    updatePaginationButtons();
    
    // Update prev/next buttons
    if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
    if (nextPageBtn) nextPageBtn.disabled = currentPage === maxPage;
  }
  
  // Change page
  function changePage(pageNum) {
    currentPage = pageNum;
    updateProgramDisplay();
    
    // Scroll to top of programs section
    const programsSection = document.getElementById('other-university');
    if (programsSection) {
      window.scrollTo({
        top: programsSection.offsetTop - 50,
        behavior: 'smooth'
      });
    }
  }
  
  // Search functionality
  if (keywordInput) {
    keywordInput.addEventListener('input', function() {
      updateProgramDisplay();
    });
    
    // Enter key support
    keywordInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        updateProgramDisplay();
      }
    });
  }
  
  // Clear search functionality
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', function() {
      keywordInput.value = '';
      updateProgramDisplay();
    });
  }
  
  if (clearSearch) {
    clearSearch.addEventListener('click', function() {
      keywordInput.value = '';
      updateProgramDisplay();
    });
  }
  
  // Previous page button
  if (prevPageBtn) {
    prevPageBtn.addEventListener('click', function() {
      if (currentPage > 1) {
        changePage(currentPage - 1);
      }
    });
  }
  
  // Next page button
  if (nextPageBtn) {
    nextPageBtn.addEventListener('click', function() {
      if (currentPage < maxPage) {
        changePage(currentPage + 1);
      }
    });
  }
  
  // Initial count update
  let initialVisibleItems = 0;
  programCards.forEach(card => {
    if (parseInt(card.dataset.page) === 1) {
      initialVisibleItems++;
    }
  });
  
  if (visibleCount) {
    visibleCount.textContent = initialVisibleItems;
  }
  
  // Initial display
  updatePaginationButtons();
  updateProgramDisplay();
});
</script>