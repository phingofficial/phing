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

namespace Phing\Io;

/**
 * @author Fabian Grutschus <fabian.grutschus@unister.de>
 * @requires OS ^(?:(?!Win).)*$
 */
class IniFileParserTest extends \PHPUnit\Framework\TestCase
{
    private $parser;
    private $root;

    protected function setUp(): void
    {
        $this->parser = new IniFileParser();
        $this->root = \org\bovigo\vfs\vfsStream::setUp();
    }

    /**
     * @dataProvider provideIniFiles
     * @covers       IniFileParser::parseFile
     * @covers       IniFileParser::inVal
     */
    public function testParseFile($data, $expected)
    {
        $file = $this->root->url() . '/test';
        file_put_contents($file, $data);

        $phingFile = new File($file);
        $this->assertSame($expected, $this->parser->parseFile($phingFile));
    }

    /**
     * @covers IniFileParser::parseFile
     */
    public function testParseFileCouldntOpenFile()
    {
        $phingFile = new File(uniqid('', true));

        $this->expectException(IOException::class);

        $this->parser->parseFile($phingFile);
    }

    /**
     * @return array
     */
    public function provideIniFiles()
    {
        return [
            [
                'data' => "property = test\nproperty2 = test2\nproperty3 = test3\n",
                'expected' => [
                    'property' => 'test',
                    'property2' => 'test2',
                    'property3' => 'test3',
                ],
            ],
            [
                'data' => "property = test\r\nproperty2 = test2\r\nproperty3 = test3\r\n",
                'expected' => [
                    'property' => 'test',
                    'property2' => 'test2',
                    'property3' => 'test3',
                ],
            ],
            [
                'data' => "property = test,\\\ntest2,\\\ntest3\n",
                'expected' => [
                    'property' => 'test,test2,test3',
                ],
            ],
            [
                'data' => "property = test,\\\r\ntest2,\\\r\ntest3\r\n",
                'expected' => [
                    'property' => 'test,test2,test3',
                ],
            ],
            [
                'data' => "# property = test",
                'expected' => [],
            ],
            [
                'data' => "   # property = test",
                'expected' => [],
            ],
            [
                'data' => "; property = test",
                'expected' => [],
            ],
            [
                'data' => "property=test",
                'expected' => [
                    'property' => 'test',
                ],
            ],
            [
                'data' => "property = true",
                'expected' => [
                    'property' => true,
                ],
            ],
            [
                'data' => "property = false",
                'expected' => [
                    'property' => false,
                ],
            ],
            [
                'data' => "[app]\napp.uno=foo\napp.dos=bar\napp.tres=baz\n",
                'expected' => [
                    'app.uno' => 'foo',
                    'app.dos' => 'bar',
                    'app.tres' => 'baz',
                ],
            ],
        ];
    }
}
