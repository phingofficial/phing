<?php
/**
 * Created by PhpStorm.
 * User: Courtney
 * Date: 4/11/2014
 * Time: 9:11 PM
 */

class PregEngineTest extends PHPUnit_Framework_TestCase {
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

    public function testSetModifiers()
    {
        $pregEngine = new PregEngine();
        $this->assertSame('', $pregEngine->getModifiers());

        $pregEngine->setModifiers('guug');
        $this->assertEquals(1, substr_count($pregEngine->getModifiers() , 'u'));
        $this->assertEquals(1, substr_count($pregEngine->getModifiers() , 'g'));

        $pregEngine = new PregEngine();
        $pregEngine->setModifiers('iii');
        $pregEngine->setIgnoreCase(false);
        $this->assertEquals(0, substr_count($pregEngine->getModifiers() , 'i'));
    }

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
        $pattern = <<<'REGEXP'
\\\\/abc\\\/123\\/efg\/456/
REGEXP;
        $source = <<<'SRC'
\\/abc\/123\/efg/456/
SRC;
        $pregEngine->match($pattern, $source, $matches);

        $this->assertEquals(array($source), $matches, 'The match method did not properly escape uses of the delimiter in the regular expression.');
    }

    public function testMatchAll()
    {
        $pregEngine = new PregEngine();
        $pattern = '\d{2}';
        $source = '1234';
        $pregEngine->matchAll($pattern, $source, $matches);

        $this->assertEquals(array(array('12','34')), $matches);
    }

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
 
