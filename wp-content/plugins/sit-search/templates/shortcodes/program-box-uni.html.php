<div class="ProgramBoxUni-card <?php echo (!empty($program['is_featured'])) ? 'is-recommended' : ''; ?>">
  <div class="ProgramBoxUni-image-container">
    <a href="<?= $program['link'] ?>" class="ProgramBoxUni-image-link">
      <img src="<?= $program['image_url'] ?>" alt="<?= $program['title'] ?>" class="ProgramBoxUni-image">
      <div class="ProgramBoxUni-image-overlay"></div>
    </a>
    
    <?php if(!empty($program['is_featured'])): ?>
      <div class="ProgramBoxUni-badge ProgramBoxUni-badge-featured">
          <span class="ProgramBoxUni-badge-text">⭐ Recommended</span>
      </div>
    <?php endif; ?>
    
    <?php if(!empty($program['Advanced_Discount'])): 
      $dis_price = 0;
      if(!empty($program['discounted_fee'])){
          $dis_price = $program['Advanced_Discount']/$program['discounted_fee'];
          $dis_price = 100*$dis_price;
          $dis_price = number_format($dis_price, 0);
      } else {
          $dis_price = $program['Advanced_Discount']/$program['fee'];
          $dis_price = 100*$dis_price;
          $dis_price = number_format($dis_price, 0);
      }
      $dis_price = 100-$dis_price;
    ?>
      <div class="ProgramBoxUni-badge ProgramBoxUni-badge-advanced <?php if(!empty($program['discounted_fee'])): ?>ProgramBoxUni-badge-with-discount<?php endif; ?>">
        <span class="ProgramBoxUni-badge-text">Get <?= $dis_price ?>% For Advanced Payment</span>
      </div>
    <?php endif; ?>
    
    <?php if(!empty($program['discounted_fee'])): ?>
      <div class="ProgramBoxUni-badge ProgramBoxUni-badge-discount">
        <span class="ProgramBoxUni-badge-text">Exclusive fee discount</span>
      </div>
    <?php endif; ?>
  </div>
  
  <div class="ProgramBoxUni-content">
    <div class="ProgramBoxUni-header">
      <a href="<?= $program['link'] ?>" class="ProgramBoxUni-title-link">
        <h3 class="ProgramBoxUni-title"><?= $program['title'] ?></h3>
      </a>
      <div class="ProgramBoxUni-location">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        <span><?= $program['country'] ?></span>
      </div>
    </div>
    
    <div class="ProgramBoxUni-university">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20"></path><path d="M6 18V2h12v16H6z"></path><path d="M6 10h12"></path></svg>
      <span><?= $program['uni_title'] ?></span>
    </div>
    
    <div class="ProgramBoxUni-description">
      <p><?= $program['description'] ?></p>
    </div>
    
    <div class="ProgramBoxUni-attributes">
      <!-- Fee Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M15 9.354a4 4 0 1 0 0 5.292"></path><path d="M12 8v2"></path><path d="M12 14v2"></path></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Annual course fee</h4>
          <?php if(!empty($program['discounted_fee'])): ?>
            <div class="ProgramBoxUni-price-container">
              <span class="ProgramBoxUni-price-original"><?= $program['fee'] ?></span>
              <span class="ProgramBoxUni-price-discounted"><?= $program['discounted_fee'] ?> <?= $program['Tuition_Currency'] ?></span>
            </div>
          <?php else: ?>
            <div class="ProgramBoxUni-price-container">
              <span class="ProgramBoxUni-price"><?= $program['fee'] ?> <?= $program['Tuition_Currency'] ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Duration Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Duration</h4>
          <span class="ProgramBoxUni-attribute-value"><?= $program['duration'] ?></span>
        </div>
      </div>
      
      <!-- Rankings Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Study Mode</h4>
          <span class="ProgramBoxUni-attribute-value"><?= isset($program['study_mode']) ? $program['study_mode'] : 'Full-time' ?></span>
        </div>
      </div>
      
      <!-- Students Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Intake</h4>
          <span class="ProgramBoxUni-attribute-value"><?= isset($program['intake']) ? $program['intake'] : 'September' ?></span>
        </div>
      </div>
    </div>
    
    <a href="<?= $program['link'] ?>" class="ProgramBoxUni-view-button">
      <span>View Program</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
    </a>
  </div>
</div>