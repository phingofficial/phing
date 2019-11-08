<?php

class ParameterUnitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Parameter
     */
    private $parameter;

    protected function setUp(): void
    {
        $this->parameter = new Parameter();
    }

    public function testSetName()
    {
        $this->parameter->setName(1);
        self::assertEquals("1", $this->parameter->getName());
        $this->parameter->setName("foo");
        self::assertEquals("foo", $this->parameter->getName());
    }
    public function testSetType()
    {
        $this->parameter->setType(1);
        self::assertEquals("1", $this->parameter->getType());
        $this->parameter->setType("foo");
        self::assertEquals("foo", $this->parameter->getType());
    }
    public function testSetValue()
    {
        $this->parameter->setValue(1);
        self::assertEquals("1", $this->parameter->getValue());
        $this->parameter->setValue("foo");
        self::assertEquals("foo", $this->parameter->getValue());
    }
    public function testGetParamsNoneSet()
    {
        $params = $this->parameter->getParams();
        self::assertEquals([], $params);
    }
    public function testCreateParamGetParams()
    {
        $param = $this->parameter->createParam();
        $class = get_class($param);
        self::assertEquals("Parameter", $class);
        $params = $this->parameter->getParams();
        $this->assertNotEquals([], $params);
    }
    public function testSetListeningValue()
    {
        $slot = new RegisterSlot("key");
        $slot->setValue("value1");
        $this->parameter->setListeningValue($slot);
        self::assertEquals("value1", $this->parameter->getValue());
    }
}
