<?php

/*function phing_phpunit_test_autoload($className) {
    $className = (string) str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require_once(dirname(__FILE__) . '/src/' . $className . '.php');
}*/

//spl_autoload_register('phing_phpunit_test_autoload');

spl_autoload_register(function($className) {
    $className = (string) str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require_once(dirname(__FILE__) . '/src/' . $className . '.php');
});

file_put_contents('/tmp/henk', var_export(spl_autoload_functions(), true));