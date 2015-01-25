<?php
namespace Phing\Tests\Util\Properties;

use Phing\Exception\BuildException;
use Phing\PropertySet;
use Phing\Util\Properties\PropertyExpansionHelper;
use Phing\Util\Properties\PropertySetImpl;

class PropertyExpansionHelperTest extends \PHPUnit_Framework_TestCase
{

    /** @var PropertySet */
    protected $properties;
    protected $helper;

    protected function setUp()
    {
        $this->properties = new PropertySetImpl();
        $this->helper = new PropertyExpansionHelper($this->properties);
    }

    public function testSimpleExpansion()
    {
        $this->properties['a'] = 'foo';
        $this->properties['b'] = '${a}bar';

        $this->assertEquals('foobar', $this->helper['b']);
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testSimpleCircleDetection()
    {
        $this->properties['a'] = 'foo${b}';
        $this->properties['b'] = 'bar${a}';
        $this->helper['a'];
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
        $this->helper['a'];
    }

    public function testCircleDoesNotMatterUnlessExpanded()
    {
        $this->properties['a'] = 'foo${b}';
        $this->properties['b'] = 'bar${a}';

        // no problem to reach here, since no expansion takes place
        $this->properties['b'] = 'baz';

        $this->assertEquals('foobaz', $this->helper['a']);
    }

    public function testPropertyCanBeReferencedMultipleTimes()
    {
        // See http://www.phing.info/trac/ticket/1118
        $this->properties['a'] = 'foo';
        $this->properties['b'] = '${a}bar${a}';

        $this->assertEquals('foobarfoo', $this->helper['b']);
    }

    public function testArrayExpansion()
    {
        $this->properties['a[]'] = 'foo';
        $this->properties['a[]'] = 'bar';
        $this->properties['expanded'] = '${a}';

        $this->assertEquals(array('foo', 'bar'), $this->helper['a']);
        $this->assertEquals('foo,bar', $this->helper['expanded']);
    }

    public function testArrayKeys()
    {
        $this->properties['a[f]'] = 'foo';
        $this->properties['a[b]'] = 'bar';

        $this->assertEquals('foo', $this->helper['a[f]']);
        $this->assertEquals('bar', $this->helper['a[b]']);
    }

    public function testBooleanExpansion()
    {
        $this->properties['t'] = true;
        $this->properties['f'] = false;
        $this->properties['true'] = '${t}';
        $this->properties['false'] = '${f}';

        /*
         * If properties are not expanded, PropertyExpansionHelper
         * feels like a regular PropertySet and does not change anything.
         */
        $this->assertTrue($this->helper['t']);
        $this->assertFalse($this->helper['f']);

        /*
         * However, when expanding boolean props they will be converted
         * to "true"/"false" strings.
         */
        $this->assertEquals('true', $this->helper['true']);
        $this->assertEquals('false', $this->helper['false']);
    }

    public function testUnknownPropertiesAreLeftUntouched()
    {
        $this->properties['test'] = 'something ${unknown}';
        $this->assertEquals('something ${unknown}', $this->helper['test']);
    }

    public function testExpansionOfArrayKeys()
    {
        $this->properties['test[a]'] = 'foobar';
        $this->properties['test[b]'] = 'barbaz';
        $this->properties['choice'] = 'a';
        $this->properties['result'] = '${test[${choice}]}';

        $this->assertEquals('foobar', $this->helper['result']);
    }

    public function testNestedExpansion()
    {
        $this->properties['options.first'] = 'foobar';
        $this->properties['options.second'] = 'barbaz';
        $this->properties['choice'] = 'first';
        $this->properties['result'] = '${options.${choice}}';

        $this->assertEquals('foobar', $this->helper['result']);
    }
}
