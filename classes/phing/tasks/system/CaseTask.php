<?php

/**
 * "Inner" class for SwitchTask.
 *
 * @package phing.tasks.system
 */
class CaseTask extends SequentialTask
{
    /**
     * @var mixed $value
     */
    private $value = null;

    /**
     * @param $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function main()
    {
        /**
         * @var Task $task
         */
        foreach ($this->nestedTasks as $task) {
            $task->perform();
        }
    }
}
