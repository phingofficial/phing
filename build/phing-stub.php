#!/usr/bin/env php
<?php

if (DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
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
