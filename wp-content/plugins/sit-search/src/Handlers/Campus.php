<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;


class Campus extends Webhook
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

        $campus_id = $data['campus_id'];

        if (!$campus_id) {

            $this->logger->log_message('info', 'Campus Id not found');

            return [
                'status' => 'error',
                'message' => 'Invalid or missing $program_id.',
            ];
        }

        $zoho = new Zoho();

        $campus = $zoho->request('Campus/' . $campus_id);
        $this->logger->log_message('info', 'Campus data: ' . json_encode($campus));

        if ($campus['data']) {
            $post = Functions::getPostByMeta('zoho_campus_id', $campus_id, 'sit-campus');

            if ($post) {
                $this->logger->log_message('info', 'Campus post found with ID: ' . $post->ID);
                $post_id = $post->ID;

                $fields = \SIT\Search\Modules\Campus::get_fields();

                $item = $campus['data'][0];
                $this->logger->log_message('info', print_r($fields, true));

                foreach ($fields as $field) {
                    Functions::create_field_if_needed($field, 'group_67bf030cdb4fd');
                    if (isset($item[$field['field_name']])) {

                        if ($field['field_name'] == 'Name') {
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

                        if ($field['field_name'] == 'Faculty') {

                            $facultydata = $zoho->request('Campus/' . $campus_id.'/6421426000013599889');
                            if ($facultydata['data']) {
                                $faculty_ids = [];
                                foreach($facultydata['data'] as $val){
                                    $faid=$val['Faculty']['id'];
                                    $faname=$val['Faculty']['name'];
                                    $this->logger->log_message('info', 'Zoho ID: ' . $faid);
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
                        }


                        $this->logger->log_message('info', 'Updating field: ' . $field['field_name']);

                        update_field($field['field_name'], $item[$field['field_name']], $post_id);

                        $this->logger->log_message('info', 'Field updated: ' . $field['field_name']);
                    } else {
                        $this->logger->log_message('info', 'Field not found: ' . $field['field_name']);
                    }
                }

            } else {
                $this->logger->log_message('info', 'Campus post not found, creating new post.');

                $post_id = \SIT\Search\Modules\Campus::create_item($campus['data'][0]);

                $this->logger->log_message('info', 'Campus post created with ID: ' . $post_id);
            }

            return [
                'status' => 'success',
                'data' => $campus['data'],
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Invalid or missing data121.',
        ];
    }
}