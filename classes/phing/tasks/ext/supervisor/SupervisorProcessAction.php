<?php

class SupervisorProcessAction {

    protected $action = '';
    protected $name = '';
    protected $failonerror = null;

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isFailonerror()
    {
        return $this->failonerror;
    }

    /**
     * @param boolean $failonerror
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = $failonerror;
    }

}