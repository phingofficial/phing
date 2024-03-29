<?php

    require_once "HelloWorld.php";

    /**
    * Test class for HelloWorld
    *
    * @author Michiel Rook
    * @package hello.world
    */
    class HelloWorldTest extends \PHPUnit\Framework\TestCase
    {
        public function testSayHello()
        {
            $hello = new HelloWorld();
            $this->assertEquals("Hello World!", $hello->sayHello());
        }
    }
