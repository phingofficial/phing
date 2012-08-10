<?php
defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', __DIR__);
defined('PHING_TEST_TMP') || define('PHING_TEST_TMP', __DIR__.'/tmp');

set_include_path(
    realpath(PHING_TEST_BASE . '/../classes') . PATH_SEPARATOR . 
    realpath(PHING_TEST_BASE . '/classes') . PATH_SEPARATOR .
    get_include_path()  // trunk version of phing classes should take precedence
);

require_once('phing/BuildFileTest.php');
require_once('phing/Phing.php');

Phing::startup();
Phing::setProperty('PHING_TEST_TMP', PHING_TEST_TMP);
