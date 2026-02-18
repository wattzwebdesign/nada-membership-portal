<?php

return [

    'enabled' => env('CHATBOT_ENABLED', true),

    'api_key' => env('ANTHROPIC_API_KEY'),

    'model' => env('CHATBOT_MODEL', 'claude-sonnet-4-20250514'),

    'max_tokens' => 1024,

    'max_messages' => 20,

    'rate_limit' => 30, // requests per hour per IP

];
