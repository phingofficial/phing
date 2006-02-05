<?php

/**
 * This is the Phing command line launcher. It starts up the system evironment
 * tests for all important paths and properties and kicks of the main command-
 * line entry point of phing located in phing.Phing
 * @version $Revision: 1.7 $
 */
 
// Set any INI options for PHP
// ---------------------------

ini_set('track_errors', 1);

/* set classpath */
if (getenv('PHP_CLASSPATH')) {
    define('PHP_CLASSPATH',  getenv('PHP_CLASSPATH') . PATH_SEPARATOR . get_include_path());
    ini_set('include_path', PHP_CLASSPATH);
} else {
    define('PHP_CLASSPATH',  get_include_path());
}

require_once 'phing/Phing.php';

/* Setup Phing environment */
Phing::startup();

/* 
  find phing home directory 
   -- if Phing is installed from PEAR this will probably be null,
   which is fine (I think).  Nothing uses phing.home right now.
*/
Phing::setProperty('phing.home', getenv('PHING_HOME'));


/* polish CLI arguments */
$args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems not to work when argv is registered (PHP5b4)
array_shift($args); // 1st arg is script name, so drop it

/* fire main application */
Phing::fire($args);

/*
  exit OO system if not already called by Phing
   -- basically we should not need this due to register_shutdown_function in Phing
 */
 Phing::halt(0);

?>