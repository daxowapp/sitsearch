<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Lead extends Module
{
    public static $module = 'Leads';

    /**
     * @param int $page
     * @param int $per_page
     * @param int $limit
     */
    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {

    }

    /**
     * @param $data
     * @return mixed
     */
    public static function create_item($data)
    {

    }

    /**
     * @param $post_id
     * @param $type
     * @return mixed
     */
    public static function get_item($post_id, $type = 'term')
    {

    }

}