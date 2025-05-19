<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Atur opsi CORS untuk mengizinkan permintaan lintas domain dari frontend.
    | Jika frontend menggunakan credentials (cookie atau Authorization header),
    | harus mengisi 'allowed_origins' dengan domain frontend dan 
    | 'supports_credentials' harus true.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],  // Mengizinkan semua metode HTTP (GET, POST, dll)

    'allowed_origins' => ['http://localhost:3000'],  // Ganti sesuai URL frontend kamu

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // Mengizinkan semua header

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // Jika frontend pakai credentials, harus true

];
