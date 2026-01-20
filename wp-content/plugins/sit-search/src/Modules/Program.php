<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class Program extends Module
{
    protected static $module = 'Products';

    public static function up_program(int $proid=0){
        $zoho = new Zoho();

        $program = $zoho->request('Products/' . $proid);

        if ($program['data']) {
            $post = Functions::getPostByMeta('zoho_product_id', $proid, 'sit-program');

            if ($post) {

                $post_id = $post->ID;

                $fields = \SIT\Search\Modules\Program::get_fields();

                $item = $program['data'][0];

                foreach ($fields as $field) {
//                    Functions::create_field_if_needed($field, 'group_6798c60f8e151');
                    if (isset($item[$field['field_name']])) {

                        if ($field['field_name'] == 'Product_Name') {
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

                        if ($field['field_name'] == 'City') {

                            $city_zoho_id = $item['City']['id'];

                            $args = array(
                                'meta_key' => 'zoho_city_id',
                                'meta_value' => $city_zoho_id,
                                'taxonomy' => 'sit-city',
                                'hide_empty' => false,
                            );

                            $city = get_terms($args);

                            $city = $city ? reset($city) : null;

                            wp_set_object_terms($post_id, null, 'sit-city');

                            if (!$city) {
                                $city = wp_insert_term($item['Country']['name'], 'sit-city');
                                update_term_meta($city['term_id'], 'zoho_city_id', $city_zoho_id);

                                wp_set_object_terms($post_id, $city['term_id'], 'sit-city');
                            } else {
                                wp_set_object_terms($post_id, $city->term_id, 'sit-city');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Country') {

                            $country_zoho_id = $item['Country']['id'];

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
                                $country = wp_insert_term($item['Country']['name'], 'sit-country');
                                update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                                wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                            } else {
                                wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Faculty') {

                            $faculty_zoho_id = $item['Faculty']['id'];

                            $args = array(
                                'meta_key' => 'zoho_faculty_id',
                                'meta_value' => $faculty_zoho_id,
                                'taxonomy' => 'sit-faculty',
                                'hide_empty' => false,
                            );

                            $faculty = get_terms($args);

                            $faculty = $faculty ? reset($faculty) : null;

                            wp_set_object_terms($post_id, null, 'sit-faculty');

                            if (!$faculty) {
                                $faculty = wp_insert_term($item['Faculty']['name'], 'sit-faculty');
                                update_term_meta($faculty['term_id'], 'zoho_faculty_id', $faculty_zoho_id);

                                wp_set_object_terms($post_id, $faculty['term_id'], 'sit-faculty');
                            } else {
                                wp_set_object_terms($post_id, $faculty->term_id, 'sit-faculty');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Speciality') {

                            $speciality_zoho_id = $item['Speciality']['id'];

                            $args = array(
                                'meta_key' => 'zoho_speciality_id',
                                'meta_value' => $speciality_zoho_id,
                                'taxonomy' => 'sit-speciality',
                                'hide_empty' => false,
                            );

                            $speciality = get_terms($args);
                            $speciality = $speciality ? reset($speciality) : null;

                            wp_set_object_terms($post_id, null, 'sit-speciality');



                            if (!$speciality) {

                                $speciality = wp_insert_term($item['Speciality']['name'], 'sit-speciality');

                                update_term_meta($speciality['term_id'], 'zoho_speciality_id', $speciality_zoho_id);

                                wp_set_object_terms($post_id, $speciality['term_id'], 'sit-speciality');
                            } else {
                                wp_set_object_terms($post_id, $speciality->term_id, 'sit-speciality');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Degrees') {

                            $zoho_degree_id = $item['Degrees']['id'];

                            $args = array(
                                'meta_key' => 'zoho_degree_id',
                                'meta_value' => $zoho_degree_id,
                                'taxonomy' => 'sit-degree',
                                'hide_empty' => false,
                            );

                            $Degree = get_terms($args);

                            $Degree = $Degree ? reset($Degree) : null;

                            wp_set_object_terms($post_id, null, 'sit-degree');

                            if (!$Degree) {
                                $Degree = wp_insert_term($item['Degrees']['name'], 'sit-degree');
                                update_term_meta($Degree['term_id'], 'zoho_degree_id', $zoho_degree_id);

                                wp_set_object_terms($post_id, $Degree['term_id'], 'sit-degree');
                            } else {
                                wp_set_object_terms($post_id, $Degree->term_id, 'sit-degree');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Program_Languages') {

                            $zoho_language_id = $item['Program_Languages']['id'];

                            $args = array(
                                'meta_key' => 'zoho_language_id',
                                'meta_value' => $zoho_language_id,
                                'taxonomy' => 'sit-language',
                                'hide_empty' => false,
                            );

                            $Language = get_terms($args);

                            $Language = $Language ? reset($Language) : null;

                            wp_set_object_terms($post_id, null, 'sit-language');

                            if (!$Language) {
                                $Language = wp_insert_term($item['Program_Languages']['name'], 'sit-language');
                                update_term_meta($Language['term_id'], 'zoho_language_id', $zoho_language_id);

                                wp_set_object_terms($post_id, $Language['term_id'], 'sit-language');
                            } else {
                                wp_set_object_terms($post_id, $Language->term_id, 'sit-language');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        update_field($field['field_name'], $item[$field['field_name']], $post_id);

                    } else {
                    }
                }

            } else {

                $post_id = \SIT\Search\Modules\Program::create_item($program['data'][0]);

            }
        }
    }
    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            Functions::create_field_if_needed($field, 'group_6798c60f8e151');
        }

        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                if (!$item['Active_in_Search']) continue;

                $zoho_id = $item['id'];

                $post_id = get_posts([
                    'post_type' => 'sit-program',
                    'meta_key' => 'zoho_product_id',
                    'meta_value' => $zoho_id
                ]);

                if (!$post_id) {
                    $post_id = self::create_item($item);
                } else {
                    $post_id = $post_id[0]->ID;

                    $fields = self::get_fields();

                    foreach ($fields as $field) {



                        if (in_array($field['field_name'], ['Program_Title', 'Owner'])) {
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);

                            continue;
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

                        if ($field['field_name'] == 'Country') {

                            $country_zoho_id = $item['Country']['id'];

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
                                $country = wp_insert_term($item['Country']['name'], 'sit-country');
                                update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                                wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                            } else {
                                wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Faculty') {

                            $faculty_zoho_id = $item['Faculty']['id'];

                            $args = array(
                                'meta_key' => 'zoho_faculty_id',
                                'meta_value' => $faculty_zoho_id,
                                'taxonomy' => 'sit-faculty',
                                'hide_empty' => false,
                            );

                            $faculty = get_terms($args);

                            $faculty = $faculty ? reset($faculty) : null;

                            wp_set_object_terms($post_id, null, 'sit-faculty');

                            if (!$faculty) {
                                $faculty = wp_insert_term($item['Faculty']['name'], 'sit-faculty');
                                update_term_meta($faculty['term_id'], 'zoho_faculty_id', $faculty_zoho_id);

                                wp_set_object_terms($post_id, $faculty['term_id'], 'sit-faculty');
                            } else {
                                wp_set_object_terms($post_id, $faculty->term_id, 'sit-faculty');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Speciality') {

                            $speciality_zoho_id = $item['Speciality']['id'];

                            $args = array(
                                'meta_key' => 'zoho_speciality_id',
                                'meta_value' => $speciality_zoho_id,
                                'taxonomy' => 'sit-speciality',
                                'hide_empty' => false,
                            );

                            $speciality = get_terms($args);
                            $speciality = $speciality ? reset($speciality) : null;

                            wp_set_object_terms($post_id, null, 'sit-speciality');



                            if (!$speciality) {

                                $speciality = wp_insert_term($item['Speciality']['name'], 'sit-speciality');

                                update_term_meta($speciality['term_id'], 'zoho_speciality_id', $speciality_zoho_id);

                                wp_set_object_terms($post_id, $speciality['term_id'], 'sit-speciality');
                            } else {
                                wp_set_object_terms($post_id, $speciality->term_id, 'sit-speciality');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Degrees') {

                            $zoho_degree_id = $item['Degrees']['id'];

                            $args = array(
                                'meta_key' => 'zoho_degree_id',
                                'meta_value' => $zoho_degree_id,
                                'taxonomy' => 'sit-degree',
                                'hide_empty' => false,
                            );

                            $Degree = get_terms($args);

                            $Degree = $Degree ? reset($Degree) : null;

                            wp_set_object_terms($post_id, null, 'sit-degree');

                            if (!$Degree) {
                                $Degree = wp_insert_term($item['Degrees']['name'], 'sit-degree');
                                update_term_meta($Degree['term_id'], 'zoho_degree_id', $zoho_degree_id);

                                wp_set_object_terms($post_id, $Degree['term_id'], 'sit-degree');
                            } else {
                                wp_set_object_terms($post_id, $Degree->term_id, 'sit-degree');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'Program_Languages') {

                            $zoho_language_id = $item['Program_Languages']['id'];

                            $args = array(
                                'meta_key' => 'zoho_language_id',
                                'meta_value' => $zoho_language_id,
                                'taxonomy' => 'sit-language',
                                'hide_empty' => false,
                            );

                            $Language = get_terms($args);

                            $Language = $Language ? reset($Language) : null;

                            wp_set_object_terms($post_id, null, 'sit-language');

                            if (!$Language) {
                                $Language = wp_insert_term($item['Program_Languages']['name'], 'sit-language');
                                update_term_meta($Language['term_id'], 'zoho_language_id', $zoho_language_id);

                                wp_set_object_terms($post_id, $Language['term_id'], 'sit-language');
                            } else {
                                wp_set_object_terms($post_id, $Language->term_id, 'sit-language');
                            }
                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
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
            'post_title' => $data['Product_Name'],
            'post_type' => 'sit-program',
            'post_status' => 'publish'
        ]);

        if ($post_id) {
            update_field('zoho_product_id', $data['id'], $post_id);
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

            if ($field['field_name'] == 'Country') {

                $country_zoho_id = $data['Country']['id'];

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
                    $country = wp_insert_term($data['Country']['name'], 'sit-country');
                    update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                    wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                } else {
                    wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                }

                continue;
            }

            if ($field['field_name'] == 'Faculty') {

                $faculty_zoho_id = $data['Faculty']['id'];

                $args = array(
                    'meta_key' => 'zoho_faculty_id',
                    'meta_value' => $faculty_zoho_id,
                    'taxonomy' => 'sit-faculty',
                    'hide_empty' => false,
                );

                $faculty = get_terms($args);

                $faculty = $faculty ? reset($faculty) : null;

                wp_set_object_terms($post_id, null, 'sit-faculty');

                if (!$faculty) {
                    $faculty = wp_insert_term($data['Faculty']['name'], 'sit-faculty');
                    update_term_meta($faculty['term_id'], 'zoho_faculty_id', $faculty_zoho_id);

                    wp_set_object_terms($post_id, $faculty['term_id'], 'sit-faculty');
                } else {
                    wp_set_object_terms($post_id, $faculty->term_id, 'sit-faculty');
                }

                continue;
            }

            if ($field['field_name'] == 'Speciality') {

                $speciality_zoho_id = $data['Speciality']['id'];

                $args = array(
                    'meta_key' => 'zoho_speciality_id',
                    'meta_value' => $speciality_zoho_id,
                    'taxonomy' => 'sit-speciality',
                    'hide_empty' => false,
                );

                $speciality = get_terms($args);

                $speciality = $speciality ? reset($speciality) : null;

                wp_set_object_terms($post_id, null, 'sit-speciality');

                if (!$speciality) {
                    $speciality = wp_insert_term($data['Speciality']['name'], 'sit-speciality');
                    update_term_meta($speciality['term_id'], 'zoho_speciality_id', $speciality_zoho_id);

                    wp_set_object_terms($post_id, $speciality['term_id'], 'sit-speciality');
                } else {
                    wp_set_object_terms($post_id, $speciality->term_id, 'sit-speciality');
                }

                continue;
            }

            if ($field['field_name'] == 'Degrees') {

                $zoho_degree_id = $data['Degrees']['id'];

                $args = array(
                    'meta_key' => 'zoho_degree_id',
                    'meta_value' => $zoho_degree_id,
                    'taxonomy' => 'sit-degree',
                    'hide_empty' => false,
                );

                $Degree = get_terms($args);

                $Degree = $Degree ? reset($Degree) : null;

                wp_set_object_terms($post_id, null, 'sit-degree');

                if (!$Degree) {
                    $Degree = wp_insert_term($data['Degrees']['name'], 'sit-degree');
                    update_term_meta($Degree['term_id'], 'zoho_degree_id', $zoho_degree_id);

                    wp_set_object_terms($post_id, $Degree['term_id'], 'sit-degree');
                } else {
                    wp_set_object_terms($post_id, $Degree->term_id, 'sit-degree');
                }
                update_field($field['field_name'], $data[$field['field_name']]['name'], $post_id);
                continue;
            }

            if ($field['field_name'] == 'Program_Languages') {

                $zoho_language_id = $data['Program_Languages']['id'];

                $args = array(
                    'meta_key' => 'zoho_language_id',
                    'meta_value' => $zoho_language_id,
                    'taxonomy' => 'sit-language',
                    'hide_empty' => false,
                );

                $Language = get_terms($args);

                $Language = $Language ? reset($Language) : null;

                wp_set_object_terms($post_id, null, 'sit-language');

                if (!$Language) {
                    $Language = wp_insert_term($data['Program_Languages']['name'], 'sit-language');
                    update_term_meta($Language['term_id'], 'zoho_language_id', $zoho_language_id);

                    wp_set_object_terms($post_id, $Language['term_id'], 'sit-language');
                } else {
                    wp_set_object_terms($post_id, $Language->term_id, 'sit-language');
                }
                update_field($field['field_name'], $data[$field['field_name']]['name'], $post_id);
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