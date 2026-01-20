<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Campus extends Module
{
    protected static $module = 'Campus';

    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            Functions::create_field_if_needed($field, 'group_67bf030cdb4fd');
        }

        do {
            $items = self::get_items($page, $per_page);
            foreach ($items['data'] as $item) {
                $zoho_id = $item['id'];

                $zoho = new Zoho();

                $campus = $zoho->request('Campus/' . $zoho_id);

                if ($campus['data']) {
                    $post = Functions::getPostByMeta('zoho_campus_id', $zoho_id, 'sit-campus');

                    if ($post) {
                        $post_id = $post->ID;

                        $fields = \SIT\Search\Modules\Campus::get_fields();

                        $item = $campus['data'][0];

                        foreach ($fields as $field) {
                            Functions::create_field_if_needed($field, 'group_67bf030cdb4fd');
                            if (isset($item[$field['field_name']])) {

                                if ($field['field_name'] == 'Name') {
                                    $post_title = $item[$field['field_name']];
                                    wp_update_post([
                                        'ID' => $post_id,
                                        'post_title' => $post_title,
                                    ]);
                                }

                                if ($field['field_name'] == 'University') {

                                    $university = get_posts([
                                        'post_type' => 'sit-university',
                                        'meta_key' => 'zoho_account_id',
                                        'meta_value' => $item['University']['id']
                                    ]);

                                    if ($university) {
                                        update_field('zh_university', $university[0]->ID, $post_id);
                                        update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                                    }

                                    continue;
                                }

                                if ($field['field_name'] == 'Faculty') {

                                    $facultydata = $zoho->request('Campus/' . $zoho_id.'/6421426000013599889');
                                    if ($facultydata['data']) {
                                        $faculty_ids = [];
                                        foreach($facultydata['data'] as $val){
                                            $faid=$val['Faculty']['id'];
                                            $faname=$val['Faculty']['name'];
                                            $argsdd = array(
                                                'meta_key' => 'zoho_faculty_id',
                                                'meta_value' => $faid,
                                                'taxonomy' => 'sit-faculty',
                                                'hide_empty' => false,
                                            );

                                            $faculty = get_terms($argsdd);

                                            $faculty = $faculty ? reset($faculty) : null;

                                            if (!$faculty) {
                                                $faculty = wp_insert_term($faname, 'sit-faculty');
                                                $faculty_ids[] = $faculty['term_id'];
                                            } else {
                                                $faculty_ids[] = $faculty->term_id;
                                            }
                                        }
                                        update_post_meta($post_id, 'sit-faculty', implode(',', $faculty_ids));
                                    }
                                    else{
                                        update_post_meta($post_id, 'sit-faculty', 'good');
                                    }
                                    continue;
                                }

                                update_field($field['field_name'], $item[$field['field_name']], $post_id);

                            } else {
                            }
                        }

                    } else {

                        $post_id = \SIT\Search\Modules\Campus::create_item($campus['data'][0]);

                    }
                }
            }

            $page++;
        } while ($items['info']['more_records'] && $page <= $limit);
    }

    public static function create_item($data)
    {
        $zoho = new Zoho();
        $post_id = wp_insert_post([
            'post_title' => $data['Name'],
            'post_type' => 'sit-campus',
            'post_status' => 'publish'
        ]);

        if ($post_id) {
            update_field('zoho_campus_id', $data['id'], $post_id);
        }

        $fields = self::get_fields();

        foreach ($fields as $field) {

            if ($field['field_name'] == 'University') {

                $university = get_posts([
                    'post_type' => 'sit-university',
                    'meta_key' => 'zoho_account_id',
                    'meta_value' => $data['University']['id']
                ]);

                if ($university) {
                    update_field('zh_university', $university[0]->ID, $post_id);
                }

                continue;
            }
            if ($field['field_name'] == 'Faculty') {

                $facultydata = $zoho->request('Campus/' .  $data['id'].'/6421426000013599889');
                if ($facultydata['data']) {
                    $faculty_ids = [];
                    foreach($facultydata['data'] as $val){
                        $faid=$val['Faculty']['id'];
                        $faname=$val['Faculty']['name'];
                        $argsdd = array(
                            'meta_key' => 'zoho_faculty_id',
                            'meta_value' => $faid,
                            'taxonomy' => 'sit-faculty',
                            'hide_empty' => false,
                        );

                        $faculty = get_terms($argsdd);

                        $faculty = $faculty ? reset($faculty) : null;

                        if (!$faculty) {
                            $faculty = wp_insert_term($faname, 'sit-faculty');
                            $faculty_ids[] = $faculty['term_id'];
                        } else {
                            $faculty_ids[] = $faculty->term_id;
                        }
                    }
                    update_post_meta($post_id, 'sit-faculty', implode(',', $faculty_ids));
                }
                else{
                    update_post_meta($post_id, 'sit-faculty', 'good');
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
        // TODO: Implement get_enabled_fields() method.
    }

    public static function get_item($post_id, $type = 'post')
    {
        if ($type == 'post') {
            return get_post($post_id);
        } else {
            $zoho_id = get_field('zoho_account_id', $post_id);

            return $zoho_id ? (new Zoho())->request('Accounts/' . $zoho_id) : null;
        }
    }

}