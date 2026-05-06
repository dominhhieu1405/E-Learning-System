<?php

use Services\Helper;

const ROOT_PATH = __DIR__ . '/..';

require_once __DIR__ . '/vendor/autoload.php';
if (true) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

session_start();

Helper::load();
(new Services\Router)->start();
