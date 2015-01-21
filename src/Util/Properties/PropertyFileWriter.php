<?php
namespace Phing\Util\Properties;

use Phing\Io\File;
use Phing\Io\FileWriter;
use Phing\Io\IOException;

class PropertyFileWriter
{
    protected $properties;

    public function __construct(PropertySet $s)
    {
        $this->properties = $s;
    }

    /**
     * Stores current properties to specified file.
     *
     * @param File   $file   File to create/overwrite with properties.
     * @param string $header Header text that will be placed (within comments) at the top of properties file.
     *
     * @return void
     * @throws IOException - on error writing properties file.
     */
    public function store(File $file, $header = null)
    {
        // stores the properties in this object in the file denoted
        // if file is not given and the properties were loaded from a
        // file prior, this method stores them in the file used by load()
        try {
            $fw = new FileWriter($file);
            if ($header !== null) {
                $fw->write("# " . $header . PHP_EOL);
            }
            $fw->write($this->toString());
            $fw->close();
        } catch (IOException $e) {
            throw new IOException("Error writing property file: " . $e->getMessage());
        }
    }

    /**
     * Create string representation that can be written to file and would be loadable using load() method.
     *
     * Essentially this function creates a string representation of properties that is ready to
     * write back out to a properties file.  This is used by store() method.
     *
     * @return string
     */
    public function toString()
    {
        $buf = "";
        foreach ($this->properties as $property => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v)
                    $buf .= "{$property}[$k] = " . $this->outVal($v) . PHP_EOL;
            } else {
                $buf .= $property . "=" . $this->outVal($value) . PHP_EOL;
            }
        }
        return $buf;
    }

    /**
     * Process values when being written out to properties file.
     * does things like convert true => "true"
     *
     * @param mixed $val The property value (may be boolean, etc.)
     *
     * @return string
     */
    protected function outVal($val)
    {
        if ($val === true) {
            $val = "true";
        } elseif ($val === false) {
            $val = "false";
        }
        return $val;
    }


}
