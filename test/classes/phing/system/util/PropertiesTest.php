<?php

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */
use Phing\Io\File;

/**
 * Unit test for Properties class
 *
 * @author Michiel Rook <mrook@php.net>
 * @package phing.system.util
 * @version $Id$
 *
 * @covers Properties
 */
class PropertiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Properties
     */
    private $props = null;

    public function setUp()
    {
        $this->props = new Properties();
    }

    public function testEmpty()
    {
        $this->assertTrue($this->props->isEmpty());
        $this->props->append('foo', 'bar');
        $this->assertFalse($this->props->isEmpty());
    }

    public function testAppendPropertyValues()
    {
        $this->props->append('t', 'a');
        $this->props->append('t', 'b');
        $this->assertEquals('a,b', $this->props->get('t'));
    }

    public function testToString()
    {
        $this->props->put('a', 'b');

        $this->assertEquals("a=b" . PHP_EOL, $this->props->toString());
    }

    public function testStore()
    {
        $file = new File(PHING_TEST_BASE . "/tmp/props");
        $this->props->put('t', 'a');
        $this->props->store($file, 'header');
        $this->assertFileExists($file->getPath());
        $this->assertEquals('# header' . PHP_EOL . 't=a' . PHP_EOL, file_get_contents($file->getPath()));
        unlink($file->getPath());
    }

    public function testCanBeInitializedWithProperties()
    {
        $this->props = new Properties(array('foo' => 'bar', 'baz' => 'qux'));
        $this->assertEquals('bar', $this->props->getProperty('foo'));
        $this->assertEquals('qux', $this->props->getProperty('baz'));
    }

    public function testKeys()
    {
        $this->props = new Properties(array('foo' => 'bar', 'baz' => 'qux'));
        $this->assertEquals(array('foo', 'baz'), $this->props->keys());
    }

    public function testReadUnknownPropertyReturnsNull()
    {
        $this->assertNull($this->props->getProperty('foo'));
    }

    public function testNoExpansionIsPerformedWhenReading()
    {
        $file = new File(PHING_TEST_BASE . "/etc/system/util/expansion.properties");
        $this->props->load($file);

        $this->assertEquals('${a}bar', $this->props->getProperty('b'));
    }

    public function testReadingInheritedSection()
    {
        /*
            In-depth testing of section loading happens in PropertyFileReaderTest. This
            just makes sure we're taking care of the section parameter at all.
        */
        $file = new File(PHING_TEST_BASE . "/etc/system/util/sections.properties");
        $this->props->load($file, 'inherited');

        $this->assertEquals('global', $this->props->getProperty('global'));
        $this->assertEquals('inherited', $this->props->getProperty('section'));
        $this->assertEquals('from-top', $this->props->getProperty('inherited'));
    }


}
