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

/**
 * @author Bernhard Mendl <mail@bernhard-mendl.de>
 * @package phing.tasks.ext.sonar
 */
class SonarConfigurationFileParserTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $buildXmlFile = PHING_TEST_BASE . '/etc/tasks/ext/sonar/ConfigurationFileParserTest.xml';
        $this->configureProject($buildXmlFile);
    }

    /**
     * @param string $fileName
     *
     * @return SonarConfigurationFileParser
     */
    private function initParser(string $fileName): SonarConfigurationFileParser
    {
        $fullFileName = PHING_TEST_BASE . '/etc/tasks/ext/sonar/properties/' . $fileName . '.properties';
        return new SonarConfigurationFileParser($fullFileName, $this->getProject());
    }

    /**
     * @return void
     */
    public function testConstructFileIsNullThrowsException(): void
    {
        $file = null;

        $this->expectException(BuildException::class);

        new SonarConfigurationFileParser($file, $this->getProject());
    }

    /**
     * @return void
     */
    public function testConstructFileIsEmptyFhrowsException(): void
    {
        $file = '';

        $this->expectException(BuildException::class);

        new SonarConfigurationFileParser($file, $this->getProject());
    }

    /**
     * @return void
     */
    public function testConstructFileDoesNotExistThrowsException(): void
    {
        $file   = 'ThisFileDoesNotExist';
        $parser = new SonarConfigurationFileParser($file, $this->getProject());

        $this->expectException(BuildException::class);

        $parser->parse();
    }

    /**
     * @return void
     */
    public function testEmptyFile(): void
    {
        $parser = $this->initParser('test-empty-file');

        $properties = $parser->parse();

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($properties);
        } else {
            $this->assertIsArray($properties);
        }
        $this->assertEmpty($properties);
    }

    /**
     * @return void
     */
    public function testPropertyWithColonAndWithoutWhitespace(): void
    {
        $parser = $this->initParser('test-property-with-colon-and-without-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('bar', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyWithColonAndWithWhitespace(): void
    {
        $parser = $this->initParser('test-property-with-colon-and-with-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('bar', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyWithEqualsSignAndWithoutWhitespace(): void
    {
        $parser = $this->initParser('test-property-with-equals-sign-and-without-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('bar', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyWithEqualsSignAndWithWhitespace(): void
    {
        $parser = $this->initParser('test-property-with-equals-sign-and-with-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('bar', $properties);
    }

    /**
     * @return void
     */
    public function testCommentAtBeginOfLine(): void
    {
        $parser = $this->initParser('test-property-with-comment-at-begin-of-line');

        $properties = $parser->parse();

        $this->assertArrayNotHasKey('comment', $properties);
    }

    /**
     * @return void
     */
    public function testCommentInMiddleOfLine(): void
    {
        $parser = $this->initParser('test-property-with-comment-in-middle-of-line');

        $properties = $parser->parse();

        $this->assertArrayNotHasKey('comment', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyHasMultiLineValue(): void
    {
        $parser = $this->initParser('test-multiline-property');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('This is a multi-line comment.', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyEndsWithABackSlash(): void
    {
        $parser = $this->initParser('test-property-with-trailing-backslash');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertArrayHasKey('bar', $properties);
        self::assertContains('This is not a multi-line property, but ends with a backslash\\', $properties);
        self::assertContains('baz', $properties);
    }

    /**
     * @return void
     */
    public function testPropertyHasMultiLineValueIntermediateLineIsEmpty(): void
    {
        $parser = $this->initParser('test-multiline-property-with-empty-intermediate-line');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        self::assertContains('This is a multi-line comment.', $properties);
    }
}
