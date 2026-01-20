<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class City extends Webhook
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

            $city_id = $data['city_id'];

            if (!$city_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing city_id.',
                ];
            }

            $zoho = new Zoho();
            $city = $zoho->request(\SIT\Search\Modules\City::$module . '/' . $city_id);

            $logger->log_message('info', 'City data: ' . json_encode($city));

            if ($city['data']) {
                $term = Functions::getTermByMeta('zoho_city_id', $city_id, 'sit-city');

                if (!$term) {
                    $logger->log_message('info', 'City term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\City::create_item($city['data'][0]);
                } else {
                    $logger->log_message('info', 'City term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-city', array(
                        'name' => $city['data'][0]['Name'],
                    ));
                }

                return [
                    'status' => 'success',
                    'message' => 'City data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing country_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'City data received.',
        ];
    }
}