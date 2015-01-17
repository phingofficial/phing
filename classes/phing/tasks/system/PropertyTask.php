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
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Io\StringReader;
use Phing\Io\Util\FileUtils;
use Phing\Project;
use Phing\Task;
use Phing\Util\StringHelper;

/*
  TODO:
    Create a better base class for PropertyTask and XmlPropertyTask that
    only contains common functionality (instead of inheriting from PropertyTask).
*/

/**
 * Task for setting properties in buildfiles.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @author    Matthias Pigulla <mp@webfactory.de>
 * @version   $Id$
 * @package   phing.tasks.system
 */
class PropertyTask extends Task
{

    /** name of the property */
    protected $name;

    /** value of the property */
    protected $value;

    protected $reference;
    
    protected $env; // Environment
    protected $file;
    protected $ref;
    protected $prefix;
    protected $section;
    protected $fallback;

    /** Whether to force overwrite of existing property. */
    protected $override = false;

    /** Whether property should be treated as "user" property. */
    protected $userProperty = false;

    /**
     * All filterchain objects assigned to this task
     */
    protected $filterChains = array();

    /** Whether to log messages as INFO or VERBOSE  */
    protected $logOutput = true;

    /**
     * Sets a the name of current property component
     * @param $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /** Get property component name. */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a the value of current property component.
     * @param    mixed      Value of name, all scalars allowed
     */
    public function setValue($value)
    {
        $this->value = (string) $value;
    }

    /**
     * Sets value of property to CDATA tag contents.
     * @param $value
     * @internal param string $values
     * @since 2.2.0
     */
    public function addText($value)
    {
        $this->setValue($value);
    }

    /** Get the value of current property component. */
    public function getValue()
    {
        return $this->value;
    }

    /** Set a file to use as the source for properties.
     * @param $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }
        $this->file = $file;
    }

    /** Get the PhingFile that is being used as property source. */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param Reference $ref
     */
    public function setRefid(Reference $ref)
    {
        $this->reference = $ref;
    }

    public function getRefid()
    {
        return $this->reference;
    }

    /**
     * Prefix to apply to properties loaded using <code>file</code>.
     * A "." is appended to the prefix if not specified.
     * @param  string $prefix prefix string
     * @return void
     * @since 2.0
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        if (!StringHelper::endsWith(".", $prefix)) {
            $this->prefix .= ".";
        }
    }

    /**
     * @return string
     * @since 2.0
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Section to load when using <code>file</code>.
     *
     * Only properties from this section and inherited sections will be
     * loaded.
     *
     * @param string $sectionName Name of the section to load.
     */
    public function setSection($sectionName)
    {
        $this->section = $sectionName;
    }

    /**
     * Get the name of the section to load.
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * the prefix to use when retrieving environment variables.
     * Thus if you specify environment="myenv"
     * you will be able to access OS-specific
     * environment variables via property names "myenv.PATH" or
     * "myenv.TERM".
     * <p>
     * Note that if you supply a property name with a final
     * "." it will not be doubled. ie environment="myenv." will still
     * allow access of environment variables through "myenv.PATH" and
     * "myenv.TERM". This functionality is currently only implemented
     * on select platforms. Feel free to send patches to increase the number of platforms
     * this functionality is supported on ;).<br>
     * Note also that properties are case sensitive, even if the
     * environment variables on your operating system are not, e.g. it
     * will be ${env.Path} not ${env.PATH} on Windows 2000.
     * @param prefix $env
     * @internal param prefix $env
     */
    public function setEnvironment($env)
    {
        $this->env = (string) $env;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    /**
     * Set whether this is a user property (ro).
     * This is deprecated in Ant 1.5, but the userProperty attribute
     * of the class is still being set via constructor, so Phing will
     * allow this method to function.
     * @param boolean $v
     */
    public function setUserProperty($v)
    {
        $this->userProperty = (boolean) $v;
    }

    /**
     * @return bool
     */
    public function getUserProperty()
    {
        return $this->userProperty;
    }

    /**
     * @param $v
     */
    public function setOverride($v)
    {
        $this->override = (boolean) $v;
    }

    /**
     * @return bool
     */
    public function getOverride()
    {
        return $this->override;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return (string) $this->value;
    }

    /**
     * @param Project $p
     */
    public function setFallback($p)
    {
        $this->fallback = $p;
    }

    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Creates a filterchain
     *
     * @return object The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /**
     * @param $logOutput
     */
    public function setLogoutput($logOutput)
    {
        $this->logOutput = (bool) $logOutput;
    }

    /**
     * @return bool
     */
    public function getLogoutput()
    {
        return $this->logOutput;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     */
    public function main()
    {
        if ($this->name !== null) {
            if ($this->value === null && $this->reference === null) {
                throw new BuildException("You must specify value or refid with the name attribute", $this->getLocation(
                ));
            }
        } else {
            if ($this->file === null && $this->env === null) {
                throw new BuildException("You must specify file or environment when not using the name attribute", $this->getLocation(
                ));
            }
        }

        if ($this->file === null && $this->prefix !== null) {
            throw new BuildException("Prefix is only valid when loading from a file.", $this->getLocation());
        }

        if ($this->file === null && $this->section !== null) {
            throw new BuildException("Section is only valid when loading from a file.", $this->getLocation());
        }

        if (($this->name !== null) && ($this->value !== null)) {
            $this->addProperty($this->name, $this->value);
        }

        if ($this->file !== null) {
            $this->loadFile($this->file);
        }

        if ($this->env !== null) {
            $this->loadEnvironment($this->env);
        }

        if (($this->name !== null) && ($this->reference !== null)) {
            // get the refereced property
            try {
                $referencedObject = $this->reference->getReferencedObject($this->project);

                if ($referencedObject instanceof Exception) {
                    $reference = $referencedObject->getMessage();
                } else {
                    $reference = $referencedObject->toString();
                }

                $this->addProperty($this->name, $reference);
            } catch (BuildException $be) {
                if ($this->fallback !== null) {
                    $referencedObject = $this->reference->getReferencedObject($this->fallback);

                    if ($referencedObject instanceof Exception) {
                        $reference = $referencedObject->getMessage();
                    } else {
                        $reference = $referencedObject->toString();
                    }
                    $this->addProperty($this->name, $reference);
                } else {
                    throw $be;
                }
            }
        }
    }

    /**
     * load the environment values
     * @param string $prefix prefix to place before them
     */
    protected function loadEnvironment($prefix)
    {

        $props = new Properties();
        if (substr($prefix, strlen($prefix) - 1) == '.') {
            $prefix .= ".";
        }
        $this->log("Loading Environment $prefix", Project::MSG_VERBOSE);
        foreach ($_ENV as $key => $value) {
            $props->setProperty($prefix . '.' . $key, $value);
        }
        $this->addProperties($props);
    }

    /**
     * iterate through a set of properties,
     * resolve them then assign them
     * @param $props
     * @throws \Phing\Exception\BuildException
     */
    protected function addProperties($props)
    {
        foreach ($props as $key => $value) {
            if ($this->prefix) {
                $key = "{$this->prefix}$key";
            }
            $this->addProperty($key, $value);
        }
    }

    /**
     * add a name value pair to the project property set
     * @param string $name  name of property
     * @param string $value value to set
     */
    protected function addProperty($name, $value)
    {
        if (count($this->filterChains) > 0) {
            $in = FileUtils::getChainedReader(new StringReader($value), $this->filterChains, $this->project);
            $value = $in->read();
        }

        if ($this->userProperty) {
            if ($this->project->getUserProperty($name) === null || $this->override) {
                $this->project->setInheritedProperty($name, $value);
            } else {
                $this->log("Override ignored for " . $name, Project::MSG_VERBOSE);
            }
        } else {
            if ($this->override) {
                $this->project->setProperty($name, $value);
            } else {
                $this->project->setNewProperty($name, $value);
            }
        }
    }

    /**
     * load properties from a file.
     * @param File $file
     * @throws \Phing\Exception\BuildException
     */
    protected function loadFile(File $file)
    {
        $this->log("Loading " . $file->getAbsolutePath(), $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE);
        try { // try to load file
            if ($file->exists()) {
                $this->addProperties($this->fetchPropertiesFromFile($file));
            } else {
                $this->log(
                    "Unable to find property file: " . $file->getAbsolutePath() . "... skipped",
                    Project::MSG_WARN
                );
            }
        } catch (IOException $ioe) {
            throw new BuildException("Could not load properties from file.", $ioe);
        }
    }

    protected function fetchPropertiesFromFile(File $f)
    {
        $p = new Properties();
        $p->load($f, $this->section);

        return $p->getProperties();
    }
}
