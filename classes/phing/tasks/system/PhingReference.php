<?php

/**
 * Helper class that implements the nested <reference>
 * element of <phing> and <phingcall>.
 *
 * @package phing.tasks.system
 */
class PhingReference extends Reference
{
    private $targetid = null;

    /**
     * Set the id that this reference to be stored under in the
     * new project.
     *
     * @param string $targetid The id under which this reference will be passed to the new project
     */
    public function setToRefid($targetid)
    {
        $this->targetid = $targetid;
    }

    /**
     * Get the id under which this reference will be stored in the new
     * project
     *
     * @return string the id of the reference in the new project.
     */
    public function getToRefid()
    {
        return $this->targetid;
    }
}
