<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials and endpoints for the third
    | party services this application talks to. All three feeds below are
    | public and unauthenticated.
    |
    */

    'velo_antwerp' => [
        'station_information_url' => env(
            'VELO_ANTWERP_STATION_INFORMATION_URL',
            'https://gbfs.smartbike.com/antwerp/1.0/en/station_information.json',
        ),
    ],

    'osrm' => [
        'base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
    ],

    'open_meteo' => [
        'archive_url' => env('OPEN_METEO_ARCHIVE_URL', 'https://archive-api.open-meteo.com/v1/archive'),
    ],

    'rides' => [
        'json_path' => env('RIDES_JSON_PATH', 'data/rides.json'),
    ],

];
