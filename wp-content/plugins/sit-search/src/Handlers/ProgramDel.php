<?php

namespace SIT\Search\Handlers;

use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class ProgramDel extends Webhook
{
    private SIT_Logger $logger;

    /**
     * Handle the incoming data for the Program webhook
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        $this->logger = SIT_Logger::get_instance();

        // Get program_id from incoming data
        $program_id = $data['program_id'] ?? null;

        // Check if program_id is present in data
        if (!$program_id) {
            $this->logger->log_message('error', 'Program ID not provided in delete webhook.');
            return [
                'status' => 'error',
                'message' => 'Missing program_id in request.'
            ];
        }

        // Fetch the post associated with the program_id
        $post = Functions::getPostByMeta('zoho_product_id', $program_id, 'sit-program');

        // If the post is found, delete it
        if ($post) {
            $post_id = $post->ID;

            // Optional: Trash the post instead of permanently deleting it
            wp_trash_post($post_id); // To permanently delete, use wp_delete_post($post_id, true);

            $this->logger->log_message('info', "Program with Zoho ID {$program_id} (post ID {$post_id}) has been trashed.");

            return [
                'status' => 'success',
                'message' => "Program post deleted (ID: {$post_id})."
            ];
        } else {
            // Log if the post is not found
            $this->logger->log_message('warning', "Program post not found for Zoho ID: {$program_id}");

            return [
                'status' => 'error',
                'message' => 'Program post not found.'
            ];
        }
    }
}
