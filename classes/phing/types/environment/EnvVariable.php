<?php

/**
 * representation of a single env value
 */
class EnvVariable
{

    /**
     * env key and value pair; everything gets expanded to a string
     * during assignment
     */
    private $key, $value;

    /**
     * set the key
     * @param string $key string
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * set the value
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * key accessor
     * @return string key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * value accessor
     * @return string value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * stringify path and assign to the value.
     * The value will contain all path elements separated by the appropriate
     * separator
     * @param Path $path
     */
    public function setPath(Path $path)
    {
        $this->value = (string) $path;
    }

    /**
     * get the absolute path of a file and assign it to the value
     * @param PhingFile $file file to use as the value
     */
    public function setFile(PhingFile $file)
    {
        $this->value = $file->getAbsolutePath();
    }

    /**
     * get the assignment string
     * This is not ready for insertion into a property file without following
     * the escaping rules of the properties class.
     * @return string of the form key=value.
     * @throws BuildException if key or value are unassigned
     */
    public function getContent()
    {
        $this->validate();
        return trim($this->key) . '=' . trim($this->value);
    }

    /**
     * checks whether all required attributes have been specified.
     * @throws BuildException if key or value are unassigned
     */
    public function validate()
    {
        if ($this->key === null || $this->value === null) {
            throw new BuildException('key and value must be specified for environment variables.');
        }
    }
}
