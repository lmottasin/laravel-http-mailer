<?php

declare(strict_types=1);

/*
 * Merged into the app's config/services.php. Any entry the app already defines wins.
 */
return [

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
        'region' => env('SENDGRID_REGION'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
    ],

];
