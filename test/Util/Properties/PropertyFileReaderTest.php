<?php
namespace Phing\Tests\Util\Properties;

use Phing\Io\File;
use Phing\Util\Properties\PropertyFileReader;
use Phing\Util\Properties\PropertySet;
use Phing\Util\Properties\PropertySetImpl;

/**
 * @covers Phing\Util\Properties\PropertyFileReader
 */
class PropertyFileReaderTest extends \PHPUnit_Framework_TestCase {

    /** @var PropertySet */
    protected $props;
    
    /** @var PropertyFileReader */
    protected $reader;

    protected function setUp()
    {
        $this->props = new PropertySetImpl();
        $this->reader = new PropertyFileReader($this->props);
    }
    
    public function testReadingFileStripsComments()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/test.properties");
        $this->reader->load($file);

        $this->assertEquals('Testline1', $this->props['testline1']); // http://www.phing.info/trac/ticket/585
        $this->assertEquals('Testline2', $this->props['testline2']); // http://www.phing.info/trac/ticket/585
        $this->assertEquals('ThisIs#NotAComment', $this->props['testline3']);
        $this->assertEquals('ThisIs;NotAComment', $this->props['testline4']);
        $this->assertEquals('This is a multiline value.', $this->props['multiline']);

        $this->assertEquals(5, count(iterator_to_array($this->props->getIterator())));
    } 

    public function testReadingArrayProperties()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/array.properties");
        $this->reader->load($file);

        $this->assertEquals(array('first', 'second', 'test' => 'third'), $this->props['array']);
        $this->assertEquals(array('one' => 'uno', 'two' => 'dos'), $this->props['keyed']);
    }

    public function testDoesNotAttemptPropertyExpansion()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/expansion.properties");
        $this->reader->load($file);

        $this->assertEquals('${a}bar', $this->props['b']);
    }

    public function testReadingGlobalSection()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/sections.properties");
        $this->reader->load($file);

        $this->assertEquals('global', $this->props['global']);
        $this->assertEquals('global', $this->props['section']);
        $this->assertFalse(isset($this->props['inherited']));
    }

    public function testReadingSimpleSection()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/sections.properties");
        $this->reader->load($file, 'top');

        $this->assertEquals('global', $this->props['global']);
        $this->assertEquals('top', $this->props['section']);
        $this->assertEquals('from-top', $this->props['inherited']);
    }

    public function testReadingInheritedSection()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/sections.properties");
        $this->reader->load($file, 'inherited');

        $this->assertEquals('global', $this->props['global']);
        $this->assertEquals('inherited', $this->props['section']);
        $this->assertEquals('from-top', $this->props['inherited']);
    }

    public function testReadingBooleans()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/booleans.properties");
        $this->reader->load($file);

        $this->assertTrue($this->props['true']);
        $this->assertFalse($this->props['false']);
    }


    /**
     * @expectedException Phing\Io\IOException
     */
    public function testLoadNonexistentFileThrowsException()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/nonexistent.properties");
        $this->reader->load($file);
    }

} 
