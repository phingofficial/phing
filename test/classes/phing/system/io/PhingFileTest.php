<?php

class PhingFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PhingFile
     */
    private $file;

    protected function setUp(): void
    {
        $this->file = new PhingFile(__FILE__);
    }

    public function testPathInsideBasedir()
    {
        $this->assertEquals(basename(__FILE__), $this->file->getPathWithoutBase(__DIR__));
    }

    public function testPathOutsideBasedir()
    {
        $this->assertEquals(__FILE__, $this->file->getPathWithoutBase("/foo/bar"));
    }
}
