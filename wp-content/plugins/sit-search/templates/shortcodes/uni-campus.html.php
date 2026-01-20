<div class="programPage-university-card">
  <div class="programPage-university-image">
    <img src="<?= $university['image_url'] ?>" alt="<?= $university['title'] ?>">
  </div>
  <div class="programPage-university-content">
    <a href="<?= $university['guid'] ?>" class="programPage-university-title-link">
      <h3 class="programPage-university-title"><?= $university['title'] ?></h3>
    </a>
    <div class="programPage-university-location">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
      <?= $university['country'] ?>
    </div>
    <div class="programPage-university-actions">
      <a href="https://www.google.com/maps?q=<?= $university['map'] ?>" target="_blank" rel="noopener noreferrer" class="programPage-university-link programPage-university-map-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
        View on Map
      </a>
      <a href="<?= $university['guid'] ?>" class="programPage-university-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
        Show all Programs
      </a>
    </div>
  </div>
</div>