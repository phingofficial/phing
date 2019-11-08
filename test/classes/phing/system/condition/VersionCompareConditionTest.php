<?php

class VersionCompareConditionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VersionCompareCondition
     */
    private $condition;

    public function setUp(): void
    {
        $this->condition = new VersionCompareCondition();
    }

    public function testDefaultCompareIsFalseForSmallerRevision()
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        self::assertFalse($this->condition->evaluate());
    }

    public function testDefaultCompareIsTrueForBiggerRevision()
    {
        $this->condition->setVersion('1.6.2');
        $this->condition->setDesiredVersion('1.3');
        self::assertTrue($this->condition->evaluate());
    }

    public function testDefaultCompareIsTrueForSameRevision()
    {
        $this->condition->setVersion('1.3');
        $this->condition->setDesiredVersion('1.3');
        self::assertTrue($this->condition->evaluate());
    }

    public function testCanUseDifferentOperator()
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        $this->condition->setOperator('<=');
        self::assertTrue($this->condition->evaluate());
    }

    public function testUseDebugMode()
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        $this->condition->setDebug(true);
        $this->expectOutputString('Assertion that 1.2.7 >= 1.3 failed' . PHP_EOL);
        $this->condition->evaluate();
    }

    /**
     * @expectedException BuildException
     */
    public function testCanNotUseUnsupportedOperator()
    {
        $this->condition->setOperator('<<<<');
    }
}
