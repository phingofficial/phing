#!/usr/bin/env php
<?php

try {
    Phar::mapPhar('phing.phar');
    include 'phar://phing.phar/bin/phing.php';
} catch (PharException $e) {
    echo $e->getMessage();
    die('Cannot initialize Phar');
}

__HALT_COMPILER();
