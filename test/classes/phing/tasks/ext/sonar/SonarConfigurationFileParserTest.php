<?php
/*
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
 *
 * @author Bernhard Mendl <mail@bernhard-mendl.de>
 * @package phing.tasks.ext.sonar
 */
class SonarConfigurationFileParserTest extends BuildFileTest
{
    protected function setUp(): void    {
        $buildXmlFile = PHING_TEST_BASE . '/etc/tasks/ext/sonar/ConfigurationFileParserTest.xml';
        $this->configureProject($buildXmlFile);
    }

    private function initParser($fileName)
    {
        $fullFileName = PHING_TEST_BASE . '/etc/tasks/ext/sonar/properties/' . $fileName . '.properties';
        $parser = new SonarConfigurationFileParser($fullFileName, $this->getProject());

        return $parser;
    }

    /**
     * @expectedException BuildException
     */
    public function test_construct_fileIsNull_throwsException()
    {
        $file = null;

        new SonarConfigurationFileParser($file, $this->getProject());
    }

    /**
     * @expectedException BuildException
     */
    public function test_construct_fileIsEmpty_throwsException()
    {
        $file = '';

        new SonarConfigurationFileParser($file, $this->getProject());
    }


    /**
     * @expectedException BuildException
     */
    public function test_construct_fileDoesNotExist_throwsException()
    {
        $file = 'ThisFileDoesNotExist';
        $parser = new SonarConfigurationFileParser($file, $this->getProject());

        $parser->parse();
    }

    public function test_emptyFile()
    {
        $parser = $this->initParser('test-empty-file');

        $properties = $parser->parse();

        $this->assertTrue(is_array($properties));
        $this->assertEmpty($properties);
    }

    public function test_propertyWithColonAndWithoutWhitespace()
    {
        $parser = $this->initParser('test-property-with-colon-and-without-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('bar', $properties);
    }

    public function test_propertyWithColonAndWithWhitespace()
    {
        $parser = $this->initParser('test-property-with-colon-and-with-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('bar', $properties);
    }

    public function test_propertyWithEqualsSignAndWithoutWhitespace()
    {
        $parser = $this->initParser('test-property-with-equals-sign-and-without-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('bar', $properties);
    }

    public function test_propertyWithEqualsSignAndWithWhitespace()
    {
        $parser = $this->initParser('test-property-with-equals-sign-and-with-whitespace');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('bar', $properties);
    }

    public function test_commentAtBeginOfLine()
    {
        $parser = $this->initParser('test-property-with-comment-at-begin-of-line');

        $properties = $parser->parse();

        $this->assertArrayNotHasKey('comment', $properties);
    }

    public function test_commentInMiddleOfLine()
    {
        $parser = $this->initParser('test-property-with-comment-in-middle-of-line');

        $properties = $parser->parse();

        $this->assertArrayNotHasKey('comment', $properties);
    }

    public function test_propertyHasMultiLineValue()
    {
        $parser = $this->initParser('test-multiline-property');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('This is a multi-line comment.', $properties);
    }

    public function test_propertyEndsWithABackSlash()
    {
        $parser = $this->initParser('test-property-with-trailing-backslash');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertArrayHasKey('bar', $properties);
        $this->assertContains('This is not a multi-line property, but ends with a backslash\\', $properties);
        $this->assertContains('baz', $properties);
    }

    public function test_propertyHasMultiLineValue_intermediateLineIsEmpty()
    {
        $parser = $this->initParser('test-multiline-property-with-empty-intermediate-line');

        $properties = $parser->parse();

        $this->assertArrayHasKey('foo', $properties);
        $this->assertContains('This is a multi-line comment.', $properties);
    }
}
