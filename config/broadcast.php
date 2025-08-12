<?php

return [

    // Grupo para tu websocket (Reverb)
    'ws' => [
        'pusher_app_key'   => env('REVERB_APP_KEY'),
        'host'             => env('REVERB_HOST', 'localhost'),
        'port'             => (int) env('REVERB_PORT', 8080),
        'scheme'           => env('REVERB_SCHEME', 'http'),
        'server_path'      => env('REVERB_SERVER_PATH', ''),

        // por si quieres también el app_id/secret:
        'app_id'           => env('REVERB_APP_ID'),
        'app_secret'       => env('REVERB_APP_SECRET'),
        'driver'           => env('BROADCAST_DRIVER', 'reverb'),
    ],

];
