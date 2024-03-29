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

namespace Phing\Test\Io;

use Phing\Io\FileParserFactory;
use Phing\Io\IniFileParser;
use Phing\Io\YamlFileParser;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for FileParserFactory.
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 *
 * @internal
 */
class FileParserFactoryTest extends TestCase
{
    /**
     * @var FileParserFactory
     */
    private $objectToTest;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectToTest = new FileParserFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        $this->objectToTest = null;
    }

    /**
     * @covers       \FileParserFactory::createParser
     * @dataProvider parserTypeProvider
     *
     * @param mixed $parserName
     * @param mixed $expectedType
     */
    public function testCreateParser($parserName, $expectedType): void
    {
        $this->assertInstanceOf($expectedType, $this->objectToTest->createParser($parserName));
    }

    public function parserTypeProvider(): array
    {
        return [
            ['properties', IniFileParser::class],
            ['ini', IniFileParser::class],
            ['foo', IniFileParser::class],
            ['yml', YamlFileParser::class],
            ['yaml', YamlFileParser::class],
        ];
    }
}
