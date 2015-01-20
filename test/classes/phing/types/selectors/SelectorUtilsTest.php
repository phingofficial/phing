<?php

/**
 * Class SelectorUtilsTest
 *
 * Test cases for SelectorUtils
 */
class SelectorUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SelectorUtils
     */
    private $selectorUtils;

    protected function setUp()
    {
        $this->selectorUtils = SelectorUtils::getInstance();
    }

    /**
     * Inspired by @link http://www.phing.info/trac/ticket/796
     */
    public function testDoNotIncludeSelfWhenMatchingSubdirectoriesAndFiles()
    {
        $result = $this->selectorUtils->matchPath("**/*", "");
        $this->assertFalse($result);
    }

    public function testUseDotToIncludeSelf()
    {
        // See http://www.phing.info/trac/ticket/724
        $result = $this->selectorUtils->matchPath(".", "");
        $this->assertTrue($result);
    }

}
