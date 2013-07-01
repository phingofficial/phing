#!/usr/bin/env php
<?php

if (DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
    array_push($argv, '-logger');
    array_push($argv, 'phing.listener.AnsiColorLogger');
    $argc+=2;
}
$argc++;

include 'phar://' . __FILE__ . '/bin/phing.php';

__HALT_COMPILER();
