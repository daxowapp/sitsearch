<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class DegreeDel extends Webhook
{
    /**
     * Handle the incoming data for the degree delete webhook
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

            // Get the term by the Zoho degree ID
            $term = Functions::getTermByMeta('zoho_degree_id', $degree_id, 'sit-degree');

            if ($term) {
                $term_id = $term->term_id;

                // Log deletion
                $logger->log_message('info', 'Deleting degree term with ID: ' . $term_id);

                // Delete the term
                $deleted = wp_delete_term($term_id, 'sit-degree');

                if (is_wp_error($deleted)) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete degree: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Degree term deleted successfully.',
                ];
            } else {
                $logger->log_message('info', 'Degree term not found for deletion.');
                return [
                    'status' => 'success',
                    'message' => 'No matching degree term found. Nothing to delete.',
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
