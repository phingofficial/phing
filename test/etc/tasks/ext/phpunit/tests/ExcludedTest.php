<?php

/**
 * @group shouldBeExcluded
 */
class ExcludedTest extends \PHPUnit\Framework\TestCase
{
    public function testFails()
    {
        $this->fail();
    }
}
