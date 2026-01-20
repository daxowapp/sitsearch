<?php

namespace SIT\Search\Handlers;
use SIT\Search\Services\Functions;
use SIT\Search\Services\SIT_Logger;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;

class Language extends Webhook
{
    /**
     * Handle the incoming data for the university webhook
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
                    'message' => 'Invalid or missing city_id.',
                ];
            }

            $zoho = new Zoho();
            $language = $zoho->request(\SIT\Search\Modules\Language::$module . '/' . $language_id);

            $logger->log_message('info', 'Language data: ' . json_encode($language));

            if ($language['data']) {
                $term = Functions::getTermByMeta('zoho_language_id', $language_id, 'sit-language');

                if (!$term) {
                    $logger->log_message('info', 'Language term not found. Creating new term.');
                    $term_id = \SIT\Search\Modules\Language::create_item($language['data'][0]);
                } else {
                    $logger->log_message('info', 'Language term found with ID: ' . $term->term_id);
                    $term_id = $term->term_id;
                    $result = wp_update_term($term_id, 'sit-language', array(
                        'name' => $language['data'][0]['Name'],
                    ));
                }

                return [
                    'status' => 'success',
                    'message' => 'Language data received.',
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid or missing Language_id.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Language data received.',
        ];
    }
}