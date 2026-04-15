<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans Payment Gateway.
    | Dapatkan credentials di: https://dashboard.sandbox.midtrans.com/settings/config_info
    |
    */

    // Server Key dari Midtrans Dashboard
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    
    // Client Key dari Midtrans Dashboard
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    
    // Merchant ID dari Midtrans Dashboard
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    
    // Environment: 'sandbox' atau 'production'
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    
    // Enable/disable sanitization
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    
    // Enable/disable 3D Secure
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Payment expiry window configuration
    // Supported units by Midtrans: second, minute, hour, day
    'payment_expiry_duration' => (int) env('MIDTRANS_PAYMENT_EXPIRY_DURATION', 15),
    'payment_expiry_unit' => env('MIDTRANS_PAYMENT_EXPIRY_UNIT', 'minute'),
    
    // Snap API URL
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false) 
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
    
    // Base API URL    
    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',
];
