<?php

/**
 * @group shouldBeExcluded
 */
class ExcludedTest extends PHPUnit_Framework_TestCase
{
    public function testFails()
    {
        $this->fail();
    }
}