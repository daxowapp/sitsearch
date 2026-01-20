<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;
class breadcrump
{
    public function __invoke()
    {
        $degree = isset($_GET['level']) && $_GET['level'] != 0 ? intval($_GET['level']) : '';
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';

        ob_start();
        Template::render('shortcodes/bread-crump',['degreeid'=>$degree,'specialityid'=>$speciality,'countryid'=>$country]);
        return ob_get_clean();
    }
}