<?php

use PHPUnit\Framework\TestCase;

class ExcludesNameEntryTest extends TestCase
{
    public function setUp(): void
    {
        $this->entry = new ExcludesNameEntry();
    }
    public function testSetName()
    {
        $this->entry->setName("test");
        $this->assertEquals($this->entry->getName(), "test");
        $this->entry->setName("test2");
        $this->assertEquals($this->entry->getName(), "test2");
    }

    public function testAddText()
    {
        $this->entry->addText("test");
        $this->assertEquals($this->entry->getName(), "test");
        $this->entry->addText("test2");
        $this->assertEquals($this->entry->getName(), "test2");
    }

    public function testToString()
    {
        $this->entry->addText("test");
        $this->assertEquals("" . $this->entry, "test");
    }
}
