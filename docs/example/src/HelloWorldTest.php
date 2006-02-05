<?php

    require_once "PHPUnit2/Framework/TestCase.php";
    require_once "HelloWorld.php";

    /**
    * Test class for HelloWorld
    *
    * @author Michiel Rook
    * @version $Id: HelloWorldTest.php,v 1.1 2004/11/11 00:05:29 mrook Exp $
    * @package hello.world
    */
    class HelloWorldTest extends PHPUnit2_Framework_TestCase
    {
        public function testSayHello()
        {
            $hello = new HelloWorld();
            $this->assertEquals("Hello World!", $hello->sayHello());
        }
    }

?>
