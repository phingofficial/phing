<?php

if (DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
    array_push($argv, '-logger');
    array_push($argv, 'phing.listener.AnsiColorLogger');
    $argc+=2;
}
$argc++;

include 'phar://phing.phar/bin/phing.php';

__HALT_COMPILER();