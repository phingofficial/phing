<?php
use \Phing\Io\File;
use \Phing\Util\Properties\PropertyFileWriter;

class PropertiesFacadeTest extends PHPUnit_Framework_TestCase
{
    protected $properties;

    protected function read($section = null)
    {
        $p = new Properties();
        $p->load(new File(PHING_TEST_BASE . "/etc/system/util/expansion.properties"), $section);
        return $p;
    }

    public function testExpansionUponLoad()
    {
        $p = $this->read();
        $this->assertEquals('foobar', $p->getProperty('b'));
        $this->assertEquals('${unknown}ref', $p->getProperty('unexpanded'));
        $this->assertEquals('late', $p->getProperty('late'));
        $this->assertEquals('first,second', $p->getProperty('first.second'));
    }

    public function testSectionLoading()
    {
        $this->assertEquals('global', $this->read()->getProperty('section'));
        $this->assertEquals('top', $this->read('top')->getProperty('section'));
        $this->assertEquals('inherited', $this->read('inherited')->getProperty('section'));

    }

    public function testGetPropertiesReturnsExpandedValues()
    {
        $props = $this->read()->getProperties();
        $this->assertEquals('foobar', $props['b']);
        $this->assertEquals('${unknown}ref', $props['unexpanded']);
        $this->assertEquals('late', $props['late']);
    }

    public function testGlobalSectionAlwaysAvailable()
    {
        $this->assertEquals('global', $this->read('inherited')->getProperty('global'));
    }

    public function testKeys()
    {
        $p = new Properties();
        $p->load(new File(PHING_TEST_BASE . "/etc/system/util/keys.properties"));
        $this->assertEquals(array('first', 'second', 'array'), $p->keys());
    }

    public function testValues()
    {
        $p = new Properties();
        $p->load(new File(PHING_TEST_BASE . "/etc/system/util/keys.properties"));
        $this->assertEquals('first', $p->get('first'));
        $this->assertEquals(array('array1', 'array2', 'index' => 'index'), $p->get('array'));
    }

    public function testStore()
    {
        $p = new Properties();
        $p->load(new File(PHING_TEST_BASE . "/etc/system/util/keys.properties"));
        $w = new PropertyFileWriter($p->getProperties());
    }

    public function testBehaviourAsContainer()
    {
        $p = new Properties();
        $this->assertTrue($p->isEmpty());
    }
}
