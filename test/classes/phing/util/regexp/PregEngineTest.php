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
 *
 * @package phing.util
 */

/**
 * Class PregEngineTest
 *
 * Test cases for phing/util/regexp/PregEngine
 */
class PregEngineTest extends PHPUnit_Framework_TestCase {

    /**
     * Test setting of the ignore-case flag.
     */
    public function testSetIgnoreCase()
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getIgnoreCase());

        $pregEngine->setIgnoreCase(true);
        $this->assertTrue($pregEngine->getIgnoreCase());
        $this->assertEquals('i', $pregEngine->getModifiers());

        $pregEngine->setIgnoreCase(false);
        $this->assertFalse($pregEngine->getIgnoreCase());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test setting of the multiline flag.
     */
    public function testSetMultiline()
    {
        $pregEngine = new PregEngine();
        $this->assertNull($pregEngine->getMultiline());

        $pregEngine->setMultiline(true);
        $this->assertTrue($pregEngine->getMultiline());
        $this->assertEquals('s', $pregEngine->getModifiers());

        $pregEngine->setMultiline(false);
        $this->assertFalse($pregEngine->getMultiline());
        $this->assertSame('', $pregEngine->getModifiers());
    }

    /**
     * Test setting of modifiers
     */
    public function testSetModifiers()
    {
        $pregEngine = new PregEngine();
        $this->assertSame('', $pregEngine->getModifiers());

        $pregEngine->setModifiers('guug');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers() , 'u'), 'Duplicate modifier characters should be reduced to one.');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers() , 'g'), 'Duplicate modifier characters should be reduced to one.');

        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('iii');
        $pregEngine->setIgnoreCase(false);
        $this->assertEquals(0, substr_count($pregEngine->getModifiers() , 'i'), 'Modifier character for ignoring case should be removed when setting ignoreCase to FALSE.');
    }

    /**
     * Test regular expressions match functionality
     */
    public function testMatch()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(array('12'), $matches);

        $pregEngine = new PregEngine();
        $pattern = '/';
        $source = '/';
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(array($source), $matches);

        $pregEngine = new PregEngine();
        $pattern = '\\\\\\\\`abc\\\\\\`123\\\\`efg\\`456`';
        $source = '\\\\`abc\\`123\\`efg`456`';
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(array($source), $matches, 'The match method did not properly escape uses of the delimiter in the regular expression.');
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

        $this->assertEquals(array(array('12','34')), $matches);
    }

    /**
     * Test regular expression replace functionality
     */
    public function testReplace()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $result = $pregEngine->replace($pattern, 'ab', $source);

        $this->assertEquals('abab', $result);

        $pregEngine = new PregEngine();
        $pattern = '(\d{2})(\d{2})';
        $source = '1234';
        $result = $pregEngine->replace($pattern, '<\1>', $source);

        $this->assertEquals('<12>', $result);
    }
}
 
