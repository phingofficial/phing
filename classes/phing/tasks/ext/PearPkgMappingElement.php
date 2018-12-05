<?php

/**
 * Sub-element of <mapping>.
 *
 * @package  phing.tasks.ext
 */
class PearPkgMappingElement
{
    private $key;
    private $value;
    private $elements = [];

    /**
     * @param $v
     */
    public function setKey($v)
    {
        $this->key = $v;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Returns either the simple value or
     * the calculated value (array) of nested elements.
     * @return mixed
     */
    public function getValue()
    {
        if (!empty($this->elements)) {
            $value = [];
            foreach ($this->elements as $el) {
                if ($el->getKey() !== null) {
                    $value[$el->getKey()] = $el->getValue();
                } else {
                    $value[] = $el->getValue();
                }
            }

            return $value;
        } else {
            return $this->value;
        }
    }

    /**
     * Handles nested <element> tags.
     */
    public function createElement()
    {
        $e = new PearPkgMappingElement();
        $this->elements[] = $e;

        return $e;
    }
}
