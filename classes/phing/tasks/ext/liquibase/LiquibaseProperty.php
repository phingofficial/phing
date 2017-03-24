<?php

/**
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id$
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseProperty extends DataType
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

        return sprintf("-D%s=%s", $this->name, escapeshellarg($this->value));
    }

    /**
     * @param Project $p
     * @return mixed
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        $dataTypeName = StringHelper::substring(get_class(), strrpos(get_class(), '\\') + 1);
        return $this->getCheckedRef(get_class(), $dataTypeName);
    }
}
