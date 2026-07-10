<?php

defined('ABSPATH') || exit;

spl_autoload_register(function ($class) {

    if (strpos($class, 'CWS\\R2Media\\') !== 0) {
        return;
    }

    $class = str_replace('CWS\\R2Media\\', '', $class);

    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $file = CWS_R2_MEDIA_PATH .
        'includes/' .
        $class .
        '.php';

    if (file_exists($file)) {
        require_once $file;
    }

});

CWS\R2Media\Core\Plugin::boot();