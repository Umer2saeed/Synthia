<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Intervention Image Driver
    |--------------------------------------------------------------------------
    | v4 supports two drivers: 'gd' and 'imagick'
    | Since you only have GD available on Laragon, we use 'gd'.
    | On production DigitalOcean you can switch to 'imagick' after:
    |   sudo apt-get install php-imagick
    */
    'driver' => env('IMAGE_DRIVER', 'gd'),
];
