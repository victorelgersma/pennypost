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

];
