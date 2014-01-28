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
include_once 'phing/system/io/IniFileParser.php';
include_once 'phing/system/io/FileParserInterface.php';

/**
 * Unit test for IniFileParser
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class IniFileParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FileParserInterface
     */
    private $objectToTest;

    /**
     * @var string
     */
    private $iniFileStub;

    /**
     * @{inheritDoc}
     */
    public function setUp() {
        $this->iniFileStub = PHING_TEST_BASE .  "/etc/system/util/comments.properties";
        $this->objectToTest = new IniFileParser();
    }

    /**
     * @{inheritDoc}
     */
    public function tearDown() {
        $this->objectToTest = null;
    }

    /**
     * @expectedException IOException
     */
    public function testParseFileFileNotExists() {
        $tmpFile =  tempnam(sys_get_temp_dir(), "test");
        touch($tmpFile);
        $file = new PhingFile($tmpFile);
        unlink($tmpFile);
        $this->objectToTest->parseFile($file);
    }

    /**
     * @covers IniFileParser::parseFile
     */
    public function testParseFileFileWithComments() {
        $file = new PhingFile($this->iniFileStub);
        $properties = $this->objectToTest->parseFile($file);

        $this->assertEquals($properties['useragent'], 'Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1');
        $this->assertEquals($properties['testline1'], 'Testline1');
        $this->assertEquals($properties['testline2'], 'Testline2');
        $this->assertEquals($properties['testline3'], true);
        $this->assertEquals($properties['testline4'], false);
    }
}
