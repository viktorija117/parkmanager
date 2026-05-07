<?php
return [
    'reservation_window_days' => env('PARKING_WINDOW_DAYS', 14),

    'cancellation_cutoff_hour' => env('PARKING_CANCEL_HOUR', 23),

    'release_day' => env('PARKING_RELEASE_DAY', 'monday'),

    'locales' => ['sr', 'en'],
];
