<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Phing\Phing;

defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', __DIR__);
defined('PHING_TEST_TMP') || define('PHING_TEST_TMP', __DIR__.'/tmp');

set_include_path(
    realpath(__DIR__ . '/../classes') . PATH_SEPARATOR . 
    realpath(__DIR__ . '/classes') . PATH_SEPARATOR .
    get_include_path()  // trunk version of phing classes should take precedence
);

Phing::setProperty('phing.home', realpath(__DIR__ . '/../'));
Phing::setProperty('phing.test_tmp', PHING_TEST_TMP);
Phing::setProperty('tmp.dir', PHING_TEST_TMP); // already used by some existing tests
Phing::startup();

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
