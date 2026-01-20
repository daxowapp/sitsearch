<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;

class CampusDel extends Webhook
{
    private SIT_Logger $logger;

    /**
     * Handle the deletion of a campus post via webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        $this->logger = SIT_Logger::get_instance();

        $campus_id = $data['campus_id'] ?? null;

        if (!$campus_id) {
            $this->logger->log_message('error', 'Missing campus_id.');
            return [
                'status' => 'error',
                'message' => 'Invalid or missing campus_id.',
            ];
        }

        $post = Functions::getPostByMeta('zoho_campus_id', $campus_id, 'sit-campus');

        if ($post) {
            $post_id = $post->ID;
            $this->logger->log_message('info', 'Deleting campus post with ID: ' . $post_id);

            $deleted = wp_delete_post($post_id, true); // true = force delete

            if (!$deleted) {
                $this->logger->log_message('error', 'Failed to delete campus post with ID: ' . $post_id);
                return [
                    'status' => 'error',
                    'message' => 'Failed to delete campus post.',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Campus post deleted successfully.',
            ];
        }

        $this->logger->log_message('info', 'No campus post found for Zoho ID: ' . $campus_id);
        return [
            'status' => 'success',
            'message' => 'No matching campus post found. Nothing to delete.',
        ];
    }
}
