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
use Phing\Io\IOException;
use Phing\Util\Properties\PropertySet;
use Phing\Util\Properties\PropertySetImpl;
use Phing\Util\Properties\PropertyFileReader;
use Phing\Util\Properties\PropertyFileWriter;

/**
 * A class for reading and writing property files.
 *
 * This class has been used in the past by various clients (not only
 * within Phing) to read (and in rare cases write) property files. It
 * is now implemented as a facade that exhibits the "old" behaviour,
 * most notably early and transparent ${} placeholder expansion.
 *
 * As it is often used stand-alone, it has no notion of "projects" or other
 * things that might provide property values for expansion. So it might
 * happen that property values returned from this class still contain
 * ${} placeholders that can only be meaningfully resolved at a later
 * stage.
 *
 * @package    phing.system.util
 * @version    $Id$
 */
class Properties implements IteratorAggregate
{
    /** @var PropertySet */
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
        $this->properties = new PropertySetImpl();

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
     * @param File   $file    The property file to read.
     * @param string $section (Optional) The section to process.
     *
     * @return void
     * @throws IOException - if unable to read file.
     */
    public function load(File $file, $section = null)
    {
        $r = new PropertyFileReader($this->properties);
        $r->load($file, $section);

        $this->file = $file;
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
        $w = new PropertyFileWriter($this->properties);
        return $w->toString();
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

        $w = new PropertyFileWriter($this->properties);
        $w->store($file, $header);
    }

    /**
     * Returns a copy of the internal PropertySet.
     *
     * This method exists for BC reasons. ${}-style property references are not expanded.
     *
     * @return array
     */
    public function getProperties()
    {
        return iterator_to_array($this->properties);
    }

    /**
     * Returns the PropertySet used internally.
     *
     * @return PropertySet
     */
    public function getPropertySet()
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
        if (!isset($this->properties[$prop])) {
            return null;
        }

        return $this->properties[$prop];
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
        return $this->put($key, $value);
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
        $oldValue = $this->get($key);
        $this->properties[$key] = $value;
        return $oldValue;
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
        if (($oldValue = $this->get($key)) !== null) {
            $newValue = $oldValue . $delimiter . $value;
        }
        $this->put($key, $newValue);
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
        return $this->properties->isEmpty();
    }

    public function getIterator()
    {
        return $this->properties->getIterator();
    }
}
