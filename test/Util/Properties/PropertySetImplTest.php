<?php
namespace Phing\Tests\Util\Properties;

use Phing\Util\Properties\PropertySetImpl;

class PropertySetImplTest extends \PHPUnit_Framework_TestCase
{
    public function testFindPrefix()
    {
        $base = new PropertySetImpl();
        $base['foo.one'] = 1;
        $base['foo.two'] = 2;
        $base['bar'] = 42;

        $fooOnly = $base->prefix('foo');

        $this->assertTrue(isset($fooOnly['one']));
        $this->assertEquals(1, $fooOnly['one']);
        $this->assertFalse(isset($fooOnly['foo.one']));
        $this->assertFalse(isset($fooOnly['bar']));
    }
}
