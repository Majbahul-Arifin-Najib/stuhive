<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Study Group Chat Window
    |--------------------------------------------------------------------------
    |
    | A study group chat box opens when the first student joins and stays
    | available for this many hours. Once the window closes the messages are
    | purged from the database by the `stuhive:purge-expired-chats` command.
    |
    */

    'study_group_chat_hours' => (int) env('STUHIVE_CHAT_HOURS', 1),

    /*
    |--------------------------------------------------------------------------
    | Low Balance Threshold
    |--------------------------------------------------------------------------
    |
    | Students are warned with a "low balance" message once their spending for
    | the month reaches this fraction of their declared monthly budget.
    |
    */

    'low_balance_threshold' => (float) env('STUHIVE_LOW_BALANCE_THRESHOLD', 0.8),

    /*
    |--------------------------------------------------------------------------
    | Reward Points
    |--------------------------------------------------------------------------
    |
    | Points awarded to a user for contributing to the hive.
    |
    */

    'points' => [
        'post' => 5,
        'comment' => 2,
        'found_item' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'disk' => env('STUHIVE_UPLOAD_DISK', 'public'),
        'max_image_kb' => 4096,
        'max_pdf_kb' => 20480,
    ],

];
