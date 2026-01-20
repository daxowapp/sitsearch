<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class UniversityDel extends Webhook
{
    private SIT_Logger $logger;

    /**
     * Handle the deletion of a university post via webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        try {
            $this->logger = SIT_Logger::get_instance();

            $university_id = $data['university_id'] ?? null;

            if (!$university_id) {
                $this->logger->log_message('error', 'Missing university_id.');
                return [
                    'status' => 'error',
                    'message' => 'Invalid or missing university_id.',
                ];
            }

            $post = Functions::getPostByMeta('zoho_account_id', $university_id, 'sit-university');

            if ($post) {
                $post_id = $post->ID;
                $this->logger->log_message('info', 'Deleting university post with ID: ' . $post_id);

                $deleted = wp_delete_post($post_id, true); // `true` for force delete

                if ($deleted === false) {
                    $this->logger->log_message('error', 'Failed to delete university post.');
                    return [
                        'status' => 'error',
                        'message' => 'Failed to delete university post.',
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'University post deleted successfully.',
                ];
            }

            $this->logger->log_message('info', 'No university post found for zoho_account_id: ' . $university_id);
            return [
                'status' => 'success',
                'message' => 'No matching university post found. Nothing to delete.',
            ];
        } catch (\Exception $e) {
            $this->logger->log_message('error', 'Exception occurred: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
}
