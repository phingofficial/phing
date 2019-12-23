<?php

declare(strict_types=1);

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Grutschus <fabian.grutschus@unister.de>
 * @package phing.system.io
 * @requires OS WIN32|WINNT
 */
class IniFileParserTest extends TestCase
{
    private const DATA_PATH = 'root';

    /**
     * @var IniFileParser
     */
    private $parser;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser = new IniFileParser();

        $structure = [
            'tmp' => [],
        ];

        vfsStream::setup(self::DATA_PATH, null, $structure);
    }

    /**
     * @param string $data
     * @param array  $expected
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     *
     * @dataProvider provideIniFiles
     * @covers       IniFileParser::parseFile
     * @covers       IniFileParser::inVal
     */
    public function testParseFile(string $data, array $expected): void
    {
        $file         = vfsStream::url(self::DATA_PATH . '/tmp/test.ini');
        $writtenBytes = file_put_contents($file, $data);

        $this->assertNotFalse($writtenBytes, 'could not write test data to file ' . $file);

        $phingFile = new PhingFile($file);var_dump($file, (string) $phingFile);
        self::assertSame($expected, $this->parser->parseFile($phingFile));
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     *
     * @covers IniFileParser::parseFile
     */
    public function testParseFileCouldntOpenFile(): void
    {
        $phingFile = new PhingFile(uniqid('', true));

        $this->expectException(IOException::class);

        $this->parser->parseFile($phingFile);
    }

    /**
     * @return array[]
     */
    public function provideIniFiles(): array
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
                'data' => '# property = test',
                'expected' => [],
            ],
            [
                'data' => '   # property = test',
                'expected' => [],
            ],
            [
                'data' => '; property = test',
                'expected' => [],
            ],
            [
                'data' => 'property=test',
                'expected' => [
                    'property' => 'test',
                ],
            ],
            [
                'data' => 'property = true',
                'expected' => [
                    'property' => true,
                ],
            ],
            [
                'data' => 'property = false',
                'expected' => [
                    'property' => false,
                ],
            ],
        ];
    }
}
