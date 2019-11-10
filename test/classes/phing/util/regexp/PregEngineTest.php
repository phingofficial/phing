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

/**
 * Class PregEngineTest
 *
 * Test cases for phing/util/regexp/PregEngine
 */
class PregEngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the default ignore-case value.
     */
    public function testIgnoreCaseDefaultValue()
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getIgnoreCase());
    }

    /**
     * Test setting the ignore-case flag to true.
     */
    public function testIgnoreCaseSetTrue()
    {
        $pregEngine = new PregEngine();

        $pregEngine->setIgnoreCase(true);
        $this->assertTrue($pregEngine->getIgnoreCase());
        $this->assertEquals('i', $pregEngine->getModifiers());
    }

    /**
     * Test setting the ignore-case flag to false.
     */
    public function testIgnoreCaseSetFalse()
    {
        $pregEngine = new PregEngine();

        $pregEngine->setIgnoreCase(false);
        $this->assertFalse($pregEngine->getIgnoreCase());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test the default multi-line value.
     */
    public function testMultiLineDefaultValue()
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getMultiline());
    }

    /**
     * Test setting the multi-line flag to true.
     */
    public function testMultilineSetTrue()
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
     */
    public function testSMultilineSetFalse()
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
     */
    public function testModifiersDefaultValue()
    {
        $pregEngine = new PregEngine();
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test setting of the modifiers.
     */
    public function testModifiersSet()
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('gu');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'u'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers(), 'g'));
    }

    /**
     * Test setting ignore-case through the modifier.
     * @todo This is a new test that fails due to a pre-existing condition.
     */
//    public function testModifiersSetIgnoreCase()
//    {
//        $pregEngine = new PregEngine();
//        $pregEngine->setModifiers('i');
//        $this->assertTrue($pregEngine->getIgnoreCase());
//        $this->assertEquals('i', $pregEngine->getModifiers());
//    }

    /**
     * Test setting multi-line through the modifier.
     * @todo This is a new test that fails due to a pre-existing conditions.
     */
//    public function testModifiersSetMultiline()
//    {
//        $pregEngine = new PregEngine();
//        $pregEngine->setModifiers('s');
//        $this->assertTrue($pregEngine->getMultiline());
//        $this->assertEquals('s', $pregEngine->getModifiers());
//    }

    /**
     * Test duplicate modifier flags are removed.
     */
    public function testModifiersSetRemoveDuplicates()
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
     */
    public function testModifierSetIgnoreCaseUnset()
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('i');
        $pregEngine->setIgnoreCase(false);
        $this->assertEquals('', $pregEngine->getModifiers());
    }

    /**
     * Tests setting the ignore-case flag with the modifier method, then unsetting using ignore-case method.
     */
    public function testModifierSetMultilineUnset()
    {
        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('s');
        $pregEngine->setMultiline(false);
        $this->assertEquals('', $pregEngine->getModifiers());
    }

    /**
     * Test pattern match functionality.
     */
    public function testPatternMatch()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(['12'], $matches);
    }

    /**
     * Test match for pattern containing the PregEngine delimiter.
     */
    public function testPatternMatchWithPatternDelimiter()
    {
        $pregEngine = new PregEngine();
        $pattern = PregEngine::DELIMITER;
        $source = PregEngine::DELIMITER;
        $pregEngine->match($pattern, $source, $matches);
    }

    /**
     * Test match for pattern containing the PregEngine delimiter with irregular escaping.
     */
    public function testPatternMatchWithEscapedPatternDelimiter()
    {
        $pregEngine = new PregEngine();
        $pattern = '\\\\\\\\' . PregEngine::DELIMITER . 'abc\\\\\\' . PregEngine::DELIMITER . '123\\\\' . PregEngine::DELIMITER . 'efg\\' . PregEngine::DELIMITER . '456' . PregEngine::DELIMITER;
        $source = '\\\\' . PregEngine::DELIMITER . 'abc\\' . PregEngine::DELIMITER . '123\\' . PregEngine::DELIMITER . 'efg' . PregEngine::DELIMITER . '456' . PregEngine::DELIMITER;
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(
            [$source],
            $matches,
            'The match method did not properly escape uses of the delimiter in the regular expression.'
        );
    }

    /**
     * Test regular expressions match-all functionality
     */
    public function testMatchAll()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $pregEngine->matchAll($pattern, $source, $matches);

        $this->assertEquals([['12', '34']], $matches);
    }

    /**
     * Test pattern replace.
     */
    public function testReplace()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $result = $pregEngine->replace($pattern, 'ab', $source);

        $this->assertEquals('abab', $result);
    }

    /**
     * Test pattern replace using \1 back reference format (as opposed to $1).
     */
    public function testReplaceWithBackReference()
    {
        $pregEngine = new PregEngine();
        $pattern = '(\d{2})(\d{2})';
        $source = '1234';
        $result = $pregEngine->replace($pattern, '<\1>', $source);

        $this->assertEquals('<12>', $result);
    }
}
