<?php

namespace SIT\Search\Handlers;
use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class SpecialityDel extends Webhook
{
    /**
     * Handle the incoming data for the speciality delete webhook
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

            $term = Functions::getTermByMeta('zoho_speciality_id', $speciality_id, 'sit-speciality');

            if ($term) {
                $term_id = $term->term_id;

                // Log term found
                $logger->log_message('info', 'Deleting speciality term with ID: ' . $term_id);

                $deleted = wp_delete_term($term_id, 'sit-speciality');

                if (is_wp_error($deleted)) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete speciality: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Speciality term deleted successfully.',
                ];
            } else {
                $logger->log_message('info', 'Speciality term not found for deletion.');
                return [
                    'status' => 'success',
                    'message' => 'No matching speciality term found. Nothing to delete.',
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
