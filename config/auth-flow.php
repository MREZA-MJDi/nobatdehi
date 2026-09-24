<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration OTP
    |--------------------------------------------------------------------------
    |
    | OTP remains enabled by default in production. Local development can
    | bypass the SMS verification step until the real SMS provider is ready.
    |
    */

    'registration_otp_required' =>
        env(
            'AUTH_REGISTRATION_OTP_REQUIRED',
            app()->environment('production')
        ),

];
