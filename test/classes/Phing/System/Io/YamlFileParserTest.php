<?php
/**
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

/**
 * Unit test for YamlFileParser
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class YamlFileParserTest extends \PHPUnit\Framework\TestCase
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
    public function setUp(): void
    {
        if (!class_exists('\Symfony\Component\Yaml\Parser')) {
            $this->markTestSkipped('Yaml parser is not installed.');
            exit;
        }
        $this->yamlFileStub = PHING_TEST_BASE . "/etc/System/Io/config.yml";
        $this->incorrectYamlFileStub = PHING_TEST_BASE . "/etc/System/Io/config_wrong.yml";
        $this->objectToTest = new YamlFileParser();
    }

    /**
     * @{inheritDoc}
     */
    public function tearDown(): void
    {
        $this->objectToTest = null;
    }

    /**
     * @covers IniFileParser::parseFile
     */
    public function testParseFileFileNotReadable()
    {
        $tmpFile = tempnam(FileUtils::getTempDir(), "test");
        touch($tmpFile);
        $file = new PhingFile($tmpFile);
        unlink($tmpFile);

        $this->expectException(IOException::class);

        $this->objectToTest->parseFile($file);
    }

    /**
     * @covers IniFileParser::parseFile
     */
    public function testParseFileFileIncorrectYaml()
    {
        $file = new PhingFile($this->incorrectYamlFileStub);

        $this->expectException(IOException::class);

        $this->objectToTest->parseFile($file);
    }

    /**
     * The YamlFileParser has to provide a flattened array which then is
     * compatible to the actual behaviour of properties.
     *
     * @covers IniFileParser::parseFile
     */
    public function testParseFileFile()
    {
        $file = new PhingFile($this->yamlFileStub);
        $properties = $this->objectToTest->parseFile($file);

        $this->assertEquals('testvalue', $properties['testarea']);
        $this->assertEquals(1, $properties['testarea1.testkey1']);
        $this->assertEquals(2, $properties['testarea1.testkey2']);
        $this->assertEquals('testvalue1,testvalue2,testvalue3', $properties['testarea2']);
        $this->assertEquals(false, $properties['testarea3']);
        $this->assertEquals(true, $properties['testarea4']);
        $this->assertEquals('testvalue1', $properties['testarea6.testkey1.testkey1']);
        $this->assertEquals('testvalue2', $properties['testarea6.testkey1.testkey2']);
        $this->assertEquals('testvalue1', $properties['testarea6.testkey2.testkey1']);
    }
}
