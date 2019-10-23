<?php

use PHPUnit\Framework\TestCase;

/**
 * Class SelectorUtilsTest
 *
 * Test cases for SelectorUtils
 */
class SelectorUtilsTest extends TestCase
{
    /**
     * @var SelectorUtils
     */
    private $selectorUtils;

    protected function setUp(): void
    {
        $this->selectorUtils = SelectorUtils::getInstance();
    }

    /**
     * Inspired by @link https://www.phing.info/trac/ticket/796
     */
    public function testDoNotIncludeSelfWhenMatchingSubdirectoriesAndFiles()
    {
        $result = $this->selectorUtils->matchPath("**/*", "");
        $this->assertFalse($result);
    }

    /**
     * Inspired by @link https://www.phing.info/trac/ticket/1264
     */
    public function testDoNotIncludePrefix()
    {
        $this->assertFalse($this->selectorUtils->matchPath("**/example.php",
            "vendor/phplot/phplot/contrib/color_range.example.php"));
    }

    /**
     * Inspired by @link https://github.com/phingofficial/phing/issues/593
     */
    public function testIncludePathsInBase()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->assertTrue($this->selectorUtils->matchPath('**\domain.ext\**', 'domain.ext\foo'));
        } else {
            $this->assertTrue($this->selectorUtils->matchPath("**/domain.ext/**", "domain.ext/foo"));
        }
    }

    public function testRemoveWhitespace()
    {
        $ret = $this->selectorUtils::removeWhitespace(" foo ");
        $this->assertEquals("foo", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\tbar\t");
        $this->assertEquals("bar", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\nfoo\t");
        $this->assertEquals("foo", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\rfoo\r");
        $this->assertEquals("foo", $ret);
    }

    /**
     * Non Existing Source File Causes Out Of Date To Return False
     *
     * @return void
     */
    public function testNonExistingSourceFileCausesOutOfDateToReturnFalse()
    {
        $sourceFile = new PhingFile("doesNotExist");
        $targetFile = new PhingFile(__FILE__);
        $ret = $this->selectorUtils::isOutOfDate($sourceFile, $targetFile, 0);
        $this->assertEquals(false, $ret);
    }

    public function testNonExistingTargetFileCausesOutOfDateToReturnTrue()
    {
        $sourceFile = new PhingFile(__FILE__);
        $targetFile = new PhingFile("doesNotExist");
        $ret = $this->selectorUtils::isOutOfDate($sourceFile, $targetFile, 0);
        $this->assertEquals(true, $ret);
    }

    /**
     * Test Granularity of isOutOfDate
     *
     * @return void
     */
    public function testOutOfDate()
    {
        $source = new PhingFile(tempnam(sys_get_temp_dir(), 'src'));
        sleep(3);
        $target = new PhingFile(tempnam(sys_get_temp_dir(), 'tgt'));
        $ret = $this->selectorUtils::isOutOfDate($source, $target, 20);
        $this->assertEquals(false, $ret);
    }
}
