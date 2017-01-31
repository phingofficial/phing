<?php

/**
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id$
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseParameter extends DataType
{
    private $name;
    private $value;

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param Project $p
     * @return string
     * @throws BuildException
     */
    public function getCommandline(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getCommandline($p);
        }

        return sprintf("--%s=%s", $this->name, escapeshellarg($this->value));
    }

    /**
     * @param Project $p
     * @return mixed
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        if (!$this->checked) {
            $stk = [];
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }

        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof LiquibaseParameter)) {
            throw new BuildException($this->ref->getRefId() . " doesn't denote a LiquibaseParameter");
        } else {
            return $o;
        }
    }
}
