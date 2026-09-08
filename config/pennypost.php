<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Letter Length
    |--------------------------------------------------------------------------
    |
    | The maximum number of characters a letter body may contain. Enforced
    | both server-side (MessageController::save()) and as the textarea's
    | maxlength attribute in messages/create.blade.php.
    |
    */

    'max_letter_length' => env('PENNYPOST_MAX_LETTER_LENGTH', 20000),


    /*
    |--------------------------------------------------------------------------
    | Batch Cutoff
    |--------------------------------------------------------------------------
    |
    |
    */

   'cutoff_days_before_batch' => env('PENNYPOST_CUTOFF_DAYS', 2),

    /*
    |--------------------------------------------------------------------------
    | Short Profile URL
    |--------------------------------------------------------------------------
    |
    | The short redirect domain (see the pp.vjbe.net Caddy block) that
    | bounces /{username} and /@{username} to the real public profile at
    | /u/{username}. Used only for what "Copy public profile" hands the
    | user — the "View public profile" link and the canonical page itself
    | are unaffected. If unset, copying falls back to the full URL.
    |
    */

    'short_profile_domain' => env('PENNYPOST_SHORT_PROFILE_DOMAIN'),

];
