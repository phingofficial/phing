<?php

require_once '../vendor/autoload.php';

use Phing\Phing;

defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', dirname(__FILE__));
set_include_path(
    realpath(dirname(__FILE__) . '/../classes') . PATH_SEPARATOR . 
    realpath(dirname(__FILE__) . '/classes') . PATH_SEPARATOR .
    get_include_path()  // trunk version of phing classes should take precedence
);

Phing::setProperty('phing.home', realpath(dirname(__FILE__) . '/../'));
Phing::startup();

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
