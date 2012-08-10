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

include_once 'phing/Task.php';

/**
 * Task for setting properties in buildfiles.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @author	  Matthias Pigulla <mp@webfactory.de>
 * @version   $Id$
 * @package   phing.tasks.system
 */
class PropertyTask extends Task {

	/** name of the property */
	protected $name;

	/** value of the property */
	protected $value;

	protected $reference;
	protected $env;     // Environment
	protected $file;
	protected $ref;
	protected $prefix;
	protected $section;
	protected $fallback;

	/** Whether to force overwrite of existing property. */
	protected $override = false;

    /** All filterchain objects assigned to this task */
    protected $filterChains  = array();

	/** Whether property should be treated as "user" property. */
	protected $userProperty = false;

	protected $filelists = array(); // all filelist objects assigned to this task
	protected $filesets = array(); // all filesets assigned to this task

	/**
	 * Sets a the name of current property component
	 */
	function setName($name) {
		$this->name = (string) $name;
	}

	/** Get property component name. */
	function getName() {
		return $this->name;
	}

	/**
	 * Sets a the value of current property component.
	 * @param    mixed      Value of name, all scalars allowed
	 */
	function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Sets value of property to CDATA tag contents.
	 * @param string $values
	 * @since 2.2.0
	 */
	public function addText($value) {
		$this->setValue($value);
	}

	/** Get the value of current property component. */
	function getValue() {
		return $this->value;
	}

	/** Set a file to use as the source for properties. */
	function setFile($file) {
		if (is_string($file)) {
			$file = new PhingFile($file);
		}
		$this->file = $file;
	}

	/** Get the PhingFile that is being used as property source. */
	function getFile() {
		return $this->file;
	}

	function setRefid(Reference $ref) {
		$this->reference = $ref;
	}

	function getRefid() {
		return $this->reference;
	}

	/**
	 * Prefix to apply to properties loaded using <code>file</code>.
	 * A "." is appended to the prefix if not specified.
	 * @param string $prefix prefix string
	 * @return void
	 * @since 2.0
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		if (!StringHelper::endsWith(".", $prefix)) {
			$this->prefix .= ".";
		}
	}

	/**
	 * @return string
	 * @since 2.0
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Section to load when using <code>file</code>.
	 * Only properties from this section and inherited sections will be
	 * loaded.
	 */
	public function setSection($s) {
		$this->section = $s;
	}

	/** @return string */
	public function getSection() {
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
	 * @param env prefix
	 */
	function setEnvironment($env) {
		$this->env = (string) $env;
	}

	function getEnvironment() {
		return $this->env;
	}

	/**
	 * Set whether this is a user property (ro).
	 * This is deprecated in Ant 1.5, but the userProperty attribute
	 * of the class is still being set via constructor, so Phing will
	 * allow this method to function.
	 * @param boolean $v
	 */
	function setUserProperty($v) {
		$this->userProperty = Boolean::cast($v);
	}

	function getUserProperty() {
		return $this->userProperty;
	}

	function setOverride($v) {
		$this->override = Boolean::cast($v);
	}

	function getOverride() {
		return $this->override;
	}

	function toString() {
		return (string) $this->value;
	}

	/**
	 * @param Project $p
	 */
	function setFallback($p) {
		$this->fallback = $p;
	}

	function getFallback() {
		return $this->fallback;
	}

    /**
     * Creates a filterchain
     *
     * @access public
     * @return  object  The created filterchain object
     */
    public function createFilterChain() {
        $num = array_push($this->filterChains, new FilterChain($this->project));
        return $this->filterChains[$num-1];
    }  

	public function createFileList() {
		$fl = new FileList();
		$this->filelists[] = $fl;
		return $fl;
	}

	public function createFileSet() {
		$fs = new FileSet();
		$this->filesets[] = $fs;
		return $fs;
	}

	protected function fail($msg) {
		throw new BuildException($msg, $this->getLocation());
	}

	/**
	 * set the property in the project to the value.
	 * if the task was give a file or env attribute
	 * here is where it is loaded
	 */
	public function main() {

		if ($this->name !== null || $this->env !== null) {

			if ($this->prefix !== null)
				$this->fail("Prefix is only valid when loading from a file.");

			if ($this->section !== null)
				$this->fail("Section is only valid when loading from a file.");
		}

		if ($this->name !== null) {
			// Set a single property value with a given name

			if ($this->value !== null) {
				$this->addProperty($this->name, $this->value);
				return;
			}

			if ($this->ref !== null) {
				// get the refereced property
				try {
					$this->addProperty($this->name, $this->reference->getReferencedObject($this->project)->toString());
				} catch (BuildException $be) {
					if ($this->fallback !== null) {
						$this->addProperty($this->name, $this->reference->getReferencedObject($this->fallback)->toString());
					} else {
						throw $be;
					}
				}
				return;
			}

			$this->fail("You must specify value or refid with the name attribute");
		}

		if ($this->env !== null) {
			// Load environment variables
			$this->loadEnvironment($this->env);
			return;
		}

		if ($this->file === null && !$this->filelists && !$this->filesets)
			$this->fail("You must specify name and value, environment, file or provide a FileList or FileSet.");

		$this->loadFromFiles();
	}

	/**
	 * load the environment values
	 * @param string $prefix prefix to place before them
	 */
	protected function loadEnvironment($prefix) {

		require_once('phing/util/properties/PropertySetImpl.php');
		$props = new PropertySetImpl();

		if ( substr($prefix, strlen($prefix)-1) == '.' ) {
			$prefix .= ".";
		}
		$this->log("Loading Environment $prefix", Project::MSG_VERBOSE);
		foreach($_ENV as $key => $value) {
			$props["$prefix.$key"] = $value;
		}
		$this->addProperties($props);
	}

	/**
	 * iterate through a set of properties,
	 * resolve them then assign them
	 */
	protected function addProperties(PropertySet $props) {
		foreach($props as $key => $value) {
			if ($this->prefix) $key = "{$this->prefix}$key";
            if (is_array($value))
                foreach ($value as $k => $v) {
                    if (is_numeric($k))
                        $this->addProperty("{$key}[]", $v);
                    else
                        $this->addProperty("{$key}[{$k}]", $v);
                }

            else
               	$this->addProperty($key, $value);
		}
	}

	/**
	 * add a name value pair to the project property set
	 * @param string $name name of property
	 * @param string $value value to set
	 */
	protected function addProperty($name, $value) {
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
	 * @param PhingFile $file
	 */
	protected function loadFromFiles() {
		require_once('phing/util/properties/PropertySetImpl.php');
		$p = new PropertySetImpl();

		if ($this->file)
			$this->processFile($this->file, $p);

		if ($this->filelists) {
			foreach ($this->filelists as $fl) {
				$fromDir  = $fl->getDir($this->project);
				foreach ($fl->getFiles($this->project) as $srcFile)
					$this->processFile(new PhingFile("$fromDir/$srcFile"), $p);
			}
		}

		if ($this->filesets) {
	        foreach($this->filesets as $fs) {
	        	$fromDir = $fs->getDir($this->project);
	        	$ds = $fs->getDirectoryScanner($this->project);
	        	foreach ($ds->getIncludedFiles() as $srcFile)
	        		$this->processFile(new PhingFile("$fromDir/$srcFile"), $p);
	        }
		}

		$this->addProperties($p);
	}

	protected function processFile(PhingFile $file, PropertySet $p) {
		$this->log("Loading properties from ". $file->getAbsolutePath(), Project::MSG_INFO);
		try { // try to load file
			if ($file->exists()) {
				$this->fetchPropertiesFromFile($file, $p);
			} else {
				$this->log("Unable to find property file: ". $file->getAbsolutePath() ."... skipped", Project::MSG_WARN);
			}
		} catch (IOException $ioe) {
			throw new BuildException("Could not load properties from file.", $ioe);
		}
	}

	protected function fetchPropertiesFromFile(PhingFile $f, PropertySet $p) {
		require_once('phing/util/properties/PropertyFileReader.php');

		// do not use the "Properties" façade to defer property expansion
		// (the Project will take care of it)

		$r = new PropertyFileReader($p);
		$r->load($f, $this->section);
	}
}
