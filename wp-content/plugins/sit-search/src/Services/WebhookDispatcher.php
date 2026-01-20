<?php

namespace SIT\Search\Services;

class WebhookDispatcher
{
    /**
     * Registry of webhook handlers
     *
     * @var array
     */
    private array $handlers = [];

    /**
     * Register a webhook handler for a given endpoint
     *
     * @param string $endpoint
     * @param string $className
     * @return void
     */
    public function registerHandler(string $endpoint, string $className): void
    {
        $this->handlers[$endpoint] = $className;
    }

    /**
     * Dispatch the request to the appropriate handler
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function dispatch(string $endpoint, array $data): array
    {
        // Check if a handler exists for the requested endpoint
        if (!isset($this->handlers[$endpoint])) {
            return [
                'status' => 'error',
                'message' => 'No handler registered for this endpoint',
            ];
        }

        // Dynamically create an instance of the handler class
        $className = $this->handlers[$endpoint];

        // Check if the class exists
        if (!class_exists($className)) {
            return [
                'status' => 'error',
                'message' => 'Handler class does not exist',
            ];
        }

        // Instantiate the handler and ensure it extends Webhook
        $handler = new $className();
        if (!$handler instanceof Webhook) {
            return [
                'status' => 'error',
                'message' => 'Handler must extend Webhook',
            ];
        }

        // Call the handler's handle method to process the request
        return $handler->handle($data);
    }
}
