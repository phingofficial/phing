<?php

use Phing\Phing;

defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', __DIR__);
set_include_path(
    realpath(__DIR__ . '/../src') . PATH_SEPARATOR .
    realpath(__DIR__ . '/src') . PATH_SEPARATOR .
    get_include_path()  // trunk version of phing classes should take precedence
);

// Use composers autoload.php if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
}

Phing::setProperty('phing.home', realpath(__DIR__ . '/../'));
Phing::startup();

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
