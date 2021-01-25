<?php

use Phing\Task\System\Condition\Condition;

class TestCondition implements Condition
{
    private $foo = null;

    public function setFoo($value)
    {
        $this->foo = $value;
    }

    public function evaluate()
    {
        return ($this->foo == "bar");
    }
}
