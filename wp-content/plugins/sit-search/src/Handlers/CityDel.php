<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class CityDel extends Webhook
{
    /**
     * Handle the incoming data for the city delete webhook
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

            // Get the term by the Zoho city ID
            $term = Functions::getTermByMeta('zoho_city_id', $city_id, 'sit-city');

            if ($term) {
                $term_id = $term->term_id;

                // Log deletion
                $logger->log_message('info', 'Deleting city term with ID: ' . $term_id);

                // Delete the term
                $deleted = wp_delete_term($term_id, 'sit-city');

                if (is_wp_error($deleted)) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete city: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'City term deleted successfully.',
                ];
            } else {
                $logger->log_message('info', 'City term not found for deletion.');
                return [
                    'status' => 'success',
                    'message' => 'No matching city term found. Nothing to delete.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error processing deletion: ' . $e->getMessage(),
            ];
        }
    }
}
