<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Country extends Webhook
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

            $country_id = $data['country_id'];

            if (!$country_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing country_id.',
                ];
            }

            $zoho = new Zoho();
            $country = $zoho->request(\SIT\Search\Modules\Country::$module . '/' . $country_id);

            $logger->log_message('info', 'Country data: ' . json_encode($country));

            if ($country['data']) {
                $term = Functions::getTermByMeta('zoho_country_id', $country_id, 'sit-country');

                if (!$term) {
                    $logger->log_message('info', 'Country term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\Country::create_item($country['data'][0]);
                } else {
                    $logger->log_message('info', 'Country term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-country', array(
                        'name' => $country['data'][0]['Name'],
                    ));
                    update_term_meta($term_id, 'active_on_university',$country['data'][0]['Active_on_University']);
                }

                return [
                    'status' => 'success',
                    'message' => 'Country data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing university_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Country data received.',
        ];
    }
}