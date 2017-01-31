<?php

/**
 * Supports the <param> nested tag for PhpTask.
 *
 * @package  phing.tasks.system
 */
class FunctionParam
{
    private $val;

    /**
     * @param $v
     */
    public function setValue($v)
    {
        $this->val = $v;
    }

    /**
     * @param $v
     */
    public function addText($v)
    {
        $this->val = $v;
    }

    public function getValue()
    {
        return $this->val;
    }
}
