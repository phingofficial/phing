<?php

class VersionCompareConditionTest extends \PHPUnit\Framework\TestCase
{
    protected $_condition;

    public function setUp(): void
    {
        $this->_condition = new VersionCompareCondition();
    }

    public function testDefaultCompareIsFalseForSmallerRevision()
    {
        $this->_condition->setVersion('1.2.7');
        $this->_condition->setDesiredVersion('1.3');
        $this->assertFalse($this->_condition->evaluate());
    }

    public function testDefaultCompareIsTrueForBiggerRevision()
    {
        $this->_condition->setVersion('1.6.2');
        $this->_condition->setDesiredVersion('1.3');
        $this->assertTrue($this->_condition->evaluate());
    }

    public function testDefaultCompareIsTrueForSameRevision()
    {
        $this->_condition->setVersion('1.3');
        $this->_condition->setDesiredVersion('1.3');
        $this->assertTrue($this->_condition->evaluate());
    }

    public function testCanUseDifferentOperator()
    {
        $this->_condition->setVersion('1.2.7');
        $this->_condition->setDesiredVersion('1.3');
        $this->_condition->setOperator('<=');
        $this->assertTrue($this->_condition->evaluate());
    }

    public function testUseDebugMode()
    {
        $this->_condition->setVersion('1.2.7');
        $this->_condition->setDesiredVersion('1.3');
        $this->_condition->setDebug(true);
        $this->expectOutputString('Assertion that 1.2.7 >= 1.3 failed' . PHP_EOL);
        $this->_condition->evaluate();
    }

    /**
     * @expectedException BuildException
     */
    public function testCanNotUseUnsupportedOperator()
    {
        $this->_condition->setOperator('<<<<');
    }
}
