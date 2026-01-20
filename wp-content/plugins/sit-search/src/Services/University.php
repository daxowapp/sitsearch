<?php

namespace SIT\Search\Services;

class University
{
    public static function sync($page = 1, $per_page = 1)
    {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            Functions::create_field_if_needed($field, 'University');
        }

        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                $zoho_id = $item['id'];

                $post_id = get_posts([
                    'post_type' => 'university',
                    'meta_key' => 'zoho_account_id',
                    'meta_value' => $zoho_id
                ]);

                if (!$post_id) {
                    $post_id = self::create_item($item);
                } else {
                    $post_id = $post_id[0]->ID;

                    $fields = self::get_fields();

                    foreach ($fields as $field) {
                        if (isset($item[$field['field_name']])) {
                            update_field($field['field_name'], $item[$field['field_name']], $post_id);
                        }
                    }
                }
            }

            $page++;
        } while ($items['info']['more_records']);
    }

    public static function get_fields()
    {
        $zoho = new Zoho();
        return $zoho->get_fields('Accounts');
    }

    public static function get_items($page = 1, $per_page = 10)
    {
        $zoho = new Zoho();
        return $zoho->request('Accounts?page=' . $page . '&per_page=' . $per_page);
    }

    public static function create_item($data)
    {
        $post_id = wp_insert_post([
            'post_title' => $data['Account_Name'],
            'post_type' => 'sit-university',
            'post_status' => 'publish'
        ]);

        if ($post_id) {
            update_field('zoho_account_id', $data['id'], $post_id);
        }

        $fields = self::get_fields();

        foreach ($fields as $field) {
            if (isset($data[$field['field_name']])) {
                update_field($field['field_name'], $data[$field['field_name']], $post_id);
            }
        }

        return $post_id;
    }

    public static function get_enabled_fields()
    {
        return get_option('university_enabled_fields', []);
    }

    public static function get_item($post_id, $type = 'post'): array|\WP_Post|null
    {
        if ($type == 'post') {
            return get_post($post_id);
        } else {
            $zoho_id = get_field('zoho_account_id', $post_id);

            return $zoho_id ? (new Zoho())->request('Accounts/' . $zoho_id) : null;
        }
    }
}