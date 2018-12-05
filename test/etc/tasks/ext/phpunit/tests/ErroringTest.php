<?php

/**
 * Test class to demonstrate
 *
 * @author Michiel Rook
 */
class ErroringTest extends \PHPUnit\Framework\TestCase
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

