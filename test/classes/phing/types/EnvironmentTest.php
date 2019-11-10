<?php

class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    private $environment;

    public function setUp(): void
    {
        $this->environment = new Environment;
    }

    public function testVariablesNull()
    {
        $count = $this->environment->getVariables();
        $this->assertNull($count);
    }

    public function testVariablesObjectIsArrayObject()
    {
        $variablesObj = $this->environment->getVariablesObject();
        $this->assertEquals("ArrayObject", get_class($variablesObj));
    }

    public function testValidateWithoutKeyAndValueSetRaisesException()
    {
        $ev = new EnvVariable();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('key and value must be specified for environment variables.');

        $ev->validate();
    }

    public function testValuesAgainstGetContent()
    {
        $ev = new EnvVariable();
        $ev->setKey(" key ");
        $ev->setValue(" value ");
        $ev->validate();
        $content = $ev->getContent();
        $this->assertEquals("key=value", $content);
    }
}
