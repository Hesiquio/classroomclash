<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | La mayoría de los frameworks MVC cargan las "vistas" desde un directorio. Sin
    | embargo, Laravel permite registrar múltiples ubicaciones que deben ser
    | examinadas para encontrar vistas, lo que permite la modularización.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Esta opción determina dónde se almacenarán todos los archivos de plantillas
    | de Blade compilados para su posterior uso. Este directorio debe tener
    | permisos de escritura para que el framework funcione correctamente.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];