<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class University extends Module
{
    protected static $module = 'Accounts';

    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            Functions::create_field_if_needed($field, 'group_67936c1053468');
        }

        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                if (!$item['Active_in_Search']) continue;
                $zoho_id = $item['id'];

                $post_id = get_posts([
                    'post_type' => 'sit-university',
                    'meta_key' => 'zoho_account_id',
                    'meta_value' => $zoho_id
                ]);

                if (!$post_id) {
                    $post_id = self::create_item($item);
                } else {
                    $post_id = $post_id[0]->ID;

                    $fields = self::get_fields();

                    foreach ($fields as $field) {

                        if ($field['field_name'] == 'University_Country' && isset($item['University_Country']['id'])) {

                            $country_zoho_id = $item['University_Country']['id'];

                            $args = array(
                                'meta_key' => 'zoho_country_id',
                                'meta_value' => $country_zoho_id,
                                'taxonomy' => 'sit-country',
                                'hide_empty' => false,
                            );

                            $country = get_terms($args);

                            $country = $country ? reset($country) : null;

                            wp_set_object_terms($post_id, null, 'sit-country');

                            if (!$country) {
                                $country = wp_insert_term($item['University_Country']['Name'], 'sit-country');
                                update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                                wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                            } else {
                                wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'File_Upload_1' && isset($item['File_Upload_1'])) {
                            $file_id_api=$item['File_Upload_1'][0]['file_Id'];
                            $file_id=get_post_meta($post_id ,'file_id',true);
                            if($file_id != $file_id_api){
                                update_post_meta($post_id ,'file_id',$item['File_Upload_1'][0]['file_Id']);
                                $att_id=$item['File_Upload_1'][0]['attachment_Id'];
                                $file_name=$item['File_Upload_1'][0]['file_Name'];
                                $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                                if ($image_path) {
                                    update_post_meta($post_id ,'uni_image',$image_path);
                                } else {
                                    update_post_meta($post_id ,'uni_image','null');
                                }
                            }
                            continue;
                        }

                        if ($field['field_name'] == 'University_Logo' && isset($item['University_Logo'])) {
                            $file_id_api=$item['University_Logo'][0]['file_Id'];
                            $file_id=get_post_meta($post_id ,'file_logo__id',true);
                            if($file_id != $file_id_api){
                                update_post_meta($post_id ,'file_logo__id',$item['University_Logo'][0]['file_Id']);
                                $att_id=$item['University_Logo'][0]['attachment_Id'];
                                $file_name=$item['University_Logo'][0]['file_Name'];
                                $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                                if ($image_path) {
                                    update_post_meta($post_id ,'uni_logo',$image_path);
                                } else {
                                    update_post_meta($post_id ,'uni_logo','null');
                                }
                            }
                            continue;
                        }

                        if (isset($item[$field['field_name']])) {
                            update_field($field['field_name'], $item[$field['field_name']], $post_id);
                        }
                    }
                }
            }

            $page++;
        } while ($items['info']['more_records'] && $page <= $limit);
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

            if ($field['field_name'] == 'University_Country' && isset($data['University_Country']['id'])) {

                $country_zoho_id = $data['University_Country']['id'];

                $args = array(
                    'meta_key' => 'zoho_country_id',
                    'meta_value' => $country_zoho_id,
                    'taxonomy' => 'sit-country',
                    'hide_empty' => false,
                );

                $country = get_terms($args);

                $country = $country ? reset($country) : null;

                wp_set_object_terms($post_id, null, 'sit-country');

                if (!$country) {
                    $country = wp_insert_term($data['University_Country']['Name'], 'sit-country');
                    update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                    wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                } else {
                    wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                }

                update_field($field['field_name'], $data[$field['field_name']]['name'], $post_id);
                continue;
            }

            if ($field['field_name'] == 'File_Upload_1' && isset($data['File_Upload_1'])) {
                $file_id_api=$data['File_Upload_1'][0]['file_Id'];
                $file_id=get_post_meta($post_id ,'file_id',true);
                if($file_id != $file_id_api){
                    update_post_meta($post_id ,'file_id',$data['File_Upload_1'][0]['file_Id']);
                    $att_id=$data['File_Upload_1'][0]['attachment_Id'];
                    $file_name=$data['File_Upload_1'][0]['file_Name'];
                    $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                    if ($image_path) {
                        update_post_meta($post_id ,'uni_image',$image_path);
                    } else {
                        update_post_meta($post_id ,'uni_image','null');
                    }
                }
                continue;
            }

            if ($field['field_name'] == 'University_Logo' && isset($data['University_Logo'])) {
                $file_id_api=$data['University_Logo'][0]['file_Id'];
                $file_id=get_post_meta($post_id ,'file_logo__id',true);
                if($file_id != $file_id_api){
                    update_post_meta($post_id ,'file_logo__id',$data['University_Logo'][0]['file_Id']);
                    $att_id=$data['University_Logo'][0]['attachment_Id'];
                    $file_name=$data['University_Logo'][0]['file_Name'];
                    $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                    if ($image_path) {
                        update_post_meta($post_id ,'uni_logo',$image_path);
                    } else {
                        update_post_meta($post_id ,'uni_logo','null');
                    }
                }
                continue;
            }


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