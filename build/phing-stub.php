#!/usr/bin/env php
<?php

/**
 * Code from Symfony/Component/Console/Output/StreamOutput.php
 */
function hasColorSupport() {
    if (DIRECTORY_SEPARATOR == '\\') {
        return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
    }
    return function_exists('posix_isatty') && @posix_isatty(STDOUT);
}

if (hasColorSupport()) {
    array_push($argv, '-logger');
    array_push($argv, 'phing.listener.AnsiColorLogger');
    $argc+=2;
}
$argc++;

try {
    Phar::mapPhar('phing.phar');
    include 'phar://phing.phar/bin/phing.php';
} catch (PharException $e) {
    echo $e->getMessage();
    die('Cannot initialize Phar');
}

__HALT_COMPILER();
