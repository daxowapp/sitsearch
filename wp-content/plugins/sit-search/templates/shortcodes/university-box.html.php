<div class="university-box-wrapper university-box-wrapper-du">

    <div class="university-image">

        <a href="<?= $university['guid'] ?>">

            <img src="<?= $university['image_url'] ?>" alt="University Image">

        </a>

    </div>

    <div class="university-content">

        <a href="<?= $university['guid'] ?>"><h3><?= $university['title'] ?></h3></a>

        <p class="country"><?= $university['country'] ?></p>

        <p><?= $university['description'] ?></p>

        <div class="university-attributes">

            <div class="attribute">

                <img src="<?= SIT_SEARCH_ASSETS . 'images/ranking.png' ?>" alt="University attribute">

                <div class="attribute-content">

                    <h4>Rankings</h4>

                    <p><?= $university['ranking'] ?></p>

                </div>

            </div>

            <div class="attribute">

                <img src="<?= SIT_SEARCH_ASSETS . 'images/accomodation.png' ?>" alt="University attribute">

                <div class="attribute-content">

                    <h4>Accommodation</h4>

                    <p><?= $university['accommodation'] ?></p>

                </div>

            </div>

            <div class="attribute">

                <img src="<?= SIT_SEARCH_ASSETS . 'images/calendar.png' ?>" alt="University attribute">

                <div class="attribute-content">

                    <h4>Year</h4>

                    <p><?= $university['year'] ?></p>

                </div>

            </div>

            <div class="attribute">

                <img src="<?= SIT_SEARCH_ASSETS . 'images/graduation-gown.png' ?>" alt="University attribute">

                <div class="attribute-content">

                    <h4>Students</h4>

                    <p><?= $university['students'] ?></p>

                </div>

            </div>

        </div>

    </div>

</div>



