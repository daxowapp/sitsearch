<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class CountryDel extends Webhook
{
    /**
     * Handle the deletion of a country term via webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        try {
            $logger = SIT_Logger::get_instance();

            $country_id = $data['country_id'] ?? null;

            if (!$country_id) {
                $logger->log_message('error', 'Missing country_id.');
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing country_id.',
                ];
            }

            $term = Functions::getTermByMeta('zoho_country_id', $country_id, 'sit-country');

            if ($term) {
                $logger->log_message('info', 'Deleting country term with ID: ' . $term->term_id);

                $deleted = wp_delete_term($term->term_id, 'sit-country');

                if (is_wp_error($deleted)) {
                    $logger->log_message('error', 'Failed to delete country term: ' . $deleted->get_error_message());
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete country term: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Country term deleted successfully.',
                ];
            }

            $logger->log_message('info', 'No country term found with zoho_country_id: ' . $country_id);

            return [
                'status' => 'success',
                'message' => 'No matching country term found. Nothing to delete.',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }
}
