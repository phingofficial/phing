<?php

// Set up environment (include_path, etc.) for running these tests
define('PHPUnit2_MAIN_METHOD', "don't let PHPUnit try to auto-invoke anything!");

include_once 'PHPUnit2/TextUI/TestRunner.php';
include_once 'PHPUnit2/Framework/TestSuite.php';

// Probably should use PHING_HOME here instead of assuming that test/ is still subdir.
ini_set('include_path', realpath(dirname(__FILE__) . '/classes') . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../classes') . PATH_SEPARATOR . ini_get('include_path'));

define('PHING_TEST_BASE', dirname(__FILE__));

// STARTUP PHING
require_once 'phing/Phing.php';

Phing::startup();


// For now just add all classes to here -- this is ugly, but
// we don't need to pollute the test structure with AllTest classes
// yet ...


// ----------------------------------------------------------
// core
// ----------------------------------------------------------

$coreSuite = new PHPUnit2_Framework_TestSuite("Phing Core");
include_once 'phing/IntrospectionHelperTest.php';
$coreSuite->addTestSuite(new ReflectionClass('IntrospectionHelperTest'));


// ----------------------------------------------------------
// types
// ----------------------------------------------------------

$typesSuite = new PHPUnit2_Framework_TestSuite("Phing Types");

include_once 'phing/types/MapperTest.php';
$typesSuite->addTestSuite(new ReflectionClass('MapperTest'));

include_once 'phing/filters/LineContainsTest.php';
$typesSuite->addTestSuite(new ReflectionClass('LineContainsTest'));

include_once 'phing/types/CommandlineTest.php';
$typesSuite->addTestSuite(new ReflectionClass('CommandlineTest'));

include_once 'phing/types/FileSetTest.php';
$typesSuite->addTestSuite(new ReflectionClass('FileSetTest'));

// ----------------------------------------------------------
// tasks
// ----------------------------------------------------------

$tasksSuite = new PHPUnit2_Framework_TestSuite("Phing Tasks");

include_once 'phing/tasks/TypedefTaskTest.php';
$tasksSuite->addTestSuite(new ReflectionClass('TypedefTaskTest'));


// Conditions
include_once 'phing/tasks/condition/ContainsConditionTest.php';
include_once 'phing/tasks/condition/EqualsConditionTest.php';
$tasksSuite->addTestSuite(new ReflectionClass('ContainsConditionTest'));
$tasksSuite->addTestSuite(new ReflectionClass('EqualsConditionTest'));

include_once 'phing/tasks/PropertyTaskTest.php';
$tasksSuite->addTestSuite(new ReflectionClass('PropertyTaskTest'));


 
$suite = new PHPUnit2_Framework_TestSuite('Phing Tests');
$suite->addTest($coreSuite);
$suite->addTest($typesSuite);
$suite->addTest($tasksSuite);



// Run it!
PHPUnit2_TextUI_TestRunner::run($suite);


// SHUTDOWN PHING
Phing::shutdown();