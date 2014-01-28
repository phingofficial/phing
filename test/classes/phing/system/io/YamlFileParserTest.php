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
require_once 'PHPUnit/TestCase.php';
include_once 'phing/system/io/YamlFileParser.php';
include_once 'phing/system/io/FileParserInterface.php';

/**
 * Unit test for YamlFileParser
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class YamlFileParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FileParserInterface
     */
    private $objectToTest;

    /**
     * @var string
     */
    private $yamlFileStub;

    /**
     * @var string
     */
    private $incorrectYamlFileStub;

    /**
     * @{inheritDoc}
     */
    public function setUp() {
        if (!class_exists('Yaml')) {
            $this->markTestSkipped('Yaml is not installed.');
            exit;
        }
        $this->yamlFileStub = PHING_TEST_BASE .  "/etc/system/io/config.yml";
        $this->incorrectYamlFileStub = PHING_TEST_BASE .  "/etc/system/io/config_wrong.yml";
        $this->objectToTest = new YamlFileParser();
    }

    /**
     * @{inheritDoc}
     */
    public function tearDown() {
        $this->objectToTest = null;
    }

    /**
     * @covers IniFileParser::parseFile
     * @expectedException IOException
     */
    public function testParseFileFileNotReadable() {
        $tmpFile =  tempnam(sys_get_temp_dir(), "test");
        touch($tmpFile);
        $file = new PhingFile($tmpFile);
        unlink($tmpFile);
        $this->objectToTest->parseFile($file);
    }

    /**
     * @covers IniFileParser::parseFile
     * @expectedException IOException
     */
    public function testParseFileFileIncorrectYaml() {
        $file = new PhingFile($this->incorrectYamlFileStub);
        $this->objectToTest->parseFile($file);
    }

    /**
     * @covers IniFileParser::parseFile
     */
    public function testParseFileFile() {
        $file = new PhingFile($this->yamlFileStub);
        $properties = $this->objectToTest->parseFile($file);

        $this->assertEquals($properties['testarea'], 'testvalue');
        $this->assertArrayHasKey(0, $properties['testarea1']);
        $this->assertArrayHasKey(1, $properties['testarea1']);
        $this->assertArrayHasKey('testvalue1', $properties['testarea1'][0]);
        $this->assertArrayHasKey('testvalue2', $properties['testarea1'][1]);
        $this->assertEquals($properties['testarea1'][0]['testvalue1'], 1);
        $this->assertEquals($properties['testarea1'][1]['testvalue2'], 2);
        $this->assertArrayHasKey(0, $properties['testarea2'][0]);
        $this->assertArrayHasKey(2, $properties['testarea2'][0]);
        $this->assertEquals($properties['testarea2'][0][0], 'testvalue1');
        $this->assertEquals($properties['testarea2'][0][2], 'testvalue3');
        $this->assertEquals($properties['testarea3'], false);
        $this->assertEquals($properties['testarea4'], true);
        $this->assertArrayHasKey('testkey1', $properties['testarea6']);
        $this->assertArrayHasKey('testkey2', $properties['testarea6']);
        $this->assertArrayHasKey('testkey1', $properties['testarea6']['testkey1']);
        $this->assertArrayHasKey('testkey1', $properties['testarea6']['testkey2']);
        $this->assertEquals($properties['testarea6']['testkey1']['testkey1'], 'testvalue1');
        $this->assertEquals($properties['testarea6']['testkey1']['testkey1'], 'testvalue1');
    }
}
