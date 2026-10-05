<?php

/*
 | Solo se sobreescriben las claves necesarias; el resto se toma de
 | vendor/spatie/laravel-medialibrary/config/media-library.php
 |
 | Ojo: upload_max_filesize y post_max_size de php.ini deben ser >= a este valor.
 */
return [
    // Planos y memorias de cálculo pueden ser pesados (default del paquete: 10 MB)
    'max_file_size' => 1024 * 1024 * (int) env('MEDIA_MAX_FILE_SIZE_MB', 40),
];
