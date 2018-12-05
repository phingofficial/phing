<?php

/**
 * "Inner" class for IfTask.
 * This class has same basic structure as the IfTask, although of course it doesn't support <else> tags.
 *
 * @package phing.tasks.system
 */
class ElseIfTask extends ConditionBase
{
    private $thenTasks = null;

    /**
     * @param SequentialTask $t
     * @throws BuildException
     */
    public function addThen(SequentialTask $t)
    {
        if ($this->thenTasks != null) {
            throw new BuildException("You must not nest more than one <then> into <elseif>");
        }
        $this->thenTasks = $t;
    }

    /**
     * @throws BuildException
     * @return boolean
     */
    public function evaluate()
    {
        if ($this->countConditions() > 1) {
            throw new BuildException("You must not nest more than one condition into <elseif>");
        }
        if ($this->countConditions() < 1) {
            throw new BuildException("You must nest a condition into <elseif>");
        }

        $conditions = $this->getConditions();
        $c = $conditions[0];

        return $c->evaluate();
    }

    /**
     *
     */
    public function main()
    {
        if ($this->thenTasks != null) {
            $this->thenTasks->main();
        }
    }
}
