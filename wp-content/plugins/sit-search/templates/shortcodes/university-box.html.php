<div class="ProgramBoxUni-card">
  <div class="ProgramBoxUni-image-container">
    <a href="<?= $university['guid'] ?>" class="ProgramBoxUni-image-link">
      <img src="<?= $university['image_url'] ?>" alt="<?= esc_attr($university['title']) ?>" class="ProgramBoxUni-image">
      <div class="ProgramBoxUni-image-overlay"></div>
    </a>
  </div>
  
  <div class="ProgramBoxUni-content">
    <div class="ProgramBoxUni-header">
      <a href="<?= $university['guid'] ?>" class="ProgramBoxUni-title-link">
        <h3 class="ProgramBoxUni-title"><?= $university['title'] ?></h3>
      </a>
      <div class="ProgramBoxUni-location">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        <span><?= $university['country'] ?></span>
      </div>
    </div>
    
    <div class="ProgramBoxUni-description" style="display: block; margin-bottom: 12px; font-size: 13px; color: #64748b; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
      <p style="margin:0;"><?= wp_trim_words($university['description'], 15) ?></p>
    </div>
    
    <div class="ProgramBoxUni-attributes">
      <!-- Rankings Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Rankings</h4>
          <span class="ProgramBoxUni-attribute-value"><?= !empty($university['ranking']) ? $university['ranking'] : 'N/A' ?></span>
        </div>
      </div>
      
      <!-- Accommodation Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Accommodation</h4>
          <span class="ProgramBoxUni-attribute-value"><?= !empty($university['accommodation']) ? $university['accommodation'] : 'Yes' ?></span>
        </div>
      </div>
      
      <!-- Founded Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Founded</h4>
          <span class="ProgramBoxUni-attribute-value"><?= !empty($university['year']) ? $university['year'] : 'N/A' ?></span>
        </div>
      </div>
      
      <!-- Students Attribute -->
      <div class="ProgramBoxUni-attribute">
        <div class="ProgramBoxUni-attribute-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="ProgramBoxUni-attribute-content">
          <h4 class="ProgramBoxUni-attribute-label">Students</h4>
          <span class="ProgramBoxUni-attribute-value"><?= !empty($university['students']) ? $university['students'] : 'N/A' ?></span>
        </div>
      </div>
    </div>
    
    <a href="<?= $university['guid'] ?>" class="ProgramBoxUni-view-button">
      <span>View University</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
    </a>
  </div>
</div>
