<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class LanguageDel extends Webhook
{
    /**
     * Handle the incoming data for the language delete webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        try {
            $logger = SIT_Logger::get_instance();

            $language_id = $data['language_id'];

            if (!$language_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing language_id.',
                ];
            }

            // Get the term using the Zoho language ID
            $term = Functions::getTermByMeta('zoho_language_id', $language_id, 'sit-language');

            if ($term) {
                $term_id = $term->term_id;

                $logger->log_message('info', 'Deleting language term with ID: ' . $term_id);

                $deleted = wp_delete_term($term_id, 'sit-language');

                if (is_wp_error($deleted)) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete language: ' . $deleted->get_error_message(),
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Language term deleted successfully.',
                ];
            } else {
                $logger->log_message('info', 'Language term not found for deletion.');
                return [
                    'status' => 'success',
                    'message' => 'No matching language term found. Nothing to delete.',
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
