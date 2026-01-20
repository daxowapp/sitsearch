<div class="top-universities owl-carousel owl-theme">
    <?php
    foreach ($universities as $university) {
        \SIT\Search\Services\Template::render('shortcodes/university-box', ['university' => $university]);
    }
    ?>
</div>