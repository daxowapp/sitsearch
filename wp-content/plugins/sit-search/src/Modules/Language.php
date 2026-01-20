<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Language extends Module
{

    public static $module='Languages';

    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                $term = get_term_by('name', $item['Name'], 'sit-language');
                if (!$term) {
                    self::create_item($item);
                }

                $fields = self::get_fields();

                foreach ($fields as $field) {
                    if (isset($item[$field['field_name']])) {
                        update_term_meta($term->term_id, $field['field_name'], $item[$field['field_name']]);
                    }
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
        $term = get_term_by('name', $data['Name'], 'sit-language');
        if ($term) {
            return $term->term_id;
        }

        $term = wp_insert_term($data['Name'], 'sit-language');

        if (is_wp_error($term)) {
            return $term;
        }

        update_term_meta($term['term_id'], 'zoho_language_id', $data['id']);
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
            return get_term($post_id, 'sit-language');
        } else {
            $zoho_language_id = get_term_meta($post_id, 'zoho_language_id', true);

            $zoho = new Zoho();
            $item = $zoho->request(static::$module . '/' . $zoho_language_id);

            return $item['data'];
        }
    }
}