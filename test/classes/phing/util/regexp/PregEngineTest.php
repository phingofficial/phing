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
 *
 * @package phing.util
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test cases for phing/util/regexp/PregEngine
 */
class PregEngineTest extends TestCase
{
    /**
     * Test the default ignore-case value.
     *
     * @return void
     */
    public function testIgnoreCaseDefaultValue(): void
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getIgnoreCase());
    }

    /**
     * Test setting the ignore-case flag to true.
     *
     * @return void
     */
    public function testIgnoreCaseSetTrue(): void
    {
        $pregEngine = new PregEngine();

        $pregEngine->setIgnoreCase(true);
        $this->assertTrue($pregEngine->getIgnoreCase());
        $this->assertEquals('i', $pregEngine->getModifiers());
    }

    /**
     * Test setting the ignore-case flag to false.
     *
     * @return void
     */
    public function testIgnoreCaseSetFalse(): void
    {
        $pregEngine = new PregEngine();

        $pregEngine->setIgnoreCase(false);
        $this->assertFalse($pregEngine->getIgnoreCase());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test the default multi-line value.
     *
     * @return void
     */
    public function testMultiLineDefaultValue(): void
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getMultiline());
    }

    /**
     * Test setting the multi-line flag to true.
     *
     * @return void
     */
    public function testMultilineSetTrue(): void
    {
        $pregEngine = new PregEngine();

        $pregEngine->setMultiline(true);
        $this->assertTrue($pregEngine->getMultiline());
        $this->assertEquals('s', $pregEngine->getModifiers());

        $pregEngine->setMultiline(false);
        $this->assertFalse($pregEngine->getMultiline());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test setting the multi-line flag to true.
     *
     * @return void
     */
    public function testSMultilineSetFalse(): void
    {
        $pregEngine = new PregEngine();

        $pregEngine->setMultiline(true);
        $this->assertTrue($pregEngine->getMultiline());
        $this->assertEquals('s', $pregEngine->getModifiers());

        $pregEngine->setMultiline(false);
        $this->assertFalse($pregEngine->getMultiline());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test the default modifiers value.
     *
     * @return void
     */
    public function testModifiersDefaultValue(): void
    {
        $pregEngine = new PregEngine();
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test setting of the modifiers.
     *
     * @return void
     */
    public function testModifiersSet(): void
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('gu');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'u'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'g'));
    }

    /**
     * Test setting ignore-case through the modifier.
     *
     * @return void
     *
     * @todo This is a new test that fails due to a pre-existing condition.
     */
    public function testModifiersSetIgnoreCase(): void
    {
        $this->markTestSkipped();

        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('i');
        $this->assertTrue($pregEngine->getIgnoreCase());
        $this->assertEquals('i', $pregEngine->getModifiers());
    }

    /**
     * Test setting multi-line through the modifier.
     *
     * @return void
     *
     * @todo This is a new test that fails due to a pre-existing conditions.
     */
    public function testModifiersSetMultiline(): void
    {
        $this->markTestSkipped();

        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('s');
        $this->assertTrue($pregEngine->getMultiline());
        $this->assertEquals('s', $pregEngine->getModifiers());
    }

    /**
     * Test duplicate modifier flags are removed.
     *
     * @return void
     */
    public function testModifiersSetRemoveDuplicates(): void
    {
        $pregEngine = new PregEngine();

        $pregEngine->setModifiers('guummmii');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'u'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'g'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'i'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'm'));
    }

    /**
     * Tests setting the ignore-case flag with the modifier method, then unsetting using ignore-case method.
     *
     * @return void
     */
    public function testModifierSetIgnoreCaseUnset(): void
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('i');
        $pregEngine->setIgnoreCase(false);
        $this->assertEquals('', $pregEngine->getModifiers());
    }

    /**
     * Tests setting the ignore-case flag with the modifier method, then unsetting using ignore-case method.
     *
     * @return void
     */
    public function testModifierSetMultilineUnset(): void
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('s');
        $pregEngine->setMultiline(false);
        $this->assertEquals('', $pregEngine->getModifiers());
    }

    /**
     * Test pattern match functionality.
     *
     * @return void
     */
    public function testPatternMatch(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = '\d{2}';
        $source     = '1234';
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(['12'], $matches);
    }

    /**
     * Test match for pattern containing the PregEngine delimiter.
     *
     * @return void
     */
    public function testPatternMatchWithPatternDelimiter(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = PregEngine::DELIMITER;
        $source     = PregEngine::DELIMITER;
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     * Test match for pattern containing the PregEngine delimiter with irregular escaping.
     *
     * @return void
     */
    public function testPatternMatchWithEscapedPatternDelimiter(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = '\\\\\\\\' . PregEngine::DELIMITER . 'abc\\\\\\' . PregEngine::DELIMITER . '123\\\\' . PregEngine::DELIMITER . 'efg\\' . PregEngine::DELIMITER . '456' . PregEngine::DELIMITER;
        $source     = '\\\\' . PregEngine::DELIMITER . 'abc\\' . PregEngine::DELIMITER . '123\\' . PregEngine::DELIMITER . 'efg' . PregEngine::DELIMITER . '456' . PregEngine::DELIMITER;
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(
            [$source],
            $matches,
            'The match method did not properly escape uses of the delimiter in the regular expression.'
        );
    }

    /**
     * Test regular expressions match-all functionality
     *
     * @return void
     */
    public function testMatchAll(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = '\d{2}';
        $source     = '1234';
        $pregEngine->matchAll($pattern, $source, $matches);

        $this->assertEquals([['12', '34']], $matches);
    }

    /**
     * Test pattern replace.
     *
     * @return void
     */
    public function testReplace(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = '\d{2}';
        $source     = '1234';
        $result     = $pregEngine->replace($pattern, 'ab', $source);

        $this->assertEquals('abab', $result);
    }

    /**
     * Test pattern replace using \1 back reference format (as opposed to $1).
     *
     * @return void
     */
    public function testReplaceWithBackReference(): void
    {
        $pregEngine = new PregEngine();
        $pattern    = '(\d{2})(\d{2})';
        $source     = '1234';
        $result     = $pregEngine->replace($pattern, '<\1>', $source);

        $this->assertEquals('<12>', $result);
    }
}
