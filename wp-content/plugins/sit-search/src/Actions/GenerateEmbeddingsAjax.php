<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;
use SIT\Search\Services\ProgramEmbeddings;

class GenerateEmbeddingsAjax extends Hook
{
    public static array $hooks = ['wp_ajax_generate_embeddings'];

    public static int $priority = 10;

    public function __invoke()
    {
        if (!current_user_can('manage_options')) {
            $this->send_progress('Error: Insufficient permissions.');
            exit;
        }

        check_ajax_referer('generate_embeddings_nonce', 'nonce');

        // Suppress normal output
        @ob_end_clean();
        header('Content-Type: text/plain');

        $this->send_progress('Starting embedding generation...');

        try {
            $openai = new \SIT\Search\Services\SIT_OpenAI_Service();
            $this->send_progress('OpenAI service instantiated.');

            $embeddings = new \SIT\Search\Services\ProgramEmbeddings($openai);
            $this->send_progress('ProgramEmbeddings service instantiated.');

            // The generateAllEmbeddings method will now need to accept a callback
            $results = $embeddings->generateAllEmbeddings(function($message) {
                $this->send_progress($message);
            });

            $this->send_progress('Generation process completed.');
            $this->send_progress(json_encode(['status' => 'complete', 'results' => $results]));

        } catch (\Throwable $e) {
            $errorMessage = 'A critical error occurred: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            $this->send_progress($errorMessage);
            error_log($errorMessage);
        }

        exit;
    }

    private function send_progress($message)
    {
        echo esc_html($message) . "---PROGRESS---";
        @ob_flush();
        flush();
    }
}
