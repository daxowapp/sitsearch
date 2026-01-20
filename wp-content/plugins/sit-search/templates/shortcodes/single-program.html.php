<div class="programPage-hero" style="background-image: url('<?= $program['image_url'] ?>');">
  <div class="programPage-overlay"></div>
  <div class="programPage-container">
    <div class="programPage-hero-content">
      <div class="programPage-badge">Program Details</div>
      <h1 class="programPage-hero-title"><?= $program['uni_title'] ?></h1>
      <p class="programPage-hero-location">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        <?= $program['city'] ?>, <?= $program['country'] ?>
      </p>
      <h2 class="programPage-hero-program"><?= $program['title'] ?></h2>
      
      <div class="programPage-hero-stats">
        <div class="programPage-stat">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
          <span><?= $program['duration'] ?> Years</span>
        </div>
        <div class="programPage-stat">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          <span>Jan, Aug Intake</span>
        </div>
        <div class="programPage-stat">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 5H9l-7 7 7 7h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
          <span><?= $program['Tuition_Currency'] ?> <?= $program['fee'] ?></span>
        </div>
      </div>
    </div>
    
    <div class="programPage-hero-actions">
      <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="programPage-action-button programPage-save-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
       Apply To <?= $program['title'] ?>
      </a>
      <a href="#" class="programPage-action-button programPage-share-btn" id="share-course-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
        Share Course
      </a>
    </div>
  </div>
</div>

<div class="programPage-breadcrumb">
  <div class="programPage-container">
    <a href="/" class="programPage-breadcrumb-item programPage-breadcrumb-home">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <span class="programPage-breadcrumb-separator">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </span>
    <a href="<?= $program['uni_link'] ?>" class="programPage-breadcrumb-item"><?= $program['uni_title'] ?></a>
    <span class="programPage-breadcrumb-separator">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </span>
    <span class="programPage-breadcrumb-item programPage-breadcrumb-current"><?= $program['title'] ?></span>
  </div>
</div>

<div class="programPage-container programPage-main-content">
  <div class="programPage-content">
    <section class="programPage-card" id="key-information">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          Key Information
        </h3>
      </div>
      <div class="programPage-card-body">
        <div class="programPage-info-row">
          <div class="programPage-info-item">
            <div class="programPage-info-label">Intake months</div>
            <div class="programPage-info-value">January, August</div>
          </div>
          <div class="programPage-info-item">
            <div class="programPage-info-label">Delivery locations</div>
            <div class="programPage-info-value"><?= $program['pro_country'] ?></div>
          </div>
          <div class="programPage-info-item">
            <div class="programPage-info-label">Duration</div>
            <div class="programPage-info-value"><?= $program['duration'] ?> Years</div>
          </div>
        </div>
        
        <div class="programPage-info-row">
          <div class="programPage-info-item">
            <div class="programPage-info-label">Annual course fee</div>
            <div class="programPage-info-value"><?= $program['Tuition_Currency'] ?> <?= $program['fee'] ?></div>
          </div>
          
          <?php if(!empty($program['discounted_fee'])): ?>
          <div class="programPage-info-item">
            <div class="programPage-info-label">Discounted course fee</div>
            <div class="programPage-info-value programPage-discount"><?= $program['Tuition_Currency'] ?> <?= $program['discounted_fee'] ?></div>
          </div>
          <?php endif; ?>
          
          <?php if(!empty($program['Advanced_Discount'])): ?>
          <div class="programPage-info-item">
            <div class="programPage-info-label">Advanced Discount</div>
            <div class="programPage-info-value programPage-discount"><?= $program['Tuition_Currency'] ?> <?= $program['Advanced_Discount'] ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
<div class="programPage-card-footer">
    <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="programPage-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        Check eligibility
    </a>
</div>
    </section>
    
    <section class="programPage-card" id="course-overview">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
          Program Overview
        </h3>
      </div>
      <div class="programPage-card-body">
        <h4 class="programPage-overview-title"><?= $program['title'] ?>, <?= $program['uni_title'] ?></h4>
        <div class="programPage-overview-content">
          <p><?= $program['description'] ?></p>
        </div>
      </div>
      <div class="programPage-card-footer">
        <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="programPage-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
          Help me Apply
        </a>
      </div>
    </section>
    
    <section class="programPage-cta-card">
      <div class="programPage-cta-content">
        <svg class="programPage-cta-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <h2 class="programPage-cta-title">More Than <?= $program['total_students'] ?> students in <?= $program['title'] ?></h2>
        <p class="programPage-cta-subtitle">Join them and start your academic journey today</p>
        <a class="programPage-cta-button" href="/apply/?prog_id=<?= $program['pro_id'] ?>">
          <span>Apply Now</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
      <div class="programPage-cta-accent"></div>
    </section>
    
    <?php if(!empty($program['ielts'] || $program['pte'] || $program['toefl'])): ?>
    <section class="programPage-card" id="admission-requirment">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
          Admission Requirements
        </h3>
      </div>
      <div class="programPage-card-body">
        <div class="programPage-requirements">
          <div class="programPage-requirement-item">
            <div class="programPage-requirement-logo">
              <img src="https://studyinturkiye.com/wp-content/uploads/2025/04/toefl-logo-periwinkle.svg" alt="TOEFL">
            </div>
            <div class="programPage-requirement-score"><?= $program['toefl'] ?></div>
            <a href="https://studyinturkiye.com/exclusive-15-discount-on-the-toefl-ibt-test/" class="programPage-requirement-link">
              Get 15% Discount
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
          </div>
          
          <div class="programPage-requirement-item">
            <div class="programPage-requirement-logo">
              <img src="https://studyinturkiye.com/wp-content/uploads/2025/05/IELTS_logo-1.svg" alt="IELTS">
            </div>
            <div class="programPage-requirement-score"><?= $program['ielts'] ?></div>
            <a href="#" class="programPage-requirement-link">
              Learn More
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
          </div>
          
          <div class="programPage-requirement-item">
            <div class="programPage-requirement-logo pte">
              <span>PTE</span>
            </div>
            <div class="programPage-requirement-score"><?= $program['pte'] ?></div>
            <a href="#" class="programPage-requirement-link">
              Learn More
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>
    
    <section class="programPage-card" id="university-detail">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z"></path><path d="M7.61 15.83L12 18l4.39-2.17L12 13.64l-4.39 2.19z"></path></svg>
          Institution Details
        </h3>
      </div>
      <div class="programPage-card-body">
        <div class="programPage-university-info">
          <div class="programPage-info-row">
            <div class="programPage-info-item">
              <div class="programPage-info-label">Type of institution</div>
              <div class="programPage-info-value">University</div>
            </div>
            <div class="programPage-info-item">
              <div class="programPage-info-label">Year Founded</div>
              <div class="programPage-info-value"><?= $program['Year_Founded'] ?></div>
            </div>
          </div>
          
          <div class="programPage-info-row">
            <div class="programPage-info-item">
              <div class="programPage-info-label">Total students</div>
              <div class="programPage-info-value"><?= $program['total_students'] ?></div>
            </div>
            <div class="programPage-info-item">
              <div class="programPage-info-label">On campus accommodation</div>
              <div class="programPage-info-value">Available</div>
            </div>
          </div>
          
          <div class="programPage-university-description">
            <p><?= $program['uni_description'] ?></p>
          </div>
          
          <div class="programPage-university-ranking">
            <h4 class="programPage-ranking-title">Rankings</h4>
            <div class="programPage-ranking-item">
              <div class="programPage-ranking-logo">
                <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/qs-top-universities-vector-logo-2022-1.png" alt="QS Ranking">
              </div>
              <div class="programPage-ranking-info">
                <div class="programPage-ranking-name">QS World University Rankings</div>
                <div class="programPage-ranking-value"><?= $program['ranking'] ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="programPage-card-footer">
        <div class="programPage-button-group">
          <a href="<?= $program['uni_link'] ?>" class="programPage-button programPage-button-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            More About Institution
          </a>
          <a href="<?= $program['University_brochure'] ?>" class="programPage-button programPage-button-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Download Brochure
          </a>
        </div>
      </div>
    </section>
    
    <section class="programPage-card" id="other-university">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Universities offering <?= $program['title'] ?>
        </h3>
      </div>
      <div class="programPage-card-body">
        <div class="programPage-campus-grid">
            
                            <?php
                if (!empty($other_uni) && is_array($other_uni)) {
                    foreach ($other_uni as $university) {
                        \SIT\Search\Services\Template::render('shortcodes/other-university', ['university' => $university]);
                    }
                } else {
                    echo '<p class="no-universities">No other universities offering this program at the moment.</p>';
                }
                ?>
            
            
 <!--
 <?php
          if (!empty($other_uni) && is_array($other_uni)) {
              foreach ($other_uni as $university) {
                ?>
                <div class="programPage-campus-card">
                  <div class="programPage-campus-image">
                    <img src="<?= $university['image'] ?>" alt="<?= $university['name'] ?>">
                  </div>
                  <div class="programPage-campus-content">
                    <h4 class="programPage-campus-name"><?= $university['name'] ?></h4>
                    <div class="programPage-campus-location">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                      <?= $university['location'] ?>
                    </div>
                    <a href="<?= $university['link'] ?>" class="programPage-campus-link">
                      View Campus
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                  </div>
                </div>
                <?php
              }
          }
          ?>-->
          
          
        </div>
      </div>
    </section>
    
    <?php if(!empty($program['curriculum'])): ?>
    <section class="programPage-card" id="curriculum">
      <div class="programPage-card-header">
        <h3 class="programPage-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
          Curriculum
        </h3>
      </div>
      <div class="programPage-card-body">
        <div class="programPage-curriculum-toggle">
          <input type="checkbox" id="curriculum-toggle">
          <label for="curriculum-toggle" class="programPage-curriculum-toggle-label">
            Show Curriculum
            <svg class="programPage-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </label>
          <div class="programPage-curriculum-content">
            <div class="programPage-curriculum-grid">
              <?php
              $curriculum = array_filter(array_map('trim', explode(',', $program['curriculum'])));
              foreach ($curriculum as $value) {
                ?>
                <div class="programPage-curriculum-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  <?= $value ?>
                </div>
                <?php
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>
  </div>
  
  <div class="programPage-sidebar">
    <div class="programPage-sticky-nav">
      <div class="programPage-nav-header">
        <h3 class="programPage-nav-title">Contents</h3>
      </div>
      <ul class="programPage-nav-list">
        <li class="programPage-nav-item">
          <a href="#key-information" class="programPage-nav-link active">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            Key information
          </a>
        </li>
        <li class="programPage-nav-item">
          <a href="#course-overview" class="programPage-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            Course Overview
          </a>
        </li>
        <li class="programPage-nav-item">
          <a href="#admission-requirment" class="programPage-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
            Entry requirements
          </a>
        </li>
        <li class="programPage-nav-item">
          <a href="#university-detail" class="programPage-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z"></path><path d="M7.61 15.83L12 18l4.39-2.17L12 13.64l-4.39 2.19z"></path></svg>
            University Details
          </a>
        </li>
        <li class="programPage-nav-item">
          <a href="#other-university" class="programPage-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Campus
          </a>
        </li>
        <?php if(!empty($program['curriculum'])): ?>
        <li class="programPage-nav-item">
          <a href="#curriculum" class="programPage-nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Curriculum
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
    
    <div class="programPage-help-card">
      <div class="programPage-help-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      </div>
      <h3 class="programPage-help-title">Unsure where to start?</h3>
      <p class="programPage-help-text">Get end-to-end study abroad assistance, for FREE!</p>
      <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="programPage-help-button">Help me Decide</a>
    </div>
    
    <div class="programPage-quick-apply">
      <div class="programPage-quick-apply-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        <h3 class="programPage-quick-apply-title">Need help with application?</h3>
      </div>
      <div class="programPage-quick-apply-body">
        <p>Our education counselors will guide you through the entire application process.</p>
        <a href="/apply/?prog_id=<?= $program['pro_id'] ?>" class="programPage-quick-apply-button">Request a Callback</a>
      </div>
    </div>
  </div>
</div>

<!-- SEO Keywords - Hidden from users but accessible to search engines -->
<?php if ($program['keywords']): ?>
<div class="programPage-seo-keywords" style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">
  <h3>Related Keywords</h3>
  <div class="keywords-content">
    <?php
    $keywords = array_filter(array_map('trim', explode(',', $program['keywords'])));
    foreach ($keywords as $keyword) {
      ?>
      <span class="keyword"><?= $keyword ?></span>
      <?php
    }
    ?>
  </div>
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
    
    /* JavaScript for interactivity */
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scrolling for navigation links
  document.querySelectorAll('.programPage-nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Get the target section
      const targetId = this.getAttribute('href');
      const targetSection = document.querySelector(targetId);
      
      if (targetSection) {
        // Scroll to the section
        window.scrollTo({
          top: targetSection.offsetTop - 20,
          behavior: 'smooth'
        });
        
        // Update active link
        document.querySelectorAll('.programPage-nav-link').forEach(link => {
          link.classList.remove('active');
        });
        this.classList.add('active');
      }
    });
  });
  
  // Highlight active section on scroll
  window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.programPage-card, .programPage-cta-card');
    const navLinks = document.querySelectorAll('.programPage-nav-link');
    
    let currentSection = '';
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      
      if (window.pageYOffset >= sectionTop - 100 && 
          window.pageYOffset < sectionTop + sectionHeight - 100) {
        currentSection = '#' + section.getAttribute('id');
      }
    });
    
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === currentSection) {
        link.classList.add('active');
      }
    });
  });
  
  // Display floating share button on mobile when scrolled
  window.addEventListener('scroll', function() {
    const floatingBtn = document.getElementById('floating-share-btn');
    
    if (window.innerWidth <= 768) {
      if (window.pageYOffset > 300) {
        floatingBtn.style.display = 'flex';
      } else {
        floatingBtn.style.display = 'none';
      }
    }
  });

  // Social Media Sharing functionality
  function createShareMenu() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?= addslashes($program["title"]) ?> - <?= addslashes($program["uni_title"]) ?>');
    
    const shareMenu = document.createElement('div');
    shareMenu.className = 'programPage-share-menu';
    shareMenu.innerHTML = `
      <a href="https://www.facebook.com/sharer/sharer.php?u=${url}" target="_blank" class="programPage-share-option facebook">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        Share on Facebook
      </a>
      <a href="https://twitter.com/intent/tweet?url=${url}&text=${title}" target="_blank" class="programPage-share-option twitter">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
        </svg>
        Share on Twitter
      </a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url=${url}" target="_blank" class="programPage-share-option linkedin">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
        </svg>
        Share on LinkedIn
      </a>
      <a href="https://wa.me/?text=${title}%20${url}" target="_blank" class="programPage-share-option whatsapp">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
        </svg>
        Share on WhatsApp
      </a>
      <a href="#" class="programPage-share-option copy" data-action="copy">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        Copy Link
      </a>
    `;
    
    return shareMenu;
  }

  // Handle share button clicks
  function handleShareClick(button) {
    // Remove existing menu if any
    const existingMenus = document.querySelectorAll('.programPage-share-dropdown');
    existingMenus.forEach(menu => menu.remove());

    // Create wrapper div for positioning
    const wrapper = document.createElement('div');
    wrapper.className = 'programPage-share-dropdown';
    wrapper.style.position = 'relative';
    wrapper.style.display = 'inline-block';
    
    // Create and append the menu
    const menu = createShareMenu();
    wrapper.appendChild(menu);
    
    // Insert wrapper after the button
    button.parentNode.appendChild(wrapper);
    
    // Position the menu relative to button
    const rect = button.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    
    // Adjust positioning based on screen space
    if (rect.right > viewportWidth - 220) {
      menu.style.right = '0';
      menu.style.left = 'auto';
    } else {
      menu.style.left = '0';
      menu.style.right = 'auto';
    }
    
    // Show the menu
    setTimeout(() => menu.classList.add('show'), 10);
    
    // Add click handlers for menu items
    menu.addEventListener('click', function(e) {
      const copyOption = e.target.closest('[data-action="copy"]');
      if (copyOption) {
        e.preventDefault();
        copyToClipboard(copyOption);
      }
    });
    
    // Close menu when clicking outside
    function closeMenu(e) {
      if (!wrapper.contains(e.target) && !button.contains(e.target)) {
        menu.classList.remove('show');
        setTimeout(() => wrapper.remove(), 300);
        document.removeEventListener('click', closeMenu);
      }
    }
    
    setTimeout(() => {
      document.addEventListener('click', closeMenu);
    }, 100);
  }

  // Copy to clipboard function
  function copyToClipboard(element) {
    const url = window.location.href;
    
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(function() {
        const originalHTML = element.innerHTML;
        element.innerHTML = `
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Link Copied!
        `;
        element.style.color = '#28a745';
        setTimeout(() => {
          element.innerHTML = originalHTML;
          element.style.color = '';
        }, 2000);
      }).catch(function() {
        fallbackCopyToClipboard(url);
      });
    } else {
      fallbackCopyToClipboard(url);
    }
  }

  function fallbackCopyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand('copy');
      alert('Link copied to clipboard!');
    } catch (err) {
      console.error('Could not copy text: ', err);
      prompt('Copy this link:', text);
    }
    document.body.removeChild(textArea);
  }

  // Add event listeners to share buttons
  const shareButton = document.getElementById('share-course-btn');
  const floatingShareBtn = document.getElementById('floating-share-btn');
  
  if (shareButton) {
    shareButton.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleShareClick(this);
    });
  }
  
  if (floatingShareBtn) {
    floatingShareBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleShareClick(this);
    });
  }
});
    
</script>