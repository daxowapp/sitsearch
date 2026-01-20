<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class City extends Module
{
    public static $module = 'City';

    /**
     * @param int $page
     * @param int $per_page
     */
    public static function sync(int $page = 1, int $per_page = 1, $limit=1)
    {
        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                $term = get_term_by('name', $item['Name'], 'sit-city');
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
        $term = get_term_by('name', $data['Name'], 'sit-city');
        if ($term) {
            return $term->term_id;
        }

        $term = wp_insert_term($data['Name'], 'sit-city');

        if (is_wp_error($term)) {
            return $term;
        }

        update_term_meta($term['term_id'], 'zoho_city_id', $data['id']);

        if (isset($data['Country_Form']['id'])) {
            update_term_meta($term['term_id'], 'zoho_parent_id', $data['Country_Form']['id']);
        }
        return $term['term_id'];
    }


    public static function get_item($post_id, $type = 'term')
    {
        if ($type === 'term') {
            return get_term($post_id, 'sit-city');
        } else {
            $zoho_city_id = get_term_meta($post_id, 'zoho_city_id', true);

            $zoho = new Zoho();
            $item = $zoho->request(static::$module . '/' . $zoho_city_id);

            return $item['data'];
        }
    }
}