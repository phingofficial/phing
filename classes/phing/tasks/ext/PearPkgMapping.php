<?php

/**
 * Handles complex options <mapping> elements which are hashes (assoc arrays).
 *
 * @package phing.tasks.ext
 */
class PearPkgMapping
{
    private $name;
    private $elements = [];

    /**
     * @param $v
     */
    public function setName($v)
    {
        $this->name = $v;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PearPkgMappingElement
     */
    public function createElement()
    {
        $e = new PearPkgMappingElement();
        $this->elements[] = $e;

        return $e;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns the PHP hash or array of hashes (etc.) that this mapping represents.
     *
     * @return array
     */
    public function getValue()
    {
        $value = [];
        foreach ($this->getElements() as $el) {
            if ($el->getKey() !== null) {
                $value[$el->getKey()] = $el->getValue();
            } else {
                $value[] = $el->getValue();
            }
        }

        return $value;
    }
}
