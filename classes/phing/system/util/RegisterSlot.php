<?php

/**
 * Represents a slot in the register.
 *
 * @package phing.system.util
 */
class RegisterSlot
{

    /** The name of this slot. */
    private $key;

    /** The value for this slot. */
    private $value;

    /**
     * Constructs a new RegisterSlot, setting the key to passed param.
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = (string)$key;
    }

    /**
     * Sets the key / name for this slot.
     * @param string $k
     */
    public function setKey($k)
    {
        $this->key = (string)$k;
    }

    /**
     * Gets the key / name for this slot.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the value for this slot.
     * @param mixed
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Returns the value at this slot.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Recursively implodes an array to a comma-separated string
     * @param  array $arr
     * @return string
     */
    private function implodeArray(array $arr)
    {
        $values = [];

        foreach ($arr as $value) {
            if (is_array($value)) {
                $values[] = $this->implodeArray($value);
            } else {
                $values[] = $value;
            }
        }

        return "{" . implode(",", $values) . "}";
    }

    /**
     * Returns the value at this slot as a string value.
     * @return string
     */
    public function __toString()
    {
        if (is_array($this->value)) {
            return $this->implodeArray($this->value);
        } else {
            return (string)$this->value;
        }
    }
}
