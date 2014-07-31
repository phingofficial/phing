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

include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/FileWriter.php';
include_once 'phing/system/io/FileParserInterface.php';
include_once 'phing/system/io/IniFileParser.php';

/**
 * Convenience class for reading and writing property files.
 * 
 * FIXME
 *        - Add support for arrays (separated by ',')
 *
 * @package    phing.system.util
 * @version $Id$
 */
class Properties {

    /**
     * @var array
     */
    private $properties = array();
    
    /**
     * @var FileParserInterface
     */
    private $fileParser;

    /**
     * @var PhingFile
     */
    private $file = null;
    
    /**
     * Constructor
     *
     * @param array $properties
     */
    function __construct($properties = NULL, FileParserInterface $fileParser = null)
    {
        $this->fileParser = $fileParser == null ? new IniFileParser() : $fileParser;

        if (is_array($properties)) {
            foreach ($properties as $key => $value) {
                $this->setProperty($key, $value);
            }
        }
    }

    /**
     * Load properties from a file.
     *
     * @param PhingFile $file
     * @return void
     * @throws IOException - if unable to read file.
     */
    function load(PhingFile $file) {
        if ($file->canRead()) {
            $this->parse($file->getPath(), false);                    

            $this->file = $file;
        } else {
            throw new IOException("Can not read file ".$file->getPath());
        }
        
    }
    
    /**
     * Parses the file given.
     *
     * @param string $filePath
     * @return array Properties loaded from file (no prop replacements done yet).
     */
    protected function parse(PhingFile $file) {
        $this->properties = $this->fileParser->parseFile($file);
    }

    
    /**
     * Process values when being written out to properties file.
     * does things like convert true => "true"
     * @param mixed $val The property value (may be boolean, etc.)
     * @return string
     */
    protected function outVal($val) {
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
    public function toString() {
        $buf = "";        
        foreach($this->properties as $key => $item) {
            $buf .= $key . "=" . $this->outVal($item) . PHP_EOL;
        }
        return $buf;    
    }
    
    /**
     * Stores current properties to specified file.
     * 
     * @param PhingFile $file File to create/overwrite with properties.
     * @param string $header Header text that will be placed (within comments) at the top of properties file.
     * @return void
     * @throws IOException - on error writing properties file.
     */
    function store(PhingFile $file = null, $header = null) {
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
                $fw->write( "# " . $header . PHP_EOL );
            }
            $fw->write($this->toString());
            $fw->close();
        } catch (IOException $e) {
            throw new IOException("Error writing property file: " . $e->getMessage());
        }                
    }
    
    /**
     * Returns copy of internal properties hash.
     * Mostly for performance reasons, property hashes are often
     * preferable to passing around objects.
     *
     * @return array
     */
    function getProperties() {
        return $this->properties;
    }
    
    /**
     * Get value for specified property.
     * This is the same as get() method.
     *
     * @param string $prop The property name (key).
     * @return mixed
     * @see get()
     */
    function getProperty($prop) {
        if (!isset($this->properties[$prop])) {
            return null;
        }
        return $this->properties[$prop];
    }

    /**
     * Get value for specified property.
     * This function exists to provide a hashtable-like interface for
     * properties.
     *
     * @param string $prop The property name (key).
     * @return mixed
     * @see getProperty()
     */    
    function get($prop) {
         if (!isset($this->properties[$prop])) {
            return null;
        }
        return $this->properties[$prop];
    }
    
    /**
     * Set the value for a property.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed Old property value or NULL if none was set.
     */
    function setProperty($key, $value) {
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
     */
    function put($key, $value) {
        return $this->setProperty($key, $value);
    }
    
    /**
     * Appends a value to a property if it already exists with a delimiter
     *
     * If the property does not, it just adds it.
     * 
     * @param string $key
     * @param mixed $value
     * @param string $delimiter
     */
    function append($key, $value, $delimiter = ',') {
        $newValue = $value;
        if (isset($this->properties[$key]) && !empty($this->properties[$key]) ) {
            $newValue = $this->properties[$key] . $delimiter . $value;
        }
        $this->properties[$key] = $newValue;
    }

    /**
     * Same as keys() function, returns an array of property names.
     * @return array
     */
    function propertyNames() {
        return $this->keys();
    }
    
    /**
     * Whether loaded properties array contains specified property name.
     * @return boolean
     */
    function containsKey($key) {
        return isset($this->properties[$key]);
    }

    /**
     * Returns properties keys.
     * Use this for foreach() {} iterations, as this is
     * faster than looping through property values.
     * @return array
     */
    function keys() {
        return array_keys($this->properties);
    }
    
    /**
     * Whether properties list is empty.
     * @return boolean
     */
    function isEmpty() {
        return empty($this->properties);
    }

}

