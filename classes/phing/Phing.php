<?php
/*
 * $Id: Phing.php,v 1.51 2006/01/06 15:12:33 hlellelid Exp $
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
 
require_once 'phing/Project.php';
require_once 'phing/ProjectComponent.php';
require_once 'phing/Target.php';
require_once 'phing/Task.php';

include_once 'phing/BuildException.php';
include_once 'phing/BuildEvent.php';

include_once 'phing/parser/Location.php';
include_once 'phing/parser/ExpatParser.php';
include_once 'phing/parser/AbstractHandler.php';
include_once 'phing/parser/ProjectConfigurator.php';
include_once 'phing/parser/RootHandler.php';
include_once 'phing/parser/ProjectHandler.php';
include_once 'phing/parser/TaskHandler.php';
include_once 'phing/parser/TargetHandler.php';
include_once 'phing/parser/DataTypeHandler.php';
include_once 'phing/parser/NestedElementHandler.php';

include_once 'phing/system/util/Properties.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/system/util/Register.php';

/**
 * Entry point into Phing.  This class handles the full lifecycle of a build -- from 
 * parsing & handling commandline arguments to assembling the project to shutting down
 * and cleaning up in the end.
 *
 * If you are invoking Phing from an external application, this is still
 * the class to use.  Your applicaiton can invoke the start() method, passing
 * any commandline arguments or additional properties.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.51 $
 * @package   phing
 */
class Phing {

    /** The default build file name */
    const DEFAULT_BUILD_FILENAME = "build.xml";

    /** Our current message output status. Follows PROJECT_MSG_XXX */
    private static $msgOutputLevel = PROJECT_MSG_INFO;

    /** PhingFile that we are using for configuration */
    private $buildFile = null;

    /** The build targets */
    private $targets = array();

    /**
     * Set of properties that are passed in from commandline or invoking code.
     * @var Properties
     */
    private static $definedProps;

    /** Names of classes to add as listeners to project */
    private $listeners = array();

    private $loggerClassname = null;

    /** The class to handle input (can be only one). */
    private $inputHandlerClassname;
    
    /** Indicates if this phing should be run */
    private $readyToRun = false;

    /** Indicates we should only parse and display the project help information */
    private $projectHelp = false;
    
    /** Used by utility function getResourcePath() */
    private static $importPaths;
    
    /** System-wide static properties (moved from System) */
    private static $properties = array();
    
    /** Static system timer. */
    private static $timer;
    
	/** The current Project */
	private static $currentProject;
	
	/** Whether to capture PHP errors to buffer. */
	private static $phpErrorCapture = false;
	
	/** Array of captured PHP errors */
	private static $capturedPhpErrors = array();
	
    /**
     * Prints the message of the Exception if it's not null.
     */
    function printMessage(Exception $t) {
        print($t->getMessage() . "\n");
        if (self::getMsgOutputLevel() === PROJECT_MSG_DEBUG) {
            print($t->getTraceAsString()."\n");
            if ($t instanceof Exception) {                
                $c = $t->getCause();
                if ($c !== null) {
                    print("Wrapped exception trace:\n");
                    print($c->getTraceAsString() . "\n");
                }
            }
        } // if output level is DEBUG
    }

    /** 
     * Entry point allowing for more options from other front ends.
     * 
     * This method encapsulates the complete build lifecycle.
     * 
     * @param array &$args The commandline args passed to phing shell script.
     * @param array $additionalUserProperties   Any additional properties to be passed to Phing (alternative front-end might implement this).
     *                                          These additional properties will be available using the getDefinedProperty() method and will
     *                                          be added to the project's "user" properties.
     * @return void
     * @see execute()
     * @see runBuild()
     */
    public static function start(&$args, $additionalUserProperties = null) {

        try {
            $m = new Phing();
            $m->execute($args);
        } catch (Exception $exc) {
            $m->printMessage($exc);
            self::halt(-1); // Parameter error
        }

        if ($additionalUserProperties !== null) {
            $keys = $m->additionalUserProperties->keys();
            while(count($keys)) {
                $key = array_shift($keys);
                $property = $m->additionalUserProperties->getProperty($key);
                $m->setDefinedProperty($key, $property);
            }
        }

        try {
            $m->runBuild();
        } catch(Exception $exc) {
            self::halt(1); // Errors occured
        }
        
        // everything fine, shutdown
        self::halt(0); // no errors, everything is cake
    }
    
    /**
     * Making output level a static property so that this property
     * can be accessed by other parts of the system, enabling
     * us to display more information -- e.g. backtraces -- for "debug" level.
     * @return int
     */
    public static function getMsgOutputLevel() {
        return self::$msgOutputLevel;
    }        
    
    /**
     * Command line entry point. This method kicks off the building
     * of a project object and executes a build using either a given
     * target or the default target.
     *
     * @param array $args Command line args.
     * @return void
     */
    public static function fire($args) {
        self::start($args, null);
    }
    
    /**
     * Setup/initialize Phing environment from commandline args.
     * @param array $args commandline args passed to phing shell.
     * @return void
     */
    public function execute($args) {
    
        self::$definedProps = new Properties();
        $this->searchForThis = null;

        // cycle through given args
        for ($i = 0, $argcount = count($args); $i < $argcount; ++$i) { 
                            // ++$i intentional here, as first param is script name
            $arg = $args[$i];

            if ($arg == "-help" || $arg == "-h") {
                $this->printUsage();
                return;
            } elseif ($arg == "-version" || $arg == "-v") {
                $this->printVersion();
                return;
            } elseif ($arg == "-quiet" || $arg == "-q") {
                self::$msgOutputLevel = PROJECT_MSG_WARN;
            } elseif ($arg == "-verbose") {
                $this->printVersion();
                self::$msgOutputLevel = PROJECT_MSG_VERBOSE;
            } elseif ($arg == "-debug") {
                $this->printVersion();
                self::$msgOutputLevel = PROJECT_MSG_DEBUG;
            } elseif ($arg == "-logfile") {
                try { // try to set logfile
                    if (!isset($args[$i+1])) {
                        print("You must specify a log file when using the -logfile argument\n");
                        return;
                    } else {
                        $logFile = new PhingFile($args[++$i]);
                        $this->loggerClassname = 'phing.listener.PearLogger';
                        $this->setDefinedProperty('pear.log.name', $logFile->getAbsolutePath());
                    }
                } catch (IOException $ioe) {
                    print("Cannot write on the specified log file. Make sure the path exists and you have write permissions.\n");
                    throw $ioe;
                }
            } elseif ($arg == "-buildfile" || $arg == "-file" || $arg == "-f") {
                if (!isset($args[$i+1])) {
                    print("You must specify a buildfile when using the -buildfile argument\n");
                    return;
                } else {
                    $this->buildFile = new PhingFile($args[++$i]);
                }
            } elseif ($arg == "-listener") {
                if (!isset($args[$i+1])) {
                    print("You must specify a listener class when using the -listener argument\n");
                    return;
                } else {
                    $this->listeners[] = $args[++$i];
                }
                
            } elseif (StringHelper::startsWith("-D", $arg)) {
                $name = substr($arg, 2);
                $value = null;
                $posEq = strpos($name, "=");
                if ($posEq !== false) {
                    $value = substr($name, $posEq+1);
                    $name  = substr($name, 0, $posEq);
                } elseif ($i < count($args)-1) {
                    $value = $args[++$i];
                }
                self::$definedProps->setProperty($name, $value);
            } elseif ($arg == "-logger") {
                if (!isset($args[$i+1])) {
                    print("You must specify a classname when using the -logger argument\n");
                    return;
                } else {
                    $this->loggerClassname = $args[++$i];
                }
            } elseif ($arg == "-inputhandler") {
                if ($this->inputHandlerClassname !== null) {
                    throw new BuildException("Only one input handler class may be specified.");
                }
                if (!isset($args[$i+1])) {
                    print("You must specify a classname when using the -inputhandler argument\n");
                    return;
                } else {
                    $this->inputHandlerClassname = $args[++$i];
                }
            } elseif ($arg == "-projecthelp" || $arg == "-targets" || $arg == "-list" || $arg == "-l") {
                // set the flag to display the targets and quit
                $this->projectHelp = true;
            } elseif ($arg == "-find") {
                // eat up next arg if present, default to build.xml
                if ($i < count($args)-1) {
                    $this->searchForThis = $args[++$i];
                } else {
                    $this->searchForThis = self::DEFAULT_BUILD_FILENAME;
                }
            } elseif (substr($arg,0,1) == "-") {
                // we don't have any more args
                print("Unknown argument: $arg\n");
                $this->printUsage();
                return;
            } else {
                // if it's no other arg, it may be the target
                array_push($this->targets, $arg);
            }
        }

        // if buildFile was not specified on the command line,
        if ($this->buildFile === null) {
            // but -find then search for it
            if ($this->searchForThis !== null) {
                $this->buildFile = $this->_findBuildFile(self::getProperty("user.dir"), $this->searchForThis);
            } else {
                $this->buildFile = new PhingFile(self::DEFAULT_BUILD_FILENAME);
            }
        }
        // make sure buildfile exists
        if (!$this->buildFile->exists()) {
            throw new BuildException("Buildfile: " . $this->buildFile->__toString() . " does not exist!");
        }

        // make sure it's not a directory
        if ($this->buildFile->isDirectory()) {   
            throw new BuildException("Buildfile: " . $this->buildFile->__toString() . " is a dir!");
        }

        $this->readyToRun = true;
    }

    /**
     * Helper to get the parent file for a given file.
     *
     * @param PhingFile $file
     * @return PhingFile Parent file or null if none
     */
    function _getParentFile(PhingFile $file) {
        $filename = $file->getAbsolutePath();
        $file     = new PhingFile($filename);
        $filename = $file->getParent();

        if ($filename !== null && self::$msgOutputLevel >= PROJECT_MSG_VERBOSE) {
            print("Searching in $filename\n");
        }

        return ($filename === null) ? null : new PhingFile($filename);
    }

    /**
     * Search parent directories for the build file.
     *
     * Takes the given target as a suffix to append to each
     * parent directory in search of a build file.  Once the
     * root of the file-system has been reached an exception
     * is thrown.
     * 
     * @param string $start Start file path.
     * @param string $suffix Suffix filename to look for in parents.
     * @return PhingFile A handle to the build file
     *
     * @throws BuildException    Failed to locate a build file
     */
    function _findBuildFile($start, $suffix) {
        if (self::$msgOutputLevel >= PROJECT_MSG_INFO) {
            print("Searching for $suffix ...\n");
        }
        $startf = new PhingFile($start);
        $parent = new PhingFile($startf->getAbsolutePath());
        $file   = new PhingFile($parent, $suffix);

        // check if the target file exists in the current directory
        while (!$file->exists()) {
            // change to parent directory
            $parent = $this->_getParentFile($parent);

            // if parent is null, then we are at the root of the fs,
            // complain that we can't find the build file.
            if ($parent === null) {
                throw new BuildException("Could not locate a build file!");
            }
            // refresh our file handle
            $file = new PhingFile($parent, $suffix);
        }
        return $file;
    }

    /**
     * Executes the build.
     * @return void
     */
    function runBuild() {

        if (!$this->readyToRun) {
            return;
        }
        
        $project = new Project();
		
		self::setCurrentProject($project);
		set_error_handler(array('Phing', 'handlePhpError'));
		
        $error = null;

        $this->addBuildListeners($project);
        $this->addInputHandler($project);
        
        // set this right away, so that it can be used in logging.
        $project->setUserProperty("phing.file", $this->buildFile->getAbsolutePath());

        try {
            $project->fireBuildStarted();
            $project->init();
        } catch (Exception $exc) {
            $project->fireBuildFinished($exc);
            throw $exc;        
        }

        $project->setUserProperty("phing.version", $this->getPhingVersion());

        $e = self::$definedProps->keys();
        while (count($e)) {
            $arg   = (string) array_shift($e);
            $value = (string) self::$definedProps->getProperty($arg);
            $project->setUserProperty($arg, $value);
        }
        unset($e);

        $project->setUserProperty("phing.file", $this->buildFile->getAbsolutePath());

        // first use the Configurator to create the project object
        // from the given build file.
                
        try {
            ProjectConfigurator::configureProject($project, $this->buildFile);
        } catch (Exception $exc) {
            $project->fireBuildFinished($exc);
			restore_error_handler();
			self::unsetCurrentProject();
            throw $exc;
        }         

        // make sure that we have a target to execute
        if (count($this->targets) === 0) {
            $this->targets[] = $project->getDefaultTarget();
        }

        // execute targets if help param was not given
        if (!$this->projectHelp) {
            
            try { 
                $project->executeTargets($this->targets);
            } catch (Exception $exc) {
                $project->fireBuildFinished($exc);
				restore_error_handler();
				self::unsetCurrentProject();
                throw $exc;
            }
        }
        // if help is requested print it
        if ($this->projectHelp) {
            try {
                $this->printDescription($project);
                $this->printTargets($project);
            } catch (Exception $exc) {
                $project->fireBuildFinished($exc);
				restore_error_handler();
				self::unsetCurrentProject();
                throw $exc;
            }
        }
                
        // finally {
        if (!$this->projectHelp) {
            $project->fireBuildFinished(null);
        }
		
		restore_error_handler();
		self::unsetCurrentProject();
    }
    
    /**
     * Bind any default build listeners to this project.
     * Currently this means adding the logger.
     * @param Project $project
     * @return void
     */
    private function addBuildListeners(Project $project) {
        // Add the default listener
        $project->addBuildListener($this->createLogger());
    }
    
    /**
     * Creates the InputHandler and adds it to the project.
     *
     * @param Project $project the project instance.
     *
     * @throws BuildException if a specified InputHandler
     *                           class could not be loaded.
     */
    private function addInputHandler(Project $project) {
        if ($this->inputHandlerClassname === null) {
            $handler = new DefaultInputHandler();
        } else {
            try {
                $clz = Phing::import($this->inputHandlerClassname);
                $handler = new $clz();
                if ($project !== null && method_exists($handler, 'setProject')) {
                    $handler->setProject($project);
                } 
            } catch (Exception $e) {
                $msg = "Unable to instantiate specified input handler "
                    . "class " . $this->inputHandlerClassname . " : "
                    . $e->getMessage();
                throw new BuildException($msg);
            }
        }
        $project->setInputHandler($handler);
    }

    /**
     * Creates the default build logger for sending build events to the log.
     * @return BuildListener The created Logger
     */
    private function createLogger() {
        if ($this->loggerClassname !== null) {
            self::import($this->loggerClassname);
            // get class name part            
            $classname = self::import($this->loggerClassname);
            $logger = new $classname;
        } else {
            require_once 'phing/listener/DefaultLogger.php';
            $logger = new DefaultLogger();
        }
        $logger->setMessageOutputLevel(self::$msgOutputLevel);
        return $logger;
    }
	
	/**
	 * Sets the current Project
	 * @param Project $p
	 */
	public static function setCurrentProject($p) {
		self::$currentProject = $p;
	}
	
	/**
	 * Unsets the current Project
	 */
	public static function unsetCurrentProject() {
		self::$currentProject = null;
	}
	
	/**
	 * Gets the current Project.
	 * @return Project Current Project or NULL if none is set yet/still.
	 */
	public static function getCurrentProject() {
		return self::$currentProject;
	}
	
	/**
	 * A static convenience method to send a log to the current (last-setup) Project.
	 * If there is no currently-configured Project, then this will do nothing.
	 * @param string $message
	 * @param int $priority PROJECT_MSG_INFO, etc.
	 */
	public static function log($message, $priority = PROJECT_MSG_INFO) {
		$p = self::getCurrentProject();
		if ($p) {
			$p->log($message, $priority);
		}
	}
	
	/**
	 * Error handler for PHP errors encountered during the build.
	 * This uses the logging for the currently configured project.
	 */
	public static function handlePhpError($level, $message, $file, $line) {
		
        // don't want to print supressed errors
        if (error_reporting() > 0) {
		
			if (self::$phpErrorCapture) {
			
				self::$capturedPhpErrors[] = array('message' => $message, 'level' => $level, 'line' => $line, 'file' => $file);
				
			} else {
			
				$message = '[PHP Error] ' . $message;
				$message .= ' [line ' . $line . ' of ' . $file . ']';
		
	            switch ($level) {
				
					case E_STRICT:
					case E_NOTICE:
	                case E_USER_NOTICE:
						self::log($message, PROJECT_MSG_VERBOSE);
	                    break;
					case E_WARNING:
	                case E_USER_WARNING:
						self::log($message, PROJECT_MSG_WARN);
	                    break;
	                case E_ERROR:
					case E_USER_ERROR:
	                default:
						self::log($message, PROJECT_MSG_ERR);
	
	            } // switch
				
			} // if phpErrorCapture
			
        } // if not @
		
	}
	
	/**
	 * Begins capturing PHP errors to a buffer.
	 * While errors are being captured, they are not logged.
	 */
	public static function startPhpErrorCapture() {
		self::$phpErrorCapture = true;
		self::$capturedPhpErrors = array();
	}
	
	/**
	 * Stops capturing PHP errors to a buffer.
	 * The errors will once again be logged after calling this method.
	 */
	public static function stopPhpErrorCapture() {
		self::$phpErrorCapture = false;
	}
	
	/**
	 * Clears the captured errors without affecting the starting/stopping of the capture.
	 */
	public static function clearCapturedPhpErrors() {
		self::$capturedPhpErrors = array();
	}
	
	/**
	 * Gets any PHP errors that were captured to buffer.
	 * @return array array('message' => message, 'line' => line number, 'file' => file name, 'level' => error level)
	 */
	public static function getCapturedPhpErrors() {
		return self::$capturedPhpErrors;
	}
	
    /**  Prints the usage of how to use this class */
    function printUsage() {
        $lSep = self::getProperty("line.separator");
        $msg = "";
        $msg .= "phing [options] [target [target2 [target3] ...]]" . $lSep;
        $msg .= "Options: " . $lSep;
        $msg .= "  -h -help               print this message" . $lSep;
        $msg .= "  -l -list               list available targets in this project" . $lSep;
        $msg .= "  -v -version            print the version information and exit" . $lSep;
        $msg .= "  -q -quiet              be extra quiet" . $lSep;
        $msg .= "  -verbose               be extra verbose" . $lSep;
        $msg .= "  -debug                 print debugging information" . $lSep;
        $msg .= "  -logfile <file>        use given file for log" . $lSep;
        $msg .= "  -logger <classname>    the class which is to perform logging" . $lSep;
        $msg .= "  -f -buildfile <file>   use given buildfile" . $lSep;
        $msg .= "  -D<property>=<value>   use value for given property" . $lSep;
        $msg .= "  -find <file>           search for buildfile towards the root of the" . $lSep;
        $msg .= "                         filesystem and use it" . $lSep;
        //$msg .= "  -recursive <file>      search for buildfile downwards and use it" . $lSep;
        $msg .= $lSep;
        $msg .= "Report bugs to <dev@phing.tigris.org>".$lSep;
        print($msg);
    }

    function printVersion() {
        print(self::getPhingVersion()."\n");
    }

    function getPhingVersion() {
        $versionPath = self::getResourcePath("phing/etc/VERSION.TXT");
		if ($versionPath === null) {
		    $versionPath = self::getResourcePath("etc/VERSION.TXT");
		}
        try { // try to read file
            $buffer = null;
            $file = new PhingFile($versionPath);
            $reader = new FileReader($file);
            $reader->readInto($buffer);
            $buffer = trim($buffer);
            //$buffer = "PHING version 1.0, Released 2002-??-??";
            $phingVersion = $buffer;
        } catch (IOException $iox) {
            print("Can't read version information file\n");
            throw new BuildException("Build failed");
        }        
        return $phingVersion;
    }

    /**  Print the project description, if any */
    function printDescription(Project $project) {
        if ($project->getDescription() !== null) {
            print($project->getDescription()."\n");
        }
    }

    /** Print out a list of all targets in the current buildfile */
    function printTargets($project) {
        // find the target with the longest name
        $maxLength = 0;
        $targets = $project->getTargets();
        $targetNames = array_keys($targets);
        $targetName = null;
        $targetDescription = null;
        $currentTarget = null;

        // split the targets in top-level and sub-targets depending
        // on the presence of a description
        
        $subNames = array();
        $topNameDescMap = array();
        
        foreach($targets as $currentTarget) {        
            $targetName = $currentTarget->getName();
            $targetDescription = $currentTarget->getDescription();            
            
            // subtargets are targets w/o descriptions
            if ($targetDescription === null) {
                $subNames[] = $targetName;
            } else {
                // topNames and topDescriptions are handled later
                // here we store in hash map (for sorting purposes)
                $topNameDescMap[$targetName] = $targetDescription;               
                if (strlen($targetName) > $maxLength) {
                    $maxLength = strlen($targetName);
                }
            }
        }
        
        // Sort the arrays
        sort($subNames); // sort array values, resetting keys (which are numeric)        
        ksort($topNameDescMap); // sort the keys (targetName) keeping key=>val associations
        
        $topNames = array_keys($topNameDescMap);
        $topDescriptions = array_values($topNameDescMap);

        $defaultTarget = $project->getDefaultTarget();

        if ($defaultTarget !== null && $defaultTarget !== "") {
            $defaultName = array();
            $defaultDesc = array();
            $defaultName[] = $defaultTarget;

            $indexOfDefDesc = array_search($defaultTarget, $topNames, true);
            if ($indexOfDefDesc !== false && $indexOfDefDesc >= 0) {
                $defaultDesc = array();
                $defaultDesc[] = $topDescriptions[$indexOfDefDesc];
            }

            $this->_printTargets($defaultName, $defaultDesc, "Default target:", $maxLength);

        }
        $this->_printTargets($topNames, $topDescriptions, "Main targets:", $maxLength);
        $this->_printTargets($subNames, null, "Subtargets:", 0);
    }    

    /**
     * Writes a formatted list of target names with an optional description.
     *
     * @param array $names The names to be printed.
     *              Must not be <code>null</code>.
     * @param array $descriptions The associated target descriptions.
     *                     May be <code>null</code>, in which case
     *                     no descriptions are displayed.
     *                     If non-<code>null</code>, this should have
     *                     as many elements as <code>names</code>.
     * @param string $heading The heading to display.
     *                Should not be <code>null</code>.
     * @param int $maxlen The maximum length of the names of the targets.
     *               If descriptions are given, they are padded to this
     *               position so they line up (so long as the names really
     *               <i>are</i> shorter than this).
     */
    private function _printTargets($names, $descriptions, $heading, $maxlen) {
        $lSep = self::getProperty("line.separator");
        $spaces = '  ';
        while (strlen($spaces) < $maxlen) {
            $spaces .= $spaces;
        }
        $msg = "";
        $msg .= $heading . $lSep;
        $msg .= str_repeat("-",79) . $lSep;

        $total = count($names);
        for($i=0; $i < $total; $i++) {
            $msg .= " ";
            $msg .= $names[$i];
            if (!empty($descriptions)) {
                $msg .= substr($spaces, 0, $maxlen - strlen($names[$i]) + 2);
                $msg .= $descriptions[$i];
            }
            $msg .= $lSep;
        }
        if ($total > 0) {
          print $msg . $lSep;
        } 
   }
   
   /**
    * Import a dot-path notation class path.
    * @param string $dotPath
    * @param mixed $classpath String or object supporting __toString()
    * @return string The unqualified classname (which can be instantiated).
    * @throws BuildException - if cannot find the specified file
    */
   public static function import($dotPath, $classpath = null) {
        
        // first check to see that the class specified hasn't already been included.
        // (this also handles case where this method is called w/ a classname rather than dotpath)
        $classname = StringHelper::unqualify($dotPath);
        if (class_exists($classname, false)) {
            return $classname;
        }
        
        $dotClassname = basename($dotPath);
        $dotClassnamePos = strlen($dotPath) - strlen($dotClassname);
        $classFile = strtr($dotClassname, '.', DIRECTORY_SEPARATOR) . ".php";
        $path = substr_replace($dotPath, $classFile, $dotClassnamePos);
        
        Phing::__import($path, $classpath);
        
        return $classname;
   }

   /**
    * Import a PHP file
    * @param string $path Path to the PHP file
    * @param mixed $classpath String or object supporting __toString()
    * @throws BuildException - if cannot find the specified file
    */
   public static function __import($path, $classpath = null) {
        
        if ($classpath) {
        
            // Apparently casting to (string) no longer invokes __toString() automatically.
            if (is_object($classpath)) {
                $classpath = $classpath->__toString();
            }
            
            // classpaths are currently additive, but we also don't want to just
            // indiscriminantly prepand/append stuff to the include_path.  This means
            // we need to parse current incldue_path, and prepend any
            // specified classpath locations that are not already in the include_path.              
            //
            // NOTE:  the reason why we do it this way instead of just changing include_path
            // and then changing it back, is that in many cases applications (e.g. Propel) will
            // include/require class files from within method calls.  This means that not all
            // necessary files will be included in this import() call, and hence we can't
            // change the include_path back without breaking those apps.  While this method could
            // be more expensive than switching & switching back (not sure, but maybe), it makes it
            // possible to write far less expensive run-time applications (e.g. using Propel), which is
            // really where speed matters more.
            
            $curr_parts = explode(PATH_SEPARATOR, ini_get('include_path'));
            $add_parts = explode(PATH_SEPARATOR, $classpath);
            $new_parts = array_diff($add_parts, $curr_parts);
            if ($new_parts) {
                if (self::getMsgOutputLevel() === PROJECT_MSG_DEBUG) {
                    print("Phing::import() prepending new include_path components: " . implode(PATH_SEPARATOR, $new_parts) . "\n");
                }
                ini_set('include_path', implode(PATH_SEPARATOR, array_merge($new_parts, $curr_parts)));
            }
        }
        
        $ret = include_once($path);        
        
        if ($ret === false) {
            $e = new BuildException("Error importing $path");
            if (self::getMsgOutputLevel() === PROJECT_MSG_DEBUG) {
                // We can't log this because listeners belong
                // to projects.  We'll just print it -- of course
                // that isn't very compatible w/ other frontends (but
                // there aren't any right now, so I'm not stressing)
                print("Error importing $path\n");
                print($e->getTraceAsString()."\n");
            }        
            throw $e;
        }
        
        return;
   }
   
   /**
    * Looks on include path for specified file.
    * @return string File found (null if no file found).
    */
   public static function getResourcePath($path) {
        
        if (self::$importPaths === null) {
            $paths = ini_get("include_path");            
            self::$importPaths = explode(PATH_SEPARATOR, ini_get("include_path"));
        }
        
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        foreach (self::$importPaths as $prefix) {
            $foo_path = $prefix . DIRECTORY_SEPARATOR . $path;
            if (file_exists($foo_path)) {
                return $foo_path;
            }
        }
        
        // Check for the property phing.home
        $home_dir = self::getProperty('phing.home');
        
        if ($home_dir)
        {
			$home_path = $home_dir . DIRECTORY_SEPARATOR . $path;
			
			if (file_exists($home_path))
			{
				return $home_path;
			}
		}
        
        // If we are using this via PEAR then check for the file in the data dir
        // This is a bit of a hack, but works better than previous solution of assuming
        // data_dir is on the include_path.
        $data_dir = '@DATA-DIR@';
        if ($data_dir{0} != '@') { // if we're using PEAR then the @ DATA-DIR @ token will have been substituted.
            $data_path = $data_dir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($data_path)) {
                   return $data_path;
               }
        }
        
        return null;
   }
   
   // -------------------------------------------------------------------------------------------
   // System-wide methods (moved from System class, which had namespace conflicts w/ PEAR System)
   // -------------------------------------------------------------------------------------------
             
    /**
     * Set System constants which can be retrieved by calling Phing::getProperty($propName).
     * @return void
     */
    private static function setSystemConstants() {

        /*
         * PHP_OS returns on
         *   WindowsNT4.0sp6  => WINNT
         *   Windows2000      => WINNT
         *   Windows ME       => WIN32
         *   Windows 98SE     => WIN32
         *   FreeBSD 4.5p7    => FreeBSD
         *   Redhat Linux     => Linux
		 *   Mac OS X		  => Darwin
         */
        self::setProperty('host.os', PHP_OS);
		
		// this is used by some tasks too
        self::setProperty('os.name', PHP_OS);
		
        // it's still possible this won't be defined,
        // e.g. if Phing is being included in another app w/o
        // using the phing.php script.
        if (!defined('PHP_CLASSPATH')) {
            define('PHP_CLASSPATH', get_include_path());
        }
        
        self::setProperty('php.classpath', PHP_CLASSPATH);

        // try to determine the host filesystem and set system property
        // used by Fileself::getFileSystem to instantiate the correct
        // abstraction layer

        switch (strtoupper(PHP_OS)) {
            case 'WINNT':
                self::setProperty('host.fstype', 'WINNT');
                break;
            case 'WIN32':
                self::setProperty('host.fstype', 'WIN32');
                break;
            default:
                self::setProperty('host.fstype', 'UNIX');
                break;
        }

        self::setProperty('php.version', PHP_VERSION);
        self::setProperty('user.home', getenv('HOME'));
        self::setProperty('application.startdir', getcwd());
        self::setProperty('line.separator', "\n");

        // try to detect machine dependent information
        $sysInfo = array();
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists("posix_uname")) {
              $sysInfo = posix_uname();
        } else {
              $sysInfo['nodename'] = php_uname('n');
              $sysInfo['machine']= php_uname('m') ;
              //this is a not so ideal substition, but maybe better than nothing
              $sysInfo['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "unknown";
              $sysInfo['release'] = php_uname('r');
              $sysInfo['version'] = php_uname('v');
        }              
     

        self::setProperty("host.name", isset($sysInfo['nodename']) ? $sysInfo['nodename'] : "unknown");
        self::setProperty("host.arch", isset($sysInfo['machine']) ? $sysInfo['machine'] : "unknown");
        self::setProperty("host.domain",isset($sysInfo['domain']) ? $sysInfo['domain'] : "unknown");
        self::setProperty("host.os.release", isset($sysInfo['release']) ? $sysInfo['release'] : "unknown");
        self::setProperty("host.os.version", isset($sysInfo['version']) ? $sysInfo['version'] : "unknown");
        unset($sysInfo);
    }
    
    /**
     * This gets a property that was set via command line or otherwise passed into Phing.
     * "Defined" in this case means "externally defined".  The reason this method exists is to
     * provide a public means of accessing commandline properties for (e.g.) logger or listener 
     * scripts.  E.g. to specify which logfile to use, PearLogger needs to be able to access
     * the pear.log.name property.
     * 
     * @param string $name
     * @return string value of found property (or null, if none found).
     */
    public static function getDefinedProperty($name) {
        return self::$definedProps->getProperty($name);
    }
    
    /**
     * This sets a property that was set via command line or otherwise passed into Phing.
     * 
     * @param string $name
     * @return string value of found property (or null, if none found).
     */
    public static function setDefinedProperty($name, $value) {
        return self::$definedProps->setProperty($name, $value);
    }
    
    /**
     * Returns property value for a System property.
     * System properties are "global" properties like line.separator,
     * and user.dir.  Many of these correspond to similar properties in Java
     * or Ant.
     * 
     * @param string $paramName
     * @return string Value of found property (or null, if none found).
     */
    public static function getProperty($propName) {
    
        // some properties are detemined on each access
        // some are cached, see below

        // default is the cached value:
        $val = isset(self::$properties[$propName]) ? self::$properties[$propName] : null;
    
        // special exceptions        
        switch($propName) {
            case 'user.dir':
                $val = getcwd();
            break;            
        }
        
        return $val;
    }

    /** Retuns reference to all properties*/
    public static function &getProperties() {
        return self::$properties;
    }

    public static function setProperty($propName, $propValue) {    
        $propName = (string) $propName;
        $oldValue = self::getProperty($propName);
        self::$properties[$propName] = $propValue;
        return $oldValue;
    }
    
    public static function currentTimeMillis() {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
    
    /**
     * Sets the include path based on PHP_CLASSPATH constant (set in phing.php).
     * @return void
     */
    private static function setIncludePaths() {
        $success = false;
        
        if (defined('PHP_CLASSPATH')) {
            $success = ini_set('include_path', PHP_CLASSPATH);
        } else {
            // don't do anything, just assume that include_path has been properly set.
            $success = true;
        }
        
        if ($success === false) {
            print("SYSTEM FAILURE: Could not set PHP include path\n");
            self::halt(-1);
        }
    }
    
    /**
     * Sets PHP INI values that Phing needs.
     * @return void
     */
    private static function setIni() {
        error_reporting(E_ALL);
        set_time_limit(0);
        ini_set('magic_quotes_gpc', 'off');
        ini_set('short_open_tag', 'off');
        ini_set('default_charset', 'iso-8859-1');
        ini_set('register_globals', 'off');
        ini_set('allow_call_time_pass_reference', 'on');
        
        // should return memory limit in MB  
        $mem_limit = (int) ini_get('memory_limit');
        if ($mem_limit < 32) {
            ini_set('memory_limit', '32M'); // nore: this may need to be higher for many projects
        }        
    }

    /**
     * Returns reference to Timer object.
     * @return Timer
     */
    public static function getTimer() {
        if (self::$timer === null) {
            include_once 'phing/system/util/Timer.php';
            self::$timer= new Timer();
        }
        return self::$timer;
    }
        
     /**
     * Start up Phing.
     * Sets up the Phing environment -- does NOT initiate the build process.
     * @return void
     */
    public static function startup() {
       
        register_shutdown_function(array('Phing', 'shutdown'));

        // some init stuff
        self::getTimer()->start();

        self::setSystemConstants();
        self::setIncludePaths();
        self::setIni();
    }
    
    /**
     * Halts the system.
     * @see shutdown()
     */
    public static function halt($code=0) {        
        self::shutdown($code);        
    }

    /**
     * Stops timers & exits.
     * @return void
     */
    public static function shutdown($exitcode = 0) {
        //print("[AUTOMATIC SYSTEM SHUTDOWN]\n");
        self::getTimer()->stop();
        exit($exitcode); // final point where everything stops
    }
    
}
