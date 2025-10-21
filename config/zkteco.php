<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco Device Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ZKTeco biometric attendance devices
    |
    */

    'device_ip' => env('ZKTECO_DEVICE_IP', '192.168.1.201'),
    'device_port' => env('ZKTECO_DEVICE_PORT', 4370),
    'sync_interval' => env('ZKTECO_SYNC_INTERVAL', 5), // minutes

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    */

    'timeout' => 10, // seconds
    'retry_attempts' => 3,
    'retry_delay' => 5, // seconds

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    */

    'auto_sync' => true,
    'sync_on_startup' => false,
    'keep_device_records' => true, // Keep records on device after sync
];
