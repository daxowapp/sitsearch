<?php
$recent_search = isset($_COOKIE['recent_search']) ? json_decode(stripslashes($_COOKIE['recent_search']), true) : [];
$recent_search=array_slice($recent_search, -5);
?>
<div class="sit-search" id="sit-search">
    <form action="<?= home_url('/results/') ?>" method="GET" id="search-bar">
        <div class="form-group">
            <img src="<?= STI_SEARCH_URL ?>assets/images/graduation-gown.png"/>
            <select class="select2" name="speciality">
                <option value="0" selected>What do you want to study?</option>
                <?php
                foreach ($specialities as $speciality) {
                    if (isset($_GET['speciality'])) {
                        if ($_GET['speciality'] == $speciality->term_id) {
                            $sel = 'selected';
                        } else {
                            $sel = '';
                        }
                    } else {
                        $sel = '';
                    }
                    echo '<option ' . $sel . ' value="' . $speciality->term_id . '">' . $speciality->name . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <img src="<?= STI_SEARCH_URL ?>assets/images/accomodation.png"/>
            <select class="select2" name="country">
                <option value="0" <?php echo empty($_GET['country']) ? 'selected' : ''; ?>>Where do you want to study?</option>
                <?php
                foreach ($countries as $country) {
                    $sel = '';
                    if (isset($_GET['country']) && $_GET['country'] == $country->term_id) {
                        $sel = 'selected';
                    }
                    echo '<option ' . $sel . ' value="' . $country->term_id . '">' . $country->name . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <img src="<?= STI_SEARCH_URL ?>assets/images/ranking.png"/>
            <select class="select2" name="level">
                <option value="0" selected>What level do you want to study?</option>
                <?php
                foreach ($degrees as $degree) {
                    if (isset($_GET['level'])) {
                        if ($_GET['level'] == $degree->term_id) {
                            $sel = 'selected';
                        } else {
                            $sel = '';
                        }
                    } else {
                        $sel = '';
                    }
                    echo '<option ' . $sel . ' value="' . $degree->term_id . '">' . $degree->name . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <img src="<?= STI_SEARCH_URL ?>assets/images/accomodation.png"/>
            <select class="select2" name="univerity-type">
                <option value="0" selected>University Type</option>
                <option <?php if(isset($_GET['univerity-type']) && $_GET['univerity-type'] == 'All'){ echo 'selected'; }  ?> value="All">All</option>
                <option <?php if(isset($_GET['univerity-type']) && $_GET['univerity-type'] == 'Public'){ echo 'selected'; }   ?> value="Public">Public</option>
                <option <?php if(isset($_GET['univerity-type']) && $_GET['univerity-type'] == 'Private'){ echo 'selected'; }  ?> value="Private">Private</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if ($recent_search) : ?>
        <div class="recent-searches">
            <h3>Recent Searches</h3>
            <ul>

                <?php
                foreach ($recent_search as $search) {
                    // Safely get term data
                    $speciality_id = isset($search['speciality']) ? $search['speciality'] : 0;
                    $country_id = isset($search['country']) ? $search['country'] : 0;
                    $level_id = isset($search['level']) ? $search['level'] : 0;
                    
                    $speciality_term = get_term($speciality_id, 'sit-speciality');
                    $country_term = get_term($country_id, 'sit-country');
                    
                    $speciality_name = (!is_wp_error($speciality_term) && $speciality_term) ? $speciality_term->name : '';
                    $country_name = (!is_wp_error($country_term) && $country_term) ? $country_term->name : '';
                    ?>
                    <li>
                        <a href="/results/?speciality=<?php echo esc_attr($speciality_id); ?>&country=<?php echo esc_attr($country_id); ?>&level=<?php echo esc_attr($level_id); ?>">
                            <svg style="margin-right:5px;" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.9375 6.25003V9.46956L13.607 11.0711C13.715 11.1332 13.8095 11.2162 13.885 11.3152C13.9605 11.4143 14.0155 11.5274 14.0469 11.6479C14.0782 11.7684 14.0852 11.894 14.0675 12.0173C14.0497 12.1405 14.0076 12.259 13.9436 12.3658C13.8795 12.4727 13.7948 12.5656 13.6945 12.6394C13.5941 12.7131 13.48 12.7661 13.3589 12.7952C13.2379 12.8243 13.1122 12.8291 12.9893 12.8091C12.8663 12.7892 12.7486 12.7449 12.643 12.6789L9.51797 10.8039C9.37909 10.7207 9.26414 10.6029 9.18432 10.462C9.1045 10.3211 9.06253 10.1619 9.0625 10V6.25003C9.0625 6.00138 9.16127 5.76293 9.33709 5.58711C9.5129 5.4113 9.75136 5.31253 10 5.31253C10.2486 5.31253 10.4871 5.4113 10.6629 5.58711C10.8387 5.76293 10.9375 6.00138 10.9375 6.25003ZM10 2.18753C8.97273 2.18488 7.95515 2.38612 7.00621 2.77959C6.05728 3.17306 5.19586 3.75092 4.47188 4.47971C4.10547 4.85003 3.76875 5.21174 3.4375 5.57346V5.00003C3.4375 4.75138 3.33873 4.51293 3.16291 4.33711C2.9871 4.1613 2.74864 4.06253 2.5 4.06253C2.25136 4.06253 2.0129 4.1613 1.83709 4.33711C1.66127 4.51293 1.5625 4.75138 1.5625 5.00003V8.12503C1.5625 8.37367 1.66127 8.61212 1.83709 8.78794C2.0129 8.96375 2.25136 9.06252 2.5 9.06252H5.625C5.87364 9.06252 6.1121 8.96375 6.28791 8.78794C6.46373 8.61212 6.5625 8.37367 6.5625 8.12503C6.5625 7.87638 6.46373 7.63793 6.28791 7.46211C6.1121 7.2863 5.87364 7.18753 5.625 7.18753H4.51328C4.92188 6.71878 5.34141 6.26721 5.80156 5.80159C6.62669 4.97622 7.67675 4.41233 8.82054 4.18038C9.96433 3.94842 11.1511 4.05869 12.2326 4.49741C13.3141 4.93612 14.2423 5.68383 14.9012 6.64708C15.5601 7.61034 15.9206 8.74642 15.9375 9.91337C15.9544 11.0803 15.627 12.2264 14.9963 13.2083C14.3656 14.1903 13.4594 14.9646 12.3911 15.4344C11.3228 15.9043 10.1397 16.0489 8.9897 15.8502C7.83967 15.6515 6.7737 15.1183 5.925 14.3172C5.74411 14.1465 5.50281 14.0546 5.25418 14.0618C5.00555 14.069 4.76995 14.1746 4.59922 14.3555C4.42849 14.5364 4.3366 14.7777 4.34378 15.0263C4.35096 15.2749 4.45661 15.5105 4.6375 15.6813C5.56487 16.5566 6.69242 17.1917 7.92171 17.531C9.151 17.8702 10.4447 17.9034 11.6897 17.6276C12.9348 17.3519 14.0934 16.7755 15.0645 15.9488C16.0355 15.1222 16.7894 14.0704 17.2604 12.8853C17.7314 11.7002 17.9051 10.4178 17.7663 9.15011C17.6276 7.88243 17.1807 6.66798 16.4645 5.61279C15.7484 4.55761 14.7848 3.69377 13.658 3.0967C12.5311 2.49964 11.2753 2.18749 10 2.18753Z"
                                      fill="#E20A17"></path>
                            </svg>
                            <div class="title">
                                <span><?= esc_html($speciality_name); ?></span> in
                                <?= esc_html($country_name); ?>
                            </div>
                            <svg style="margin:2px 0 0 5px;" width="18" height="18" viewBox="0 0 18 18" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.5855 9.39804L10.523 14.4605C10.4174 14.5661 10.2743 14.6254 10.125 14.6254C9.97573 14.6254 9.83258 14.5661 9.72703 14.4605C9.62148 14.355 9.56219 14.2118 9.56219 14.0626C9.56219 13.9133 9.62148 13.7701 9.72703 13.6646L13.8298 9.56257H2.8125C2.66332 9.56257 2.52024 9.50331 2.41475 9.39782C2.30926 9.29233 2.25 9.14925 2.25 9.00007C2.25 8.85088 2.30926 8.70781 2.41475 8.60232C2.52024 8.49683 2.66332 8.43757 2.8125 8.43757H13.8298L9.72703 4.33554C9.62148 4.22999 9.56219 4.08684 9.56219 3.93757C9.56219 3.7883 9.62148 3.64515 9.72703 3.5396C9.83258 3.43405 9.97573 3.37476 10.125 3.37476C10.2743 3.37476 10.4174 3.43405 10.523 3.5396L15.5855 8.6021C15.6378 8.65434 15.6793 8.71638 15.7076 8.78466C15.7359 8.85295 15.7504 8.92615 15.7504 9.00007C15.7504 9.07399 15.7359 9.14719 15.7076 9.21547C15.6793 9.28376 15.6378 9.3458 15.5855 9.39804Z"
                                      fill="#E20A17"></path>
                            </svg>
                        </a>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    <?php endif; ?>
</div>