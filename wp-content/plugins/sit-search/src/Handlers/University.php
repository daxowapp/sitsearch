<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class University extends Webhook
{
    private SIT_Logger $logger;

    /**
     * Handle the incoming data for the university webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        try {
            $this->logger = SIT_Logger::get_instance();

            $university_id = $data['university_id'];

            if (!$university_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing university_id.',
                ];
            }

            $zoho = new Zoho();

            $university = $zoho->request('Accounts/' . $university_id);

            $this->logger->log_message('info', 'University data: ' . json_encode($university));

            if ($university['data']) {
                $post = Functions::getPostByMeta('zoho_account_id', $university_id, 'sit-university');
                if ($post) {
                    $this->logger->log_message('info', 'University post found with ID: ' . $post->ID);
                    $post_id = $post->ID;

                    $fields = \SIT\Search\Modules\University::get_fields();

                    $item = $university['data'][0];

                    $this->logger->log_message('info', print_r($fields, true));

                    foreach ($fields as $field) {
                        Functions::create_field_if_needed($field, 'group_67936c1053468');
                        $this->logger->log_message('info', 'field to map'.$field['field_name']);
                        if (isset($item[$field['field_name']])) {

                            if ($field['field_name'] == 'Account_Name') {
                                $post_title = $item[$field['field_name']];
                                $this->logger->log_message('info', 'Updating post title: ' . $post_title);
                                wp_update_post([
                                    'ID' => $post_id,
                                    'post_title' => $post_title,
                                ]);

//                                $slug = sanitize_title($post_title);
//                                $slug_exists = get_page_by_path($slug, OBJECT, 'sit-university');
//                                if ($slug_exists) {
//                                    $slug = $slug . '-' . $post_id;
//                                }
//
//                                $this->logger->log_message('info', 'Updating post slug: ' . $slug);
//                                wp_update_post([
//                                    'ID' => $post_id,
//                                    'post_name' => $slug,
//                                ]);
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

                            if ($field['field_name'] == 'University_City' && isset($item['University_City']['id'])) {

                                $city_zoho_id = $item['University_City']['id'];

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
                                    $city = wp_insert_term($item['University_City']['Name'], 'sit-city');
                                    update_term_meta($city['term_id'], 'zoho_city_id', $city_zoho_id);

                                    wp_set_object_terms($post_id, $city['term_id'], 'sit-city');
                                } else {
                                    wp_set_object_terms($post_id, $city->term_id, 'sit-city');
                                }

                                update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
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

                            $this->logger->log_message('info', 'Updating field: ' . $field['field_name']);

                            update_field($field['field_name'], $item[$field['field_name']], $post_id);

                            $this->logger->log_message('info', 'Field updated: ' . $field['field_name']);
                        } else {
                            $this->logger->log_message('info', 'Field not found: ' . $field['field_name']);
                        }
                    }

                } else {
                    $this->logger->log_message('info', 'University post not found, creating new post.');

                    $post_id = \SIT\Search\Modules\University::create_item($university['data'][0]);

                    $this->logger->log_message('info', 'University post created with ID: ' . $post_id);
                }

                return [
                    'status' => 'success',
                    'data' => $university['data'],
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log_message('error', 'Error: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $data;
    }
}
