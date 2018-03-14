<?php

/**
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
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
        $dataTypeName = StringHelper::substring(__CLASS__, strrpos(__CLASS__, '\\') + 1);
        return $this->getCheckedRef(__CLASS__, $dataTypeName);
    }
}
