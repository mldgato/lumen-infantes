<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Correo Institucional Requerido
    |--------------------------------------------------------------------------
    | Si es true, el formulario de inscripción exige correo institucional
    | y contraseña propios. Si es false, se usa el correo personal como
    | correo institucional y la contraseña se fija en "password".
    */
    'require_institutional_email' => env('REQUIRE_INSTITUTIONAL_EMAIL', true),

];
