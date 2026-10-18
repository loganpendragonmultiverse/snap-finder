<?php

return [
    'default_temperature' => (float) env('PENDRAGON_DEFAULT_TEMPERATURE', 0.9),
    'default_top_p' => (float) env('PENDRAGON_DEFAULT_TOP_P', 0.95),
    'default_max_tokens' => (int) env('PENDRAGON_DEFAULT_MAX_TOKENS', 900),
    'summary_message_limit' => (int) env('PENDRAGON_SUMMARY_MESSAGE_LIMIT', 12),
    'memory_extraction_limit' => (int) env('PENDRAGON_MEMORY_EXTRACTION_LIMIT', 3),
    'default_image_model' => env('PENDRAGON_DEFAULT_IMAGE_MODEL', 'chroma'),
    'models' => [
        'venice-uncensored-role-play' => [
            'label' => 'Venice Role Play Uncensored',
            'status' => 'active',
            'description' => 'Primary roleplay model for immersive character interaction.',
        ],
        'venice-uncensored' => [
            'label' => 'Venice Uncensored 1.1',
            'status' => 'active',
            'description' => 'General uncensored creative model with good flexibility.',
        ],
        'zai-org-glm-4.7' => [
            'label' => 'GLM 4.7',
            'status' => 'active',
            'description' => 'Large-model option for stronger general intelligence and planning.',
        ],
    ],
    'image_models' => [
        'chroma' => [
            'label' => 'Chroma',
            'status' => 'active',
            'description' => 'Best uncensored general image model.',
        ],
        'venice-sd35' => [
            'label' => 'Venice SD35',
            'status' => 'active',
            'description' => 'Best Venice-native realistic image model.',
        ],
        'lustify-v7' => [
            'label' => 'Lustify v7',
            'status' => 'active',
            'description' => 'NSFW-focused image model with strong erotic output.',
        ],
        'lustify-sdxl' => [
            'label' => 'Lustify SDXL',
            'status' => 'active',
            'description' => 'Alternative Lustify image model with SDXL-style output.',
        ],
    ],
];
