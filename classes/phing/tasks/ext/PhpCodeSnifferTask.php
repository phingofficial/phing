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
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author  Dirk Thomas <dirk.thomas@4wdmedia.de>
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTask extends Task
{
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    protected $file;

    // parameters for php code sniffer

    /**
     * @var string[]
     */
    protected $standards = ['Generic'];

    /**
     * @var string[]
     */
    protected $sniffs = [];

    /**
     * @var bool
     */
    protected $showWarnings = true;

    /**
     * @var bool
     */
    protected $showSources = false;

    /**
     * @var int
     */
    protected $reportWidth = 80;

    /**
     * @var int
     */
    protected $verbosity = 0;

    /**
     * @var int
     */
    protected $tabWidth = 0;

    /**
     * @var string[]
     */
    protected $allowedFileExtensions = ['php', 'inc', 'js', 'css'];

    /**
     * @var string[]
     */
    protected $allowedTypes = [];

    /**
     * @var array
     */
    protected $ignorePatterns = [];

    /**
     * @var bool
     */
    protected $noSubdirectories = false;

    /**
     * @var Parameter[]
     */
    protected $configData = [];

    /**
     * @var string
     */
    protected $encoding = 'iso-8859-1';

    // parameters to customize output

    /**
     * @var bool
     */
    protected $showSniffs = false;

    /**
     * @var string
     */
    protected $format = 'full';

    /**
     * @var PhpCodeSnifferTaskFormatterElement[]
     */
    protected $formatters = [];

    /**
     * Holds the type of the doc generator
     *
     * @var string
     */
    protected $docGenerator = '';

    /**
     * Holds the outfile for the documentation
     *
     * @var PhingFile
     */
    protected $docFile = null;

    /**
     * @var bool
     */
    private $haltonerror = false;

    /**
     * @var bool
     */
    private $haltonwarning = false;

    /**
     * @var bool
     */
    private $skipversioncheck = false;

    /**
     * @var string|null
     */
    private $propertyName = null;

    /**
     * Cache data storage
     *
     * @var DataStore
     */
    protected $cache;

    /**
     * Load the necessary environment for running PHP_CodeSniffer.
     *
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * File to be performed syntax check on
     *
     * @param PhingFile $file
     *
     * @return void
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
    }

    /**
     * Sets the coding standard to test for
     *
     * @param string $standards The coding standards
     *
     * @return void
     */
    public function setStandard(string $standards): void
    {
        $this->standards = [];
        $token           = ' ,;';
        $ext             = strtok($standards, $token);
        while ($ext !== false) {
            $this->standards[] = $ext;
            $ext               = strtok($token);
        }
    }

    /**
     * Sets the sniffs which the standard should be restricted to
     *
     * @param string $sniffs
     *
     * @return void
     */
    public function setSniffs(string $sniffs): void
    {
        $token = ' ,;';
        $sniff = strtok($sniffs, $token);
        while ($sniff !== false) {
            $this->sniffs[] = $sniff;
            $sniff          = strtok($token);
        }
    }

    /**
     * Sets the type of the doc generator
     *
     * @param string $generator HTML or Text
     *
     * @return void
     */
    public function setDocGenerator(string $generator): void
    {
        $this->docGenerator = $generator;
    }

    /**
     * Sets the outfile for the documentation
     *
     * @param PhingFile $file The outfile for the doc
     *
     * @return void
     */
    public function setDocFile(PhingFile $file): void
    {
        $this->docFile = $file;
    }

    /**
     * Sets the flag if warnings should be shown
     *
     * @param bool $show
     *
     * @return void
     */
    public function setShowWarnings(bool $show): void
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * Sets the flag if sources should be shown
     *
     * @param bool $show Whether to show sources or not
     *
     * @return void
     */
    public function setShowSources(bool $show): void
    {
        $this->showSources = StringHelper::booleanValue($show);
    }

    /**
     * Sets the width of the report
     *
     * @param int $width How wide the screen reports should be.
     *
     * @return void
     */
    public function setReportWidth(int $width): void
    {
        $this->reportWidth = (int) $width;
    }

    /**
     * Sets the verbosity level
     *
     * @param int $level
     *
     * @return void
     */
    public function setVerbosity(int $level): void
    {
        $this->verbosity = (int) $level;
    }

    /**
     * Sets the tab width to replace tabs with spaces
     *
     * @param int $width
     *
     * @return void
     */
    public function setTabWidth(int $width): void
    {
        $this->tabWidth = (int) $width;
    }

    /**
     * Sets file encoding
     *
     * @param string $encoding
     *
     * @return void
     */
    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    /**
     * Sets the allowed file extensions when using directories instead of specific files
     *
     * @param string $extensions
     *
     * @return void
     */
    public function setAllowedFileExtensions(string $extensions): void
    {
        $this->allowedFileExtensions = [];
        $token                       = ' ,;';
        $ext                         = strtok($extensions, $token);
        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext                           = strtok($token);
        }
    }

    /**
     * Sets the allowed types for the PHP_CodeSniffer::suggestType()
     *
     * @param string $types
     *
     * @return void
     */
    public function setAllowedTypes(string $types): void
    {
        $this->allowedTypes = [];
        $token              = ' ,;';
        $type               = strtok($types, $token);
        while ($type !== false) {
            $this->allowedTypes[] = $type;
            $type                 = strtok($token);
        }
    }

    /**
     * Sets the ignore patterns to skip files when using directories instead of specific files
     *
     * @param string $patterns
     *
     * @return void
     */
    public function setIgnorePatterns(string $patterns): void
    {
        $this->ignorePatterns = [];
        $token                = ' ,;';
        $pattern              = strtok($patterns, $token);
        while ($pattern !== false) {
            $this->ignorePatterns[$pattern] = 'relative';
            $pattern                        = strtok($token);
        }
    }

    /**
     * Sets the flag if subdirectories should be skipped
     *
     * @param bool $subdirectories
     *
     * @return void
     */
    public function setNoSubdirectories(bool $subdirectories): void
    {
        $this->noSubdirectories = StringHelper::booleanValue($subdirectories);
    }

    /**
     * Creates a config parameter for this task
     *
     * @return Parameter The created parameter
     */
    public function createConfig(): Parameter
    {
        $num = array_push($this->configData, new Parameter());

        return $this->configData[$num - 1];
    }

    /**
     * Sets the flag if the used sniffs should be listed
     *
     * @param bool $show
     *
     * @return void
     */
    public function setShowSniffs(bool $show): void
    {
        $this->showSniffs = StringHelper::booleanValue($show);
    }

    /**
     * Sets the output format
     *
     * @param string $format
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Create object for nested formatter element.
     *
     * @return PhpCodeSnifferTaskFormatterElement
     */
    public function createFormatter(): PhpCodeSnifferTaskFormatterElement
    {
        $num = array_push(
            $this->formatters,
            new PhpCodeSnifferTaskFormatterElement()
        );

        return $this->formatters[$num - 1];
    }

    /**
     * Sets the haltonerror flag
     *
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonerror(bool $value): void
    {
        $this->haltonerror = $value;
    }

    /**
     * Sets the haltonwarning flag
     *
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonwarning(bool $value): void
    {
        $this->haltonwarning = $value;
    }

    /**
     * Sets the skipversioncheck flag
     *
     * @param bool $value
     *
     * @return void
     */
    public function setSkipVersionCheck(bool $value): void
    {
        $this->skipversioncheck = $value;
    }

    /**
     * Sets the name of the property to use
     *
     * @param string $propertyName
     *
     * @return void
     */
    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     *
     * @return string|null
     */
    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    /**
     * Whether to store last-modified times in cache
     *
     * @param PhingFile $file
     *
     * @return void
     *
     * @throws IOException
     */
    public function setCacheFile(PhingFile $file): void
    {
        $this->cache = new DataStore($file);
    }

    /**
     * Return the list of files to parse
     *
     * @return string[] list of absolute files to parse
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    protected function getFilesToParse()
    {
        $filesToParse = [];

        if ($this->file instanceof PhingFile) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
                    $fileAbsolutePath = $dir . DIRECTORY_SEPARATOR . $filename;
                    if ($this->cache) {
                        $lastMTime    = $this->cache->get($fileAbsolutePath);
                        $currentMTime = filemtime($fileAbsolutePath);
                        if ($lastMTime >= $currentMTime) {
                            continue;
                        }

                        $this->cache->put($fileAbsolutePath, $currentMTime);
                    }
                    $filesToParse[] = $fileAbsolutePath;
                }
            }
        }
        return $filesToParse;
    }

    /**
     * Executes PHP code sniffer against PhingFile or a FileSet
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function main(): void
    {
        if (!class_exists('PHP_CodeSniffer')) {
            @include_once 'PHP/CodeSniffer.php';

            if (!class_exists('PHP_CodeSniffer')) {
                throw new BuildException(
                    'This task requires the PHP_CodeSniffer package installed and available on the include path',
                    $this->getLocation()
                );
            }
        }

        /**
         * Determine PHP_CodeSniffer version number
         */
        if (!$this->skipversioncheck) {
            if (defined('PHP_CodeSniffer::VERSION')) {
                preg_match('/\d\.\d\.\d/', PHP_CodeSniffer::VERSION, $version);
            } else {
                preg_match('/\d\.\d\.\d/', shell_exec('phpcs --version'), $version);
            }

            if (version_compare($version[0], '1.2.2') < 0) {
                throw new BuildException(
                    'PhpCodeSnifferTask requires PHP_CodeSniffer version >= 1.2.2',
                    $this->getLocation()
                );
            }
        }

        if (!isset($this->file) && count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (count($this->formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PhpCodeSnifferTaskFormatterElement();
            $fmt->setType($this->format);
            $fmt->setUseFile(false);
            $this->formatters[] = $fmt;
        }

        $fileList = $this->getFilesToParse();

        $cwd = getcwd();

        // Save command line arguments because it confuses PHPCS (version 1.3.0)
        $oldArgs         = $_SERVER['argv'];
        $_SERVER['argv'] = [];
        $_SERVER['argc'] = 0;

        $codeSniffer = new PhpCodeSnifferTaskWrapper($this->verbosity, $this->tabWidth, $this->encoding);
        $codeSniffer->setAllowedFileExtensions($this->allowedFileExtensions);
        if ($this->allowedTypes) {
            PhpCodeSnifferTaskWrapper::$allowedTypes = $this->allowedTypes;
        }
        if (is_array($this->ignorePatterns)) {
            $codeSniffer->setIgnorePatterns($this->ignorePatterns);
        }
        foreach ($this->configData as $configData) {
            $codeSniffer::setConfigData($configData->getName(), $configData->getValue(), true);
        }

        /*
         * Verifying if standard is installed only after setting config data.
         * Custom standard paths could be provided via installed_paths config parameter.
         */
        foreach ($this->standards as $standard) {
            if (PHP_CodeSniffer::isInstalledStandard($standard) === false) {
                // They didn't select a valid coding standard, so help them
                // out by letting them know which standards are installed.
                $installedStandards = PHP_CodeSniffer::getInstalledStandards();
                $numStandards       = count($installedStandards);
                $errMsg             = '';

                if ($numStandards === 0) {
                    $errMsg = 'No coding standards are installed.';
                } else {
                    $lastStandard = array_pop($installedStandards);

                    if ($numStandards === 1) {
                        $errMsg = 'The only coding standard installed is ' . $lastStandard;
                    } else {
                        $standardList  = implode(', ', $installedStandards);
                        $standardList .= ' and ' . $lastStandard;
                        $errMsg        = 'The installed coding standards are ' . $standardList;
                    }
                }

                throw new BuildException(
                    'ERROR: the "' . $standard . '" coding standard is not installed. ' . $errMsg,
                    $this->getLocation()
                );
            }
        }

        if (!$this->showWarnings) {
            $codeSniffer->cli->warningSeverity = 0;
        }

        // nasty integration hack
        $values          = $codeSniffer->cli->getDefaults();
        $_SERVER['argv'] = ['t'];
        $_SERVER['argc'] = 1;
        foreach ($this->formatters as $fe) {
            if ($fe->getUseFile()) {
                $_SERVER['argv'][] = '--report-' . $fe->getType() . '=' . $fe->getOutfile();
            } else {
                $_SERVER['argv'][] = '--report-' . $fe->getType();
            }

            $_SERVER['argc']++;
        }

        if ($this->cache) {
            ReportsPhingRemoveFromCache::setCache($this->cache);
            // add a fake report to remove from cache
            $_SERVER['argv'][] = '--report-phingRemoveFromCache';
            $_SERVER['argc']++;
        }

        $codeSniffer->process($fileList, $this->standards, $this->sniffs, $this->noSubdirectories);
        $_SERVER['argv'] = [];
        $_SERVER['argc'] = 0;

        if ($this->cache) {
            ReportsPhingRemoveFromCache::setCache(null);
            $this->cache->commit();
        }

        $this->printErrorReport($codeSniffer);

        // generate the documentation
        if ($this->docGenerator !== '' && $this->docFile !== null) {
            ob_start();

            $codeSniffer->generateDocs($this->standards, $this->sniffs, $this->docGenerator);

            $output = ob_get_contents();
            ob_end_clean();

            // write to file
            $outputFile = $this->docFile->getPath();
            $check      = file_put_contents($outputFile, $output);

            if ($check === false) {
                throw new BuildException('Error writing doc to ' . $outputFile);
            }
        } elseif ($this->docGenerator !== '' && $this->docFile === null) {
            $codeSniffer->generateDocs($this->standards, $this->sniffs, $this->docGenerator);
        }

        if ($this->haltonerror && $codeSniffer->reporting->totalErrors > 0) {
            throw new BuildException('phpcodesniffer detected ' . $codeSniffer->reporting->totalErrors . ' error' . ($codeSniffer->reporting->totalErrors > 1 ? 's' : ''));
        }

        if ($this->haltonwarning && $codeSniffer->reporting->totalWarnings > 0) {
            throw new BuildException('phpcodesniffer detected ' . $codeSniffer->reporting->totalWarnings . ' warning' . ($codeSniffer->reporting->totalWarnings > 1 ? 's' : ''));
        }

        $_SERVER['argv'] = $oldArgs;
        $_SERVER['argc'] = count($oldArgs);
        chdir($cwd);
    }

    /**
     * Prints the error report.
     *
     * @param PHP_CodeSniffer $phpcs The PHP_CodeSniffer object containing
     *                               the errors.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function printErrorReport(PHP_CodeSniffer $phpcs): void
    {
        $sniffs   = $phpcs->getSniffs();
        $sniffStr = '';
        foreach ($sniffs as $sniff) {
            if (is_string($sniff)) {
                $sniffStr .= '- ' . $sniff . PHP_EOL;
            } else {
                $sniffStr .= '- ' . get_class($sniff) . PHP_EOL;
            }
        }
        $this->project->setProperty($this->getPropertyName(), (string) $sniffStr);

        if ($this->showSniffs) {
            $this->log('The list of used sniffs (#' . count($sniffs) . '): ' . PHP_EOL . $sniffStr, Project::MSG_INFO);
        }

        // process output
        $reporting = $phpcs->reporting;
        foreach ($this->formatters as $fe) {
            $reportFile = null;

            if ($fe->getUseFile()) {
                $reportFile = $fe->getOutfile();
                //ob_start();
            }

            // Crude check, but they broke backwards compatibility
            // with a minor version release.
            if (PHP_CodeSniffer::VERSION >= '2.2.0') {
                $cliValues = ['colors' => false];
                $reporting->printReport(
                    $fe->getType(),
                    $this->showSources,
                    $cliValues,
                    $reportFile,
                    $this->reportWidth
                );
            } else {
                $reporting->printReport(
                    $fe->getType(),
                    $this->showSources,
                    $reportFile,
                    $this->reportWidth
                );
            }

            // reporting class uses ob_end_flush(), but we don't want
            // an output if we use a file
            //if ($fe->getUseFile()) {
            //    ob_end_clean();
            //}
        }
    }

    /**
     * Outputs the results with a custom format
     *
     * @param array $report Packaged list of all errors in each file
     *
     * @return void
     *
     * @throws Exception
     */
    protected function outputCustomFormat(array $report): void
    {
        $files = $report['files'];
        foreach ($files as $file => $attributes) {
            $errors   = $attributes['errors'];
            $warnings = $attributes['warnings'];
            $messages = $attributes['messages'];
            if ($errors > 0) {
                $this->log(
                    $file . ': ' . $errors . ' error' . ($errors > 1 ? 's' : '') . ' detected',
                    Project::MSG_ERR
                );
                $this->outputCustomFormatMessages($messages, 'ERROR');
            } else {
                $this->log($file . ': No syntax errors detected', Project::MSG_VERBOSE);
            }
            if ($warnings > 0) {
                $this->log(
                    $file . ': ' . $warnings . ' warning' . ($warnings > 1 ? 's' : '') . ' detected',
                    Project::MSG_WARN
                );
                $this->outputCustomFormatMessages($messages, 'WARNING');
            }
        }

        $totalErrors   = $report['totals']['errors'];
        $totalWarnings = $report['totals']['warnings'];
        $this->log(count($files) . ' files were checked', Project::MSG_INFO);
        if ($totalErrors > 0) {
            $this->log($totalErrors . ' error' . ($totalErrors > 1 ? 's' : '') . ' detected', Project::MSG_ERR);
        } else {
            $this->log('No syntax errors detected', Project::MSG_INFO);
        }
        if ($totalWarnings > 0) {
            $this->log($totalWarnings . ' warning' . ($totalWarnings > 1 ? 's' : '') . ' detected', Project::MSG_INFO);
        }
    }

    /**
     * Outputs the messages of a specific type for one file
     *
     * @param array  $messages
     * @param string $type
     *
     * @return void
     *
     * @throws Exception
     */
    protected function outputCustomFormatMessages(array $messages, string $type): void
    {
        foreach ($messages as $line => $messagesPerLine) {
            foreach ($messagesPerLine as $column => $messagesPerColumn) {
                foreach ($messagesPerColumn as $message) {
                    $msgType = $message['type'];
                    if ($type == $msgType) {
                        $logLevel = Project::MSG_INFO;
                        if ($msgType == 'ERROR') {
                            $logLevel = Project::MSG_ERR;
                        } else {
                            if ($msgType == 'WARNING') {
                                $logLevel = Project::MSG_WARN;
                            }
                        }
                        $text   = $message['message'];
                        $string = $msgType . ' in line ' . $line . ' column ' . $column . ': ' . $text;
                        $this->log($string, $logLevel);
                    }
                }
            }
        }
    }
}
