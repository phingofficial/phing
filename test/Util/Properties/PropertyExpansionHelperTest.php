<?php
namespace Phing\Tests\Util\Properties;

use Phing\Util\Properties\PropertyExpansionHelper;
use Phing\Util\Properties\PropertySet;
use Phing\Util\Properties\PropertySetImpl;

/**
 * @covers \Phing\Util\Properties\PropertyExpansionHelper
 */
class PropertyExpansionHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var PropertySet */
    protected $properties;

    /** @var PropertyExpansionHelper */
    protected $helper;

    protected function setUp()
    {
        $this->properties = new PropertySetImpl();
        $this->helper = new PropertyExpansionHelper($this->properties);
    }

    public function testSimpleExpansion()
    {
        $this->properties['a'] = 'foo';
        $this->assertEquals('foobar', $this->helper->expand('${a}bar'));
    }

    public function testArrayExpansion()
    {
        $this->properties['a'] = 'foo';
        $this->properties['b'] = 'bar';

        $this->assertEquals(array('first' => 'foo', 'second' => 'bar'), $this->helper->expand(array('first' => '${a}', 'second' => '${b}')));
    }

    public function testTraversableExpansion()
    {
        $this->properties['a'] = 'foo';
        $this->properties['b'] = 'bar';
        $this->properties['foobar'] = '${a}${b}';

        $this->assertEquals(array('a' => 'foo', 'b' => 'bar', 'foobar' => 'foobar'), $this->helper->expand($this->properties));
    }

    public function testNullExpansion()
    {
        $this->assertNull($this->helper->expand(null));
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testSimpleCircleDetection()
    {
        $this->properties['a'] = 'foo${b}';
        $this->properties['b'] = 'bar${a}';
        $this->helper->expand('${a}');
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testLargerCircleDetection()
    {
        // See https://github.com/phingofficial/phing/pull/249
        $this->properties['a'] = 'foo${b}';
        $this->properties['b'] = 'bar${c}';
        $this->properties['c'] = 'baz${b}';
        $this->helper->expand('${a}');
    }

    public function testPropertyCanBeReferencedMultipleTimes()
    {
        // See http://www.phing.info/trac/ticket/1118
        $this->properties['a'] = 'foo';
        $this->assertEquals('foobarfoo', $this->helper->expand('${a}bar${a}'));
    }

    public function testArrayPropertyExpansionImplodesToString()
    {
        $this->properties['a[]'] = 'foo';
        $this->properties['a[]'] = 'bar';

        $this->assertEquals('foo,bar', $this->helper->expand('${a}'));
    }

    public function testBooleanExpansion()
    {
        $this->properties['t'] = true;
        $this->properties['f'] = false;

        $this->assertEquals('true', $this->helper->expand('${t}')); // converted to string
        $this->assertEquals('false', $this->helper->expand('${f}')); // converted to string
    }

    public function testUnknownPropertiesAreLeftUntouched()
    {
        $this->assertEquals('something ${unknown}', $this->helper->expand('something ${unknown}'));
    }

    public function testExpansionOfArrayKeys()
    {
        $this->properties['test[a]'] = 'foobar';
        $this->properties['test[b]'] = 'barbaz';
        $this->properties['choice'] = 'a';

        $this->assertEquals('foobar', $this->helper->expand('${test[${choice}]}'));
    }

    public function testNestedExpansion()
    {
        $this->properties['options.first'] = 'foobar';
        $this->properties['options.second'] = 'barbaz';
        $this->properties['choice'] = 'first';

        $this->assertEquals('foobar', $this->helper->expand('${options.${choice}}'));
    }
}
