<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class FacultyDel extends Webhook
{
    /**
     * Handle the incoming data for the faculty delete webhook
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
                    'message' => 'Invalid or missing faculty_id.',
                ];
            }

            // Get the term by the Zoho faculty ID
            $term = Functions::getTermByMeta('zoho_faculty_id', $faculty_id, 'sit-faculty');

            if ($term) {
                $term_id = $term->term_id;

                $logger->log_message('info', 'Deleting faculty term with ID: ' . $term_id);

                $deleted = wp_delete_term($term_id, 'sit-faculty');

                if (is_wp_error($deleted)) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete faculty: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Faculty term deleted successfully.',
                ];
            } else {
                $logger->log_message('info', 'Faculty term not found for deletion.');
                return [
                    'status' => 'success',
                    'message' => 'No matching faculty term found. Nothing to delete.',
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
