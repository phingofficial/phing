<?php

class ProviderFailTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleFail()
    {
        $this->assertFalse(true);
    }

 /**
  * @dataProvider provider
  */
    public function testProvider($v1, $v2)
    {
        $this->assertEquals($v1, $v2);
    }

    public function provider()
    {
        return [
        [true,true]
        ];
    }
}
