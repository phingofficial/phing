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

include_once 'phing/system/io/YamlFileParser.php';
include_once 'phing/system/io/FileParserInterface.php';

use org\bovigo\vfs\vfsStream;

/**
 * @author Fabian Grutschus <fabian.grutschus@unister.de>
 * @package phing.system.io
 */
class IniFileParserTest extends PHPUnit_Framework_TestCase
{
    private $parser;
    private $root;

    protected function setUp()
    {
        $this->parser = new IniFileParser();
        $this->root = vfsStream::setup();
    }

    /**
     * @dataProvider provideIniFiles
     * @covers IniFileParser::parseFile
     * @covers IniFileParser::inVal
     */
    public function testParseFile($data, $expected)
    {
        $file = $this->root->url() . '/test';
        file_put_contents($file, $data);

        $phingFile = new PhingFile($file);
        $this->assertSame($expected, $this->parser->parseFile($phingFile));
    }

    /**
     * @covers IniFileParser::parseFile
     * @expectedException IOException
     */
    public function testParseFileCouldntOpenFile()
    {
        $phingFile = new PhingFile(uniqid());
        $this->parser->parseFile($phingFile);
    }

    /**
     * @return array
     */
    public function provideIniFiles()
    {
        return array(
            array(
                'data'     => "property = test\nproperty2 = test2\nproperty3 = test3\n",
                'expected' => array(
                    'property'  => 'test',
                    'property2' => 'test2',
                    'property3' => 'test3',
                ),
            ),
            array(
                'data'     => "property = test\r\nproperty2 = test2\r\nproperty3 = test3\r\n",
                'expected' => array(
                    'property'  => 'test',
                    'property2' => 'test2',
                    'property3' => 'test3',
                ),
            ),
            array(
                'data'     => "property = test,\\\ntest2,\\\ntest3\n",
                'expected' => array(
                    'property' => 'test,test2,test3',
                ),
            ),
            array(
                'data'     => "property = test,\\\r\ntest2,\\\r\ntest3\r\n",
                'expected' => array(
                    'property' => 'test,test2,test3',
                ),
            ),
            array(
                'data'     => "# property = test",
                'expected' => array(),
            ),
            array(
                'data'     => "   # property = test",
                'expected' => array(),
            ),
            array(
                'data'     => "; property = test",
                'expected' => array(),
            ),
            array(
                'data'     => "property=test",
                'expected' => array(
                    'property' => 'test',
                ),
            ),
            array(
                'data'     => "property = true",
                'expected' => array(
                    'property' => true,
                ),
            ),
            array(
                'data'     => "property = false",
                'expected' => array(
                    'property' => false,
                ),
            ),
        );
    }
}
