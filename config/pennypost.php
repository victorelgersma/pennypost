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
    | Letters are delivered every Friday at noon UTC. To catch a given
    | week's batch, a letter must be sealed this many days before that
    | Friday. A sealed letter can also be unsealed back to a draft any
    | time up until this same cutoff. See Message::nextBatchFor() and
    | Message::unsealDeadline().
    |
    */

    'cutoff_days_before_batch' => env('PENNYPOST_CUTOFF_DAYS', 3),

];