<?php
/**
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

declare(strict_types=1);

/**
 * Variable Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class Variable extends PropertyTask
{
    /**
     * @var bool
     */
    private $remove = false;

    /**
     * Determines whether the property should be removed from the project.
     * Default is false. Once  removed, conditions that check for property
     * existence will find this property does not exist.
     *
     * @param bool $b set to true to remove the property from the project.
     *
     * @return void
     */
    public function setUnset(bool $b): void
    {
        $this->remove = $b;
    }

    /**
     * Execute this task.
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException  Description of the Exception
     * @throws Exception
     */
    public function main(): void
    {
        if ($this->remove) {
            if ($this->name === null || $this->name === '') {
                throw new BuildException("The 'name' attribute is required with 'unset'.");
            }
            $this->removeProperty($this->name);
            return;
        }
        if ($this->file === null) {
            // check for the required name attribute
            if ($this->name === null || $this->name === '') {
                throw new BuildException("The 'name' attribute is required.");
            }

            // adjust the property value if necessary -- is this necessary?
            // Doesn't Ant do this automatically?
            $this->value = $this->getProject()->replaceProperties($this->value);

            // set the property
            $this->forceProperty($this->name, $this->value);
        } else {
            if (!$this->file->exists()) {
                throw new BuildException($this->file->getAbsolutePath() . ' does not exists.');
            }
            $this->loadFile($this->file);
        }
    }

    /**
     * Remove a property from the project's property table and the userProperty table.
     * Note that Ant 1.6 uses a helper for this.
     *
     * @param string $name
     *
     * @return void
     */
    private function removeProperty(string $name): void
    {
        $properties = null;
        try {
            $properties = $this->getPropValue($this->getProject(), 'properties');
            if ($properties !== null) {
                unset($properties[$name]);
                $this->setPropValue($properties, $this->getProject(), 'properties');
            }
        } catch (Throwable $e) {
        }
        try {
            $properties = $this->getPropValue($this->getProject(), 'userProperties');
            if ($properties !== null) {
                unset($properties[$name]);
                $this->setPropValue($properties, $this->getProject(), 'userProperties');
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    private function forceProperty(string $name, $value): void
    {
        try {
            $properties = $this->getPropValue($this->getProject(), 'properties');
            if ($properties === null) {
                $this->getProject()->setUserProperty($name, $value);
            } else {
                $properties[$name] = $value;
                $this->setPropValue($properties, $this->getProject(), 'properties');
            }
        } catch (Throwable $e) {
            $this->getProject()->setUserProperty($name, $value);
        }
    }

    /**
     * Get a private property of a class
     *
     * @param mixed  $thisClass The class
     * @param string $fieldName The property to get
     *
     * @return ReflectionProperty               The property value
     *
     * @throws Exception
     */
    private function getField($thisClass, string $fieldName): ReflectionProperty
    {
        $refClazz = new ReflectionObject($thisClass);
        if (!$refClazz->hasProperty($fieldName)) {
            throw new Exception('Invalid field : ' . $fieldName);
        }

        return $refClazz->getProperty($fieldName);
    }

    /**
     * Get a private property of an object
     *
     * @param mixed  $instance  the object instance
     * @param string $fieldName the name of the field
     *
     * @return mixed an object representing the value of the field
     *
     * @throws Exception
     */
    private function getPropValue($instance, string $fieldName)
    {
        $field = $this->getField($instance, $fieldName);
        $field->setAccessible(true);
        return $field->getValue($instance);
    }

    /**
     * @param mixed  $value
     * @param mixed  $instance
     * @param string $fieldName
     *
     * @return void
     *
     * @throws Exception
     */
    private function setPropValue($value, $instance, string $fieldName): void
    {
        $field = $this->getField($instance, $fieldName);
        $field->setAccessible(true);
        $field->setValue($instance, $value);
    }

    /**
     * load variables from a file
     *
     * @param PhingFile $file file to load
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    protected function loadFile(PhingFile $file): void
    {
        $props = new Properties();
        try {
            if ($file->exists()) {
                $props->load($file);

                $this->addProperties($props);
            } else {
                $this->log(
                    'Unable to find property file: ' . $file->getAbsolutePath(),
                    Project::MSG_VERBOSE
                );
            }
        } catch (IOException $ex) {
            throw new BuildException($ex, $this->getLocation());
        }
    }

    /**
     * iterate through a set of properties, resolve them, then assign them
     *
     * @param Properties $props The feature to be added to the Properties attribute
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addProperties(Properties $props): void
    {
        $this->resolveAllProperties($props);
        foreach ($props->keys() as $name) {
            $this->forceProperty($name, $props->getProperty($name));
        }
    }

    /**
     * resolve properties inside a properties hashtable
     *
     * @param Properties $props properties object to resolve
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException  Description of the Exception
     */
    protected function resolveAllProperties(Properties $props): void
    {
        foreach ($props->keys() as $name) {
            // There may be a nice regex/callback way to handle this
            // replacement, but at the moment it is pretty complex, and
            // would probably be a lot uglier to work into a preg_replace_callback()
            // system.  The biggest problem is the fact that a resolution may require
            // multiple passes.

            $value        = $props->getProperty($name);
            $resolved     = false;
            $resolveStack = [];

            $ih = PropertyHelper::getPropertyHelper($this->project);

            while (!$resolved) {
                $fragments    = [];
                $propertyRefs = [];

                // [HL] this was ::parsePropertyString($this->value ...) ... this seems wrong
                $ih->parsePropertyString($value, $fragments, $propertyRefs);

                $resolved = true;
                if (count($propertyRefs) == 0) {
                    continue;
                }

                $sb = '';

                $j = $propertyRefs;

                foreach ($fragments as $fragment) {
                    if ($fragment !== null) {
                        $sb .= $fragment;
                        continue;
                    }

                    $propertyName = array_shift($j);
                    if (in_array($propertyName, $resolveStack)) {
                        // Should we maybe just log this as an error & move on?
                        // $this->log("Property ".$name." was circularly defined.", Project::MSG_ERR);
                        throw new BuildException('Property ' . $propertyName . ' was circularly defined.');
                    }

                    $fragment = $this->getProject()->getProperty($propertyName);
                    if ($fragment !== null) {
                        $sb .= $fragment;
                        continue;
                    }

                    if ($props->containsKey($propertyName)) {
                        $fragment = $props->getProperty($propertyName);
                        if (strpos($fragment, '${') !== false) {
                            $resolveStack[] = $propertyName;
                            $resolved       = false; // parse again (could have been replaced w/ another var)
                        }
                    } else {
                        $fragment = '${' . $propertyName . '}';
                    }

                    $sb .= $fragment;
                }

                $this->log(sprintf('Resolved Property "%s" to "%s"', $value, $sb), Project::MSG_DEBUG);
                $value = $sb;
                $props->setProperty($name, $value);

                $this->getProject()->setProperty($name, $value);
            }
        }
    }
}
