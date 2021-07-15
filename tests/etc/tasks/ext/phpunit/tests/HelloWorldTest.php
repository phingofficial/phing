<?php

    require_once 'src/HelloWorld.php';

    /**
     * Test class for HelloWorld.
     *
     * @author Michiel Rook
     *
     * @internal
     * @coversNothing
     */
    class HelloWorldTest extends \PHPUnit\Framework\TestCase
    {
        public function testSayHello()
        {
            $hello = new HelloWorld();
            $this->assertEquals('Hello World!', $hello->sayHello());
        }
    }
