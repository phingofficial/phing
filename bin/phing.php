<?php

/**
 * This is the Phing command line launcher. It starts up the system evironment
 * tests for all important paths and properties and kicks of the main command-
 * line entry point of phing located in phing.Phing
 * @version $Id$
 */


/**
 * use composer if available.
 * move test logic to test/bootstrap.php
 */
if (is_readable('composer.json'))
{
	$composer = json_decode(file_get_contents('composer.json'), true);
	if (is_array($composer) and isset($composer['config']['vendor-dir']))
	{
		$autoloadDir = realpath($composer['config']['vendor-dir']);
	}
	else
	{
		$autoloadDir = realpath('vendor');
	}

	$autoloadFile = $autoloadDir . '/autoload.php';
	if (is_readable($autoloadFile))
	{
		require_once $autoloadFile;
		define ('PHING_COMPOSER_AUTOLOAD_FILE', $autoloadFile);
	}
	unset ($composer, $autoloadDir, $autoloadFile);
}

// Set any INI options for PHP
// ---------------------------

/* set include paths */
set_include_path(
            dirname(__FILE__) . '/../classes' .
            PATH_SEPARATOR .
            get_include_path()
        );

require_once 'phing/Phing.php';

try {

    /* Setup Phing environment */
    Phing::startup();

    // Set phing.home property to the value from environment
    // (this may be NULL, but that's not a big problem.)
    Phing::setProperty('phing.home', getenv('PHING_HOME'));
    // Grab and clean up the CLI arguments
    $args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems to not work (sometimes?) when argv is registered
    array_shift($args); // 1st arg is script name, so drop it

    // Invoke the commandline entry point
    Phing::fire($args);

    // Invoke any shutdown routines.
    Phing::shutdown();

} catch (ConfigurationException $x) {

    Phing::printMessage($x);
    exit(-1); // This was convention previously for configuration errors.

} catch (Exception $x) {

    // Assume the message was already printed as part of the build and
    // exit with non-0 error code.

    exit(1);

}
