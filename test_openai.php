<?php
require_once('wp-load.php');
$engine = new SIT_Engine();
$response = $engine->generate_chat_question('Test', 3, []);
print_r($response);
echo "OpenAI enabled? " . ($engine->is_openai_enabled() ? 'Yes' : 'No') . "\n";
