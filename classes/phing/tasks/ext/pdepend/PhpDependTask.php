<?php
/**
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

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/pdepend/PhpDependLoggerElement.php';
require_once 'phing/tasks/ext/pdepend/PhpDependAnalyzerElement.php';

/**
 * Runs the PHP_Depend software analyzer and metric tool.
 * Performs static code analysis on a given source base.
 *
 * @package phing.tasks.ext.pdepend
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id$
 * @since   2.4.1
 */
class PhpDependTask extends Task
{
    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    protected $file = null;

    /**
     * All fileset objects assigned to this task
     *
     * @var FileSet[]
     */
    protected $filesets = array();

    /**
     * List of allowed file extensions. Default file extensions are <b>php</b>
     * and <p>php5</b>.
     *
     * @var array<string>
     */
    protected $allowedFileExtensions = array('php', 'php5');

    /**
     * List of exclude directories. Default exclude dirs are <b>.git</b>,
     * <b>.svn</b> and <b>CVS</b>.
     *
     * @var array<string>
     */
    protected $excludeDirectories = array('.git', '.svn', 'CVS');

    /**
     * List of exclude packages
     *
     * @var array<string>
     */
    protected $excludePackages = array();

    /**
     * Should the parse ignore doc comment annotations?
     *
     * @var boolean
     */
    protected $withoutAnnotations = false;

    /**
     * Should PHP_Depend treat <b>+global</b> as a regular project package?
     *
     * @var boolean
     */
    protected $supportBadDocumentation = false;

    /**
     * Flag for enable/disable debugging
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * PHP_Depend configuration file
     *
     * @var PhingFile
     */
    protected $configFile = null;

    /**
     * Logger elements
     *
     * @var PhpDependLoggerElement[]
     */
    protected $loggers = array();

    /**
     * Analyzer elements
     *
     * @var PhpDependAnalyzerElement[]
     */
    protected $analyzers = array();

    /**
     * Holds the PHP_Depend runner instance
     *
     * @var PHP_Depend_TextUI_Runner
     */
    protected $runner = null;

    /**
     * Flag that determines whether to halt on error
     *
     * @var boolean
     */
    protected $haltonerror = false;

    /**
     * Load the necessary environment for running PHP_Depend
     *
     * @throws BuildException
     */
    protected function requireDependencies()
    {
        /**
         * Determine PHP_Depend installation
         */
        @include_once 'PHP/Depend/TextUI/Runner.php';

        if (! class_exists('PHP_Depend_TextUI_Runner')) {
            throw new BuildException(
                'PhpDependTask depends on PHP_Depend being installed and on include_path',
                $this->getLocation()
            );
        }

        /**
         * Other dependencies that should only be loaded when class is actually used
         */
        require_once 'PHP/Depend/Autoload.php';
    }

    /**
     * Set the input source file or directory
     *
     * @param PhingFile $file The input source file or directory
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute)
     *
     * @return FileSet The created fileset object
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());

        return $this->filesets[$num-1];
    }

    /**
     * Sets a list of filename extensions for valid php source code files
     *
     * @param string $fileExtensions List of valid file extensions
     */
    public function setAllowedFileExtensions($fileExtensions)
    {
        $this->allowedFileExtensions = array();

        $token = ' ,;';
        $ext   = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets a list of exclude directories
     *
     * @param string $excludeDirectories List of exclude directories
     */
    public function setExcludeDirectories($excludeDirectories)
    {
        $this->excludeDirectories = array();

        $token   = ' ,;';
        $pattern = strtok($excludeDirectories, $token);

        while ($pattern !== false) {
            $this->excludeDirectories[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Sets a list of exclude packages
     *
     * @param string $excludePackages Exclude packages
     */
    public function setExcludePackages($excludePackages)
    {
        $this->excludePackages = array();

        $token   = ' ,;';
        $pattern = strtok($excludePackages, $token);

        while ($pattern !== false) {
            $this->excludePackages[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Should the parser ignore doc comment annotations?
     *
     * @param boolean $withoutAnnotations
     */
    public function setWithoutAnnotations($withoutAnnotations)
    {
        $this->withoutAnnotations = StringHelper::booleanValue($withoutAnnotations);
    }

    /**
     * Should PHP_Depend support projects with a bad documentation. If this
     * option is set to <b>true</b>, PHP_Depend will treat the default package
     * <b>+global</b> as a regular project package.
     *
     * @param boolean $supportBadDocumentation
     */
    public function setSupportBadDocumentation($supportBadDocumentation)
    {
        $this->supportBadDocumentation = StringHelper::booleanValue($supportBadDocumentation);
    }

    /**
     * Set debugging On/Off
     *
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = StringHelper::booleanValue($debug);
    }

    /**
     * Set halt on error
     *
     * @param boolean $haltonerror
     */
    public function setHaltonerror($haltonerror)
    {
        $this->haltonerror = StringHelper::booleanValue($haltonerror);
    }

    /**
     * Set the configuration file
     *
     * @param PhingFile $configFile The configuration file
     */
    public function setConfigFile(PhingFile $configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * Create object for nested logger element
     *
     * @return PhpDependLoggerElement
     */
    public function createLogger()
    {
        $num = array_push($this->loggers, new PhpDependLoggerElement());

        return $this->loggers[$num-1];
    }

    /**
     * Create object for nested analyzer element
     *
     * @return PhpDependAnalyzerElement
     */
    public function createAnalyzer()
    {
        $num = array_push($this->analyzers, new PhpDependAnalyzerElement());

        return $this->analyzers[$num-1];
    }

    /**
     * Executes PHP_Depend_TextUI_Runner against PhingFile or a FileSet
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->requireDependencies();

        $autoload = new PHP_Depend_Autoload();
        $autoload->register();

        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException('Missing either a nested fileset or attribute "file" set');
        }

        if (count($this->loggers) == 0) {
            throw new BuildException('Missing nested "logger" element');
        }

        $this->validateLoggers();
        $this->validateAnalyzers();

        $filesToParse = array();

        if ($this->file instanceof PhingFile) {
            $filesToParse[] = $this->file->__toString();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();

                foreach ($files as $filename) {
                     $f = new PhingFile($fs->getDir($this->project), $filename);
                     $filesToParse[] = $f->getAbsolutePath();
                }
            }
        }

        $this->runner = new PHP_Depend_TextUI_Runner();
        $this->runner->addProcessListener(new PHP_Depend_TextUI_ResultPrinter());

        $configurationFactory = new PHP_Depend_Util_Configuration_Factory();
        $configuration        = $configurationFactory->createDefault();

        $this->runner->setConfiguration($configuration);
        $this->runner->setSourceArguments($filesToParse);

        foreach ($this->loggers as $logger) {
            // Register logger
            $this->runner->addLogger(
                $logger->getType(),
                $logger->getOutfile()->__toString()
            );
        }

        foreach ($this->analyzers as $analyzer) {
            // Register additional analyzer
            $this->runner->addOption(
                $analyzer->getType(),
                $analyzer->getValue()
            );
        }

        // Disable annotation parsing
        if ($this->withoutAnnotations) {
            $this->runner->setWithoutAnnotations();
        }

        // Enable bad documentation support
        if ($this->supportBadDocumentation) {
            $this->runner->setSupportBadDocumentation();
        }

        // Check for suffix
        if (count($this->allowedFileExtensions) > 0) {
            $this->runner->setFileExtensions($this->allowedFileExtensions);
        }

        // Check for ignore directories
        if (count($this->excludeDirectories) > 0) {
            $this->runner->setExcludeDirectories($this->excludeDirectories);
        }

        // Check for exclude packages
        if (count($this->excludePackages) > 0) {
            $this->runner->setExcludePackages($this->excludePackages);
        }

        // Check for configuration option
        if ($this->configFile instanceof PhingFile) {
            if (file_exists($this->configFile->__toString()) === false) {
                throw new BuildException(
                    'The configuration file "' . $this->configFile->__toString() . '" doesn\'t exist.'
                );
            }

            // Load configuration file
            $config = new PHP_Depend_Util_Configuration(
                $this->configFile->__toString(),
                null,
                true
            );

            // Store in config registry
            PHP_Depend_Util_ConfigurationInstance::set($config);
        }

        if ($this->debug) {
            require_once 'PHP/Depend/Util/Log.php';
            // Enable debug logging
            PHP_Depend_Util_Log::setSeverity(PHP_Depend_Util_Log::DEBUG);
        }

        $this->runner->run();

        if ($this->runner->hasParseErrors() === true) {
            $this->log('Following errors occurred:');

            foreach ($this->runner->getParseErrors() as $error) {
                $this->log($error);
            }

            if ($this->haltonerror === true) {
                throw new BuildException('Errors occurred during parse process');
            }
        }
    }

    /**
     * Validates the available loggers
     *
     * @throws BuildException
     */
    protected function validateLoggers()
    {
        foreach ($this->loggers as $logger) {
            if ($logger->getType() === '') {
                throw new BuildException('Logger missing required "type" attribute');
            }

            if ($logger->getOutfile() === null) {
                throw new BuildException('Logger requires "outfile" attribute');
            }
        }
    }

    /**
     * Validates the available analyzers
     *
     * @throws BuildException
     */
    protected function validateAnalyzers()
    {
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->getType() === '') {
                throw new BuildException('Analyzer missing required "type" attribute');
            }

            if (count($analyzer->getValue()) === 0) {
                throw new BuildException('Analyzer missing required "value" attribute');
            }
        }
    }
}
