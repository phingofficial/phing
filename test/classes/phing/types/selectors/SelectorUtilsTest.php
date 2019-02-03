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

    protected function setUp(): void    {
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
        $this->assertFalse($this->selectorUtils->matchPath("**/example.php", "vendor/phplot/phplot/contrib/color_range.example.php"));
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
}
