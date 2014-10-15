<?php

/**
 * Test class to demonstrate
 *
 * @author Michiel Rook
 * @version $Id$
 */
class ErroringTest extends PHPUnit_Framework_TestCase
{
    public function testError()
    {
        throw new Exception("Error");
    }
    
    public function testFailure()
    {
        $this->fail('Fail');
    }
}

