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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit test for FileParserFactory
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class FileParserFactoryTest extends TestCase
{
    /**
     * @var FileParserInterface
     */
    private $objectToTest;

    /**
     * @return void
     *
     * @{inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectToTest = new FileParserFactory();
    }

    /**
     * @return void
     *
     * @{inheritDoc}
     */
    protected function tearDown(): void
    {
        $this->objectToTest = null;
    }

    /**
     * @param string $parserName
     * @param string $expectedType
     *
     * @return void
     *
     * @covers       FileParserFactory::createParser
     * @dataProvider parserTypeProvider
     */
    public function testCreateParser(string $parserName, string $expectedType): void
    {
        self::assertInstanceOf($expectedType, $this->objectToTest->createParser($parserName));
    }

    /**
     * @return array[]
     */
    public function parserTypeProvider(): array
    {
        return [
            ['properties', 'IniFileParser'],
            ['ini', 'IniFileParser'],
            ['foo', 'IniFileParser'],
            ['yml', 'YamlFileParser'],
            ['yaml', 'YamlFileParser'],
        ];
    }
}
