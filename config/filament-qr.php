<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | Default values applied across Filament table columns, infolist entries,
    | and actions when not overridden via fluent component setters.
    |
    */
    'defaults' => [
        'size' => 160,
        'margin' => 1,
        'format' => 'svg', // svg, png, webp
        'error_correction' => 'M', // L, M, Q, H
        'color' => '#000000',
        'background_color' => '#ffffff',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preview Modal Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for the zoom/preview modal opened on click.
    |
    */
    'preview' => [
        'modal_width' => 'sm',
        'render_size' => 320,
    ],
];
