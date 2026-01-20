<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class ApplyProgram
{
    public function __invoke()
    {
        if(!isset($_GET['prog_id'])){
            exit();
        }

        $prgram_id = $_GET['prog_id'];
        $prgram = get_post($prgram_id);
        $current_uni_id=get_post_meta($prgram_id, 'zh_university', true);
        if(empty($prgram)){
            exit();
        }
    }
}