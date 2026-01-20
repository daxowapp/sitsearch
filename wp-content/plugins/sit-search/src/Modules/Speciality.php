<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Speciality extends Module
{
    public static $module = 'Speciality';

    /**
     * @param int $page
     * @param int $per_page
     * @param int $limit
     */
    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                $term = get_term_by('name', $item['Name'], 'sit-speciality');
                if (!$term) {
                    self::create_item($item);
                }
            }
        } while ($items['info']['more_records'] && $page <= $limit);
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function create_item($data)
    {
        $term = get_term_by('name', $data['Name'], 'sit-speciality');
        if ($term) {
            return $term->term_id;
        }

        $term = wp_insert_term($data['Name'], 'sit-speciality');

        if (is_wp_error($term)) {
            return $term;
        }

        update_term_meta($term['term_id'], 'zoho_speciality_id', $data['id']);
        if (!empty($data['Active'])) {
            $Active=$data['Active'];
            if($Active == '1'){
                update_term_meta($term['term_id'] ,'active_in_search',$Active);
            }
            else{
                update_term_meta($term['term_id'] ,'active_in_search','');
            }
        }
        else{
            update_term_meta($term['term_id'] ,'active_in_search','');
        }
        return $term['term_id'];
    }

    /**
     * @param $post_id
     * @param $type
     * @return mixed
     */
    public static function get_item($post_id, $type = 'term')
    {
        if ($type === 'term') {
            return get_term($post_id, 'sit-speciality');
        } else {
            $zoho_speciality_id = get_term_meta($post_id, 'zoho_speciality_id', true);

            $zoho = new Zoho();
            $item = $zoho->request(static::$module . '/' . $zoho_speciality_id);

            return $item['data'];
        }
    }
}