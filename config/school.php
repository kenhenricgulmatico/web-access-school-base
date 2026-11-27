<?php

/**
 * ✅ School details shown on the sign-in page.
 *    Set them in .env (no code change needed). Empty = that line is simply not shown.
 *
 *    SCHOOL_MOTTO="Faith, Excellence and Service"
 *    SCHOOL_ESTABLISHED=2011
 *    SCHOOL_HELP_CONTACT="MIS Office, 2nd floor Admin Bldg · local 123"
 */
return [
    'name'         => env('SCHOOL_NAME', 'Colegio De Sta. Ana de Victorias, Inc.'),
    'short_name'   => env('SCHOOL_SHORT_NAME', 'CSAV'),
    'motto'        => env('SCHOOL_MOTTO'),
    'established'  => env('SCHOOL_ESTABLISHED'),
    'help_contact' => env('SCHOOL_HELP_CONTACT'),
    'email_domain' => '@csav.edu.ph',
];
