<?php

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */
use Phing\Io\File;
use Phing\Io\FileWriter;
use Phing\Io\IOException;
use Phing\PropertySet;


/**
 * Convenience class for reading and writing property files.
 *
 * FIXME
 *        - Add support for arrays (separated by ',')
 *
 * @package    phing.system.util
 * @version $Id$
 */
class Properties
{

    private $properties;

    /**
     * @var File
     */
    private $file = null;

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct($properties = null)
    {
        $this->properties = new PropertySet();

        if (is_array($properties)) {
            foreach ($properties as $key => $value) {
                $this->setProperty($key, $value);
            }
        }
    }

    /**
     * Load properties from a file.
     *
     * Does not try to expand ${}-style property references in any way.
     *
     * @param  File  $file
     * @param string $section (Optional) The property file section to read.
     *
     * @return void
     * @throws IOException - if unable to read file.
     */
    public function load(File $file, $section = null)
    {
        if ($file->canRead()) {
            $this->parse($file->getPath(), $section);

            $this->file = $file;
        } else {
            throw new IOException("Can not read file " . $file->getPath());
        }

    }

    /**
     * Replaces parse_ini_file() or better_parse_ini_file().
     * Saves a step since we don't have to parse and then check return value
     * before throwing an error or setting class properties.
     *
     * @param string $filePath
     * @param string  $section The property file section to parse
     *
     * @throws IOException
     * @internal param bool $processSections Whether to honor [SectionName] sections in INI file.
     * @return array   Properties loaded from file (no prop replacements done yet).
     */
    protected function parse($filePath, $section = null)
    {
        $section = (string) $section;

        // load() already made sure that file is readable
        // but we'll double check that when reading the file into
        // an array

        if (($lines = @file($filePath)) === false) {
            throw new IOException("Unable to parse contents of $filePath");
        }

        // concatenate lines ending with backslash
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; $i++) {
            if (substr($lines[$i], -2, 1) === '\\') {
                $lines[$i + 1] = substr($lines[$i], 0, -2) . ltrim($lines[$i + 1]);
                $lines[$i] = '';
            }
        }

        $currentSection = '';
        $sect = array($currentSection => array(), $section => array());
        $depends = array();

        foreach ($lines as $l) {

            $l = preg_replace('/(#|;).*$/', '', $l);

            if (!($l = trim($l))) {
                continue;
            }

            if (preg_match('/^\[(\w+)(?:\s*:\s*(\w+))?\]$/', $l, $matches)) {
                $currentSection = $matches[1];
                $sect[$currentSection] = array();
                if (isset($matches[2])) {
                    $depends[$currentSection] = $matches[2];
                }
                continue;
            }

            $pos = strpos($l, '=');
            $name = trim(substr($l, 0, $pos));
            $value = $this->inVal(trim(substr($l, $pos + 1)));

            /*
             * Take care: Property file may contain identical keys like
             * a[] = first
             * a[] = second
             */
            $sect[$currentSection][] = array($name, $value);
        }

        $dependencyOrder = array();
        while ($section) {
            array_unshift($dependencyOrder, $section);
            $section = isset($depends[$section]) ? $depends[$section] : '';
        }
        array_unshift($dependencyOrder, '');

        foreach ($dependencyOrder as $section) {
            foreach ($sect[$section] as $def) {
                list ($name, $value) = $def;
                $this->setProperty($name, $value);
            }
        }
    }

    /**
     * Process values when being read in from properties file.
     * does things like convert "true" => true
     * @param  string $val Trimmed value.
     * @return mixed  The new property value (may be boolean, etc.)
     */
    protected function inVal($val)
    {
        if ($val === "true") {
            $val = true;
        } elseif ($val === "false") {
            $val = false;
        }

        return $val;
    }

    /**
     * Process values when being written out to properties file.
     * does things like convert true => "true"
     * @param  mixed  $val The property value (may be boolean, etc.)
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
        foreach ($this->properties as $key => $item) {
            $buf .= $key . "=" . $this->outVal($item) . PHP_EOL;
        }

        return $buf;
    }

    /**
     * Stores current properties to specified file.
     *
     * @param  File   $file   File to create/overwrite with properties.
     * @param  string      $header Header text that will be placed (within comments) at the top of properties file.
     * @return void
     * @throws IOException - on error writing properties file.
     */
    public function store(File $file = null, $header = null)
    {
        if ($file == null) {
            $file = $this->file;
        }

        if ($file == null) {
            throw new IOException("Unable to write to empty filename");
        }

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
     * Returns the internal PropertySet.
     *
     * ${}-style property references are not expanded.
     *
     * @return PropertySet
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get value for specified property. ${}-style property references are not expanded.
     *
     * This is the same as get() method.
     *
     * @param  string $prop The property name (key).
     * @return mixed
     * @see get()
     */
    public function getProperty($prop)
    {
        return $this->get($prop);
    }

    /**
     * Get value for specified property. ${}-style property references are not expanded.
     *
     * This function exists to provide a hashtable-like interface for
     * properties.
     *
     * @param  string $prop The property name (key).
     * @return mixed
     * @see getProperty()
     */
    public function get($prop)
    {
        return $this->getProperty($prop);
    }

    /**
     * Set the value for a property.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed  Old property value or NULL if none was set.
     */
    public function setProperty($key, $value)
    {
        $oldValue = null;
        if (isset($this->properties[$key])) {
            $oldValue = $this->properties[$key];
        }
        $this->properties[$key] = $value;

        return $oldValue;
    }

    /**
     * Set the value for a property.
     * This function exists to provide hashtable-lie
     * interface for properties.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function put($key, $value)
    {
        return $this->setProperty($key, $value);
    }

    /**
     * Appends a value to a property if it already exists with a delimiter
     *
     * If the property does not, it just adds it.
     *
     * @param string $key
     * @param mixed  $value
     * @param string $delimiter
     */
    public function append($key, $value, $delimiter = ',')
    {
        $newValue = $value;
        if (isset($this->properties[$key]) && !empty($this->properties[$key])) {
            $newValue = $this->properties[$key] . $delimiter . $value;
        }
        $this->properties[$key] = $newValue;
    }

    /**
     * Same as keys() function, returns an array of property names.
     * @return array
     */
    public function propertyNames()
    {
        return $this->keys();
    }

    /**
     * Whether loaded properties array contains specified property name.
     * @param $key
     * @return boolean
     */
    public function containsKey($key)
    {
        return isset($this->properties[$key]);
    }

    /**
     * Returns properties keys.
     * Use this for foreach () {} iterations, as this is
     * faster than looping through property values.
     * @return array
     */
    public function keys()
    {
        return $this->properties->keys();
    }

    /**
     * Whether properties list is empty.
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->properties);
    }
}
