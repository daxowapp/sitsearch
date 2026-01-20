<?php

namespace SIT\Search\Handlers;
use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Degree extends Webhook
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

            $degree_id = $data['degree_id'];

            if (!$degree_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing degree_id.',
                ];
            }

            $zoho = new Zoho();
            $degree = $zoho->request(\SIT\Search\Modules\Degree::$module . '/' . $degree_id);

            $logger->log_message('info', 'Degree data: ' . json_encode($degree));

            if ($degree['data']) {
                $term = Functions::getTermByMeta('zoho_degree_id', $degree_id, 'sit-degree');

                if (!$term) {
                    $logger->log_message('info', 'Degree term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\Degree::create_item($degree['data'][0]);
                } else {
                    $logger->log_message('info', 'Degree term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-degree', array(
                        'name' => $degree['data'][0]['Name'],
                    ));
                }
                $fields = \SIT\Search\Modules\Degree::get_fields();
                $actv=$degree['data'][0]['Active_In_Search'];
                if(!empty($actv) && $actv == '1'){
                    update_term_meta($term_id, 'active_in_search', $actv);
                }
                else{
                    update_term_meta($term_id, 'active_in_search', '');
                }
                return [
                    'status' => 'success',
                    'message' => 'Degree data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing Degree_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Degree data received.',
        ];
    }
}