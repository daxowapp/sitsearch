<?php

namespace SIT\Search\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SIT_Logger
{
    private static ?SIT_Logger $instance = null;
    private Logger $log;
    private string $log_file;

    private function __construct()
    {
        $this->log_file = ABSPATH . 'wp-content/uploads/sit/sit-search.log';

        if (!file_exists(dirname($this->log_file))) {
            mkdir(dirname($this->log_file), 0755, true);
        }

        if (!file_exists($this->log_file)) {
            $file = fopen($this->log_file, 'w');
            fclose($file);
        }

        $this->log = new Logger('SIT-SEARCH');
        $this->log->pushHandler(new StreamHandler($this->log_file, Logger::DEBUG));
    }

    public static function get_instance(): SIT_Logger
    {
        if (self::$instance === null) {
            self::$instance = new SIT_Logger();
        }

        return self::$instance;
    }

    public function log_message(string $level, string $message): void
    {
        if (method_exists($this->log, $level)) {
            $this->log->{$level}($message);
        } else {
            $this->log->error("Invalid log level: {$level}");
        }
    }
}
