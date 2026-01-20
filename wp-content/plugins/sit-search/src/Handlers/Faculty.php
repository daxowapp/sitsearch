<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Faculty extends Webhook
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

            $faculty_id = $data['faculty_id'];

            if (!$faculty_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing city_id.',
                ];
            }

            $zoho = new Zoho();
            $faculty = $zoho->request(\SIT\Search\Modules\Faculty::$module . '/' . $faculty_id);

            $logger->log_message('info', 'Faculty data: ' . json_encode($faculty));

            if ($faculty['data']) {
                $term = Functions::getTermByMeta('zoho_faculty_id', $faculty_id, 'sit-faculty');

                if (!$term) {
                    $logger->log_message('info', 'Faculty term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\Faculty::create_item($faculty['data'][0]);
                } else {
                    $logger->log_message('info', 'Faculty term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-faculty', array(
                        'name' => $faculty['data'][0]['Name'],
                    ));
                }

                return [
                    'status' => 'success',
                    'message' => 'Faculty data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing Faculty_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Faculty data received.',
        ];
    }
}