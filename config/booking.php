<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking grid
    |--------------------------------------------------------------------------
    */
    'slot_interval_minutes' => 15,

    /*
    |--------------------------------------------------------------------------
    | Commission
    |--------------------------------------------------------------------------
    |
    | The rate is snapshotted on each booking so future policy changes do not
    | rewrite historical reservations.
    |
    | During the current startup phase the commission allocation belongs to
    | the salon. Payment settlement is intentionally kept outside the booking
    | engine until the payment flow is introduced.
    |--------------------------------------------------------------------------
    */
    'commission' => [
        'rate' => 10.00,
        'recipient' => 'salon',
        'status' => 'pending',
    ],
];
