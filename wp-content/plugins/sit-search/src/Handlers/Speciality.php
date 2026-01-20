<?php

namespace SIT\Search\Handlers;
use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Speciality extends Webhook
{
    /**
     * Handle the incoming data for the university webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        try {
            $logger = SIT_Logger::get_instance();

            $speciality_id = $data['speciality_id'];

            if (!$speciality_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing speciality_id.',
                ];
            }

            $zoho = new Zoho();
            $speciality = $zoho->request(\SIT\Search\Modules\Speciality::$module . '/' . $speciality_id);

            $logger->log_message('info', 'Speciality data: ' . json_encode($speciality));

            if ($speciality['data']) {
                $term = Functions::getTermByMeta('zoho_speciality_id', $speciality_id, 'sit-speciality');

                if (!$term) {
                    $logger->log_message('info', 'Speciality term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\Speciality::create_item($speciality['data'][0]);
                } else {
                    $logger->log_message('info', 'Speciality term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-speciality', array(
                        'name' => $speciality['data'][0]['Name'],
                    ));
                }
                $ff=$speciality['data'][0];
                if (!empty($ff['Active'])) {
                    $Active=$ff['Active'];
                    if($Active == '1'){
                        update_term_meta($term_id ,'active_in_search',$Active);
                    }
                    else{
                        update_term_meta($term_id ,'active_in_search','');
                    }
                }
                else{
                    update_term_meta($term_id ,'active_in_search','');
                }
                if (!empty($ff['Speciality_main_image'])) {
                    $file_id_api=$ff['Speciality_main_image'][0]['file_Id'];
                    $file_id=get_term_meta($term_id ,'file_id',true);
                    if($file_id != $file_id_api){
                        update_term_meta($term_id ,'file_id',$ff['Speciality_main_image'][0]['file_Id']);
                        $att_id=$ff['Speciality_main_image'][0]['attachment_Id'];
                        $file_name=$ff['Speciality_main_image'][0]['file_Name'];
                        $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);
                        if ($image_path) {
                            update_term_meta($term_id ,'spec_image',$image_path);
                        } else {
                            update_term_meta($term_id ,'spec_image','');
                        }
                    }
                }
                else{
                    update_term_meta($term_id ,'spec_image','');
                }

                return [
                    'status' => 'success',
                    'message' => 'Speciality data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing Speciality_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Country data received.',
        ];
    }
}