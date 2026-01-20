<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Program extends Webhook
{
    private SIT_Logger $logger;

    /**
     * Handle the incoming data for the Program webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        $this->logger = SIT_Logger::get_instance();

        $program_id = $data['program_id'];

        if (!$program_id) {

            $this->logger->log_message('info', 'Program Id not found');

            return [
                'status' => 'error',
                'message' => 'Invalid or missing $program_id.',
            ];
        }

        $zoho = new Zoho();

        $program = $zoho->request('Products/' . $program_id);
        $this->logger->log_message('info', 'Program data: ' . json_encode($program));

        if ($program['data']) {
            $post = Functions::getPostByMeta('zoho_product_id', $program_id, 'sit-program');

            if ($post) {
                $this->logger->log_message('info', 'Program post found with ID: ' . $post->ID);
                $post_id = $post->ID;

                $fields = \SIT\Search\Modules\Program::get_fields();

                $item = $program['data'][0];
                $this->logger->log_message('info', print_r($fields, true));

                foreach ($fields as $field) {
                    Functions::create_field_if_needed($field, 'group_6798c60f8e151');
                    if (isset($item[$field['field_name']])) {

                        if ($field['field_name'] == 'Product_Name') {
                            $post_title = $item[$field['field_name']];
                            $this->logger->log_message('info', 'Updating post title: ' . $post_title);
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

                        $this->logger->log_message('info', 'Updating field: ' . $field['field_name']);

                        update_field($field['field_name'], $item[$field['field_name']], $post_id);

                        $this->logger->log_message('info', 'Field updated: ' . $field['field_name']);
                    } else {
                        $this->logger->log_message('info', 'Field not found: ' . $field['field_name']);
                    }
                }

            } else {
                $this->logger->log_message('info', 'Program post not found, creating new post.');

                $post_id = \SIT\Search\Modules\Program::create_item($program['data'][0]);

                $this->logger->log_message('info', 'Program post created with ID: ' . $post_id);
            }

            return [
                'status' => 'success',
                'data' => $program['data'],
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Invalid or missing data121.',
        ];
    }
}
