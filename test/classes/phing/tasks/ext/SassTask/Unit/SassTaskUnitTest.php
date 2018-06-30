<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SassTaskUnitTest extends TestCase
{

    /** @var SassTask */
    private $object;

    /** @var SassTaskAssert */
    private $sassTaskAssert;

    public function setUp(): void
    {
        $this->object = new SassTask();
        $this->sassTaskAssert = new SassTaskAssert();
    }

    public function testCheckDefaults(): void
    {
        $this->sassTaskAssert->assertDefaults($this->object);
    }

    public function testSetStyleCompactViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compact');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompactViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompact('yes');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompressedViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compressed');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }

    public function testSetStyleCompressedViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompressed('yes');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }
}
