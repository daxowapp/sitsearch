<?php

namespace SIT\Search\Services;

class Webhook
{
    private array $handlers = [];
    private SIT_Logger $logger;

    public function __construct()
    {
        $this->logger = SIT_Logger::get_instance();

        add_filter('query_vars', function ($vars) {
            $vars[] = 'wh_api';
            return $vars;
        });

        add_action('init', [$this, 'addWebhookRewriteRule']);
        add_action('template_redirect', [$this, 'processWebhookRequest']);
    }

    public function registerHandlers($handlers): void
    {
        foreach ($handlers as $endpoint => $className) {
            $this->handlers[$endpoint] = $className;
        }
    }

    public function addWebhookRewriteRule()
    {
        add_rewrite_rule(
            '^webhook/([^/]+)/?$',
            'index.php?wh_api=$matches[1]',
            'top'
        );
        flush_rewrite_rules();
    }

    public function processWebhookRequest()
    {
        $requestedEndpoint = get_query_var('wh_api');
        $this->logger->log_message('info', 'Requested endpoint: ' . $requestedEndpoint);

        if ($requestedEndpoint) {

            $data = $_POST;

            $this->logger->log_message('info', 'Received data: ' . json_encode($data));

            $response = $this->dispatch($requestedEndpoint, $data ? array_merge($data, $_GET) : $_GET);

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    private function dispatch(string $endpoint, array $data): array
    {
        if (!isset($this->handlers[$endpoint])) {
            $this->logger->log_message('error', 'No handler registered for endpoint: ' . $endpoint);
            return [
                'status' => 'error',
                'message' => 'No handler registered for this endpoint',
            ];
        }

        $className = $this->handlers[$endpoint];

        if (!class_exists($className)) {
            $this->logger->log_message('error', 'Handler class does not exist for endpoint: ' . $endpoint);
            return [
                'status' => 'error',
                'message' => 'Handler class does not exist',
            ];
        }

        $handler = new $className();
        if (!$handler instanceof Webhook) {
            $this->logger->log_message('error', 'Handler for endpoint does not extend Webhook class');
            return [
                'status' => 'error',
                'message' => 'Handler must extend Webhook',
            ];
        }

        $this->logger->log_message('info', 'Dispatching request to handler for endpoint: ' . $endpoint);
        return $handler->handle($data);
    }
}
