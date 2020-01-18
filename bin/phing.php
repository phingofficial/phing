<?php

/**
 * This is the Phing command line launcher. It starts up the system evironment
 * tests for all important paths and properties and kicks of the main command-
 * line entry point of phing located in phing.Phing
 */

// check required PHP version
if (version_compare('7.1.0', PHP_VERSION, '>')) {
    fwrite(
        STDERR,
        sprintf(
            'This version of Phing is supported on PHP 7.1, PHP 7.2, PHP 7.3 and PHP 7.4.' . PHP_EOL .
            'You are using PHP %s (%s).' . PHP_EOL,
            PHP_VERSION,
            PHP_BINARY
        )
    );

    die(1);
}

// set timezone
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

// search autoload file
foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('PHING_COMPOSER_INSTALL', $file);

        break;
    }
}

unset($file);

// check that autoload file was found
if (!defined('PHING_COMPOSER_INSTALL')) {
    fwrite(
        STDERR,
        'You need to set up the project dependencies using Composer:' . PHP_EOL . PHP_EOL .
        '    composer install' . PHP_EOL . PHP_EOL .
        'You can learn all about Composer on https://getcomposer.org/.' . PHP_EOL
    );

    die(1);
}

require PHING_COMPOSER_INSTALL;

/**
 * Code from Symfony/Component/Console/Output/StreamOutput.php
 */
function hasColorSupport()
{
    if (DIRECTORY_SEPARATOR == '\\') {
        return 0 >= version_compare('10.0.10586', PHP_WINDOWS_VERSION_MAJOR.'.'.PHP_WINDOWS_VERSION_MINOR.'.'.PHP_WINDOWS_VERSION_BUILD)
            || false !== getenv('ANSICON')
            || 'ON' === getenv('ConEmuANSI')
            || 'xterm' === getenv('TERM');
    }

    return function_exists('posix_isatty') && @posix_isatty(STDOUT);
}

// Grab and clean up the CLI arguments
$args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems to not work (sometimes?) when argv is registered
array_shift($args); // 1st arg is script name, so drop it

// default logger
if (!in_array('-logger', $args) && hasColorSupport()) {
    array_splice($argv, 0, 0, ['-logger', 'phing.listener.AnsiColorLogger']);
}

try {
    /* Setup Phing environment */
    Phing::startup();

    // Set phing.home property to the value from environment
    // (this may be NULL, but that's not a big problem.)
    Phing::setProperty('phing.home', getenv('PHING_HOME'));

    // Invoke the commandline entry point
    Phing::fire($args);
} catch (ConfigurationException $x) {
    Phing::shutdown();
    Phing::printMessage($x);
    exit(-1); // This was convention previously for configuration errors.
} catch (Exception $x) {
    Phing::shutdown();

    // Assume the message was already printed as part of the build and
    // exit with non-0 error code.

    exit(1);
}
