<?php

class TestCondition implements Condition
{
    private $foo = null;

    public function setFoo($value)
    {
        $this->foo = $value;
    }

    public function evaluate(): bool
    {
        return ($this->foo == "bar");
    }
}
