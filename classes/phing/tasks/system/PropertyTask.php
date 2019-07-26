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

/**
 * Task for setting properties in buildfiles.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.system
 */
class PropertyTask extends Task
{
    use FilterChainAware;

    /**
     * @var string name of the property
     */
    protected $name;

    /**
     * @var mixed value of the property
     */
    protected $value;

    /**
     * @var Reference
     */
    protected $reference;

    /**
     * @var string environment
     */
    protected $env;

    /**
     * @var PhingFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Project
     */
    protected $fallback;

    /**
     * Whether to force overwrite of existing property.
     */
    protected $override = false;

    /**
     * Whether property should be treated as "user" property.
     */
    protected $userProperty = false;

    /**
     * Whether to log messages as INFO or VERBOSE
     */
    protected $logOutput = true;

    /**
     * @var FileParserFactoryInterface
     */
    private $fileParserFactory;

    /**
     * Whether a warning should be displayed when the property ismissing.
     */
    private $quiet = false;

    /**
     * @param FileParserFactoryInterface $fileParserFactory
     */
    public function __construct(FileParserFactoryInterface $fileParserFactory = null)
    {
        parent::__construct();
        $this->fileParserFactory = $fileParserFactory != null ? $fileParserFactory : new FileParserFactory();
    }

    /**
     * Sets a the name of current property component
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Get property component name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a the value of current property component.
     *
     * @param mixed $value Value of name, all scalars allowed
     */
    public function setValue($value)
    {
        $this->value = (string) $value;
    }

    /**
     * Sets value of property to CDATA tag contents.
     *
     * @param    $value
     * @internal param string $values
     * @since    2.2.0
     */
    public function addText($value)
    {
        $this->setValue($value);
    }

    /**
     * Get the value of current property component.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set a file to use as the source for properties.
     *
     * @param $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new PhingFile($file);
        }
        $this->file = $file;
    }

    /**
     * Get the PhingFile that is being used as property source.
     */
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
     *
     * @param  string $prefix prefix string
     * @return void
     * @since  2.0
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
     *
     * @param string $env
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
     *
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
    public function __toString()
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
     * @param $logOutput
     */
    public function setLogoutput(bool $logOutput)
    {
        $this->logOutput = $logOutput;
    }

    /**
     * @return bool
     */
    public function getLogoutput()
    {
        return $this->logOutput;
    }

    /**
     * Set quiet mode, which suppresses warnings if chmod() fails.
     *
     * @see   setFailonerror()
     * @param $bool
     */
    public function setQuiet($bool)
    {
        $this->quiet = $bool;
    }

    /**
     * @return bool
     */
    public function getQuiet(): bool
    {
        return $this->quiet;
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
                throw new BuildException(
                    "You must specify value or refid with the name attribute",
                    $this->getLocation()
                );
            }
        } else {
            if ($this->file === null && $this->env === null) {
                throw new BuildException(
                    "You must specify file or environment when not using the name attribute",
                    $this->getLocation()
                );
            }
        }

        if ($this->file === null && $this->prefix !== null) {
            throw new BuildException("Prefix is only valid when loading from a file.", $this->getLocation());
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
                    $reference = (string) $referencedObject;
                }

                $this->addProperty($this->name, $reference);
            } catch (BuildException $be) {
                if ($this->fallback !== null) {
                    $referencedObject = $this->reference->getReferencedObject($this->fallback);

                    if ($referencedObject instanceof Exception) {
                        $reference = $referencedObject->getMessage();
                    } else {
                        $reference = (string) $referencedObject;
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
     *
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
     *
     * @param  $props
     * @throws BuildException
     */
    protected function addProperties($props)
    {
        $this->resolveAllProperties($props);
        foreach ($props->keys() as $name) {
            $value = $props->getProperty($name);
            $v = $this->project->replaceProperties($value);
            if ($this->prefix !== null) {
                $name = $this->prefix . $name;
            }
            $this->addProperty($name, $v);
        }
    }

    /**
     * add a name value pair to the project property set
     *
     * @param string $name name of property
     * @param string $value value to set
     */
    protected function addProperty($name, $value)
    {
        if (count($this->filterChains) > 0) {
            $in = FileUtils::getChainedReader(new StringReader($value), $this->filterChains, $this->project);
            $value = $in->read();
        }

        $ph = PropertyHelper::getPropertyHelper($this->getProject());
        if ($this->userProperty) {
            if ($ph->getUserProperty(null, $name) === null || $this->override) {
                $ph->setInheritedProperty(null, $name, $value);
            } else {
                $this->log('Override ignored for ' . $name, Project::MSG_VERBOSE);
            }
        } else {
            if ($this->override) {
                $ph->setProperty(null, $name, $value, true);
            } else {
                $ph->setNewProperty(null, $name, $value);
            }
        }
    }

    /**
     * load properties from a file.
     *
     * @param  PhingFile $file
     * @throws BuildException
     */
    protected function loadFile(PhingFile $file)
    {
        $fileParser = $this->fileParserFactory->createParser($file->getFileExtension());
        $props = new Properties(null, $fileParser);
        $this->log("Loading " . $file->getAbsolutePath(), $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE);
        try { // try to load file
            if ($file->exists()) {
                $props->load($file);
                $this->addProperties($props);
            } else {
                $this->log(
                    "Unable to find property file: " . $file->getAbsolutePath() . "... skipped",
                    $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN
                );
            }
        } catch (IOException $ioe) {
            throw new BuildException("Could not load properties from file.", $ioe);
        }
    }

    /**
     * Given a Properties object, this method goes through and resolves
     * any references to properties within the object.
     *
     * @param  Properties $props The collection of Properties that need to be resolved.
     * @throws BuildException
     * @return void
     */
    protected function resolveAllProperties(Properties $props)
    {
        foreach ($props->keys() as $name) {
            // There may be a nice regex/callback way to handle this
            // replacement, but at the moment it is pretty complex, and
            // would probably be a lot uglier to work into a preg_replace_callback()
            // system.  The biggest problem is the fact that a resolution may require
            // multiple passes.

            $value = $props->getProperty($name);
            $resolved = false;
            $resolveStack = [];

            while (!$resolved) {
                $fragments = [];
                $propertyRefs = [];

                PropertyHelper::getPropertyHelper($this->project)->parsePropertyString(
                    $value,
                    $fragments,
                    $propertyRefs
                );

                $resolved = true;
                if (count($propertyRefs) === 0) {
                    continue;
                }

                $sb = "";

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
                        throw new BuildException("Property " . $propertyName . " was circularly defined.");
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
                            $resolved = false; // parse again (could have been replaced w/ another var)
                        }
                    } else {
                        $fragment = "\${" . $propertyName . "}";
                    }

                    $sb .= $fragment;
                }

                $this->log("Resolved Property \"$value\" to \"$sb\"", Project::MSG_DEBUG);
                $value = $sb;
                $props->setProperty($name, $value);
            } // while (!$resolved)
        } // while (count($keys)
    }
}
