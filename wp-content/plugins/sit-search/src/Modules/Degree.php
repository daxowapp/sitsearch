<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Degree extends Module
{
    public static $module = 'Degrees';

    /**
     * @param int $page
     * @param int $per_page
     * @param int $limit
     * @return void
     */
    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                $term = get_term_by('name', $item['Name'], 'sit-degree');
                if (!$term) {
                    self::create_item($item);
                }

                $fields = self::get_fields();

                foreach ($fields as $field) {
                    if (isset($item[$field['field_name']])) {
                        if($field['field_name'] == 'Active_In_Search') {
                            $actv=$item[$field['field_name']];
                            if(!empty($actv) && $actv == '1'){
                                update_term_meta($term->term_id, 'active_in_search', $actv);
                            }
                            else{
                                update_term_meta($term->term_id, 'active_in_search', '');
                            }
                            continue;
                        }
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
        $term = get_term_by('name', $data['Name'], 'sit-degree');
        if ($term) {
            return $term->term_id;
        }

        $term = wp_insert_term($data['Name'], 'sit-degree');

        if (is_wp_error($term)) {
            return $term;
        }

        if($data['Active_In_Search'] == '1') {
            update_term_meta($term['term_id'], 'active_in_search',$data['Active_In_Search']);
        }
        else{
            update_term_meta($term['term_id'], 'active_in_search', '');
        }

        update_term_meta($term['term_id'], 'zoho_degree_id', $data['id']);
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
            return get_term($post_id, 'sit-degree');
        } else {
            $zoho_degree_id = get_term_meta($post_id, 'zoho_degree_id', true);

            $zoho = new Zoho();
            $item = $zoho->request(static::$module . '/' . $zoho_degree_id);

            return $item['data'];
        }
    }
}