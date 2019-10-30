<?php

class ParameterUnitTest extends \PHPUnit\Framework\TestCase
{
    private $parameter;

    protected function setUp(): void
    {
        $this->parameter = new Parameter();
    }

    public function testSetName()
    {
        $this->parameter->setName(1);
        $this->assertEquals("1", $this->parameter->getName());
        $this->parameter->setName("foo");
        $this->assertEquals("foo", $this->parameter->getName());
    }
    public function testSetType()
    {
        $this->parameter->setType(1);
        $this->assertEquals("1", $this->parameter->getType());
        $this->parameter->setType("foo");
        $this->assertEquals("foo", $this->parameter->getType());
    }
    public function testSetValue()
    {
        $this->parameter->setValue(1);
        $this->assertEquals("1", $this->parameter->getValue());
        $this->parameter->setValue("foo");
        $this->assertEquals("foo", $this->parameter->getValue());
    }
    public function testGetParamsNoneSet()
    {
        $params = $this->parameter->getParams();
        $this->assertEquals([], $params);
    }
    public function testCreateParamGetParams()
    {
        $param = $this->parameter->createParam();
        $class = get_class($param);
        $this->assertEquals("Parameter", $class);
        $params = $this->parameter->getParams();
        $this->assertNotEquals([], $params);
    }
    public function testSetListeningValue()
    {
        $slot = new RegisterSlot("key");
        $slot->setValue("value1");
        $this->parameter->setListeningValue($slot);
        $this->assertEquals("value1", $this->parameter->getValue());
    }
}
