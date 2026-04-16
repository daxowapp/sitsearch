<div class="up-campus-card">
  <a href="<?= esc_url($university['guid']) ?>" class="up-campus-image">
    <img src="<?= esc_url($university['image_url']) ?>" alt="<?= esc_attr(strip_tags($university['title'])) ?>">
    <div class="up-campus-overlay"></div>
  </a>

  <div class="up-campus-content">
    <a href="<?= esc_url($university['guid']) ?>" class="up-campus-title">
      <?= esc_html(strip_tags($university['title'])) ?>
    </a>

    <div class="up-campus-meta">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
      <span><?= esc_html($university['country']) ?></span>
    </div>
  </div>

  <div class="up-campus-footer">
    <div class="up-campus-actions">
      <a href="#" class="up-campus-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
        Map
      </a>
      <a href="<?= esc_url($university['guid']) ?>" class="up-campus-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        About Campus
      </a>
    </div>
    <a href="<?= esc_url($university['guid']) ?>" class="up-campus-btn-full">
      See all courses <span>&rarr;</span>
    </a>
  </div>
</div>
