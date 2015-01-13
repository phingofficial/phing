<?php

    require_once "etc/tasks/ext/phpunit/src/HelloWorld.php";

    /**
    * Test class for HelloWorld
    *
    * @author Michiel Rook
    * @version $Id$
    * @package hello.world
    */
    class HelloWorldTest extends PHPUnit_Framework_TestCase
    {
        public function testSayHello()
        {
            $hello = new HelloWorld();
            $this->assertEquals("Hello World!", $hello->sayHello());
        }
    }

?>
