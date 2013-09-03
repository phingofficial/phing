<?php
/**
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
require_once 'phing/BuildException.php';

/**
 * Runs phploc a tool for quickly measuring the size of PHP projects.
 *
 * @package phing.tasks.ext.phploc
 * @author  Raphael Stolt <raphael.stolt@gmail.com>
 */
class PHPLocTask extends Task
{
    /**
     * @var array
     */
    protected $suffixesToCheck = array('php');

    /**
     * @var array
     */
    protected $acceptedReportTypes = array('cli', 'txt', 'xml', 'csv');

    /**
     * @var null
     */
    protected $reportDirectory = null;

    /**
     * @var string
     */
    protected $reportType = 'cli';

    /**
     * @var string
     */
    protected $reportFileName = 'phploc-report';

    /**
     * @var bool
     */
    protected $countTests = false;

    /**
     * @var null|PhingFile
     */
    protected $fileToCheck = null;

    /**
     * @var array
     */
    protected $filesToCheck = array();

    /**
     * @var FileSet[]
     */
    protected $fileSets = array();

    /**
     * @var bool
     */
    protected $oldVersion = false;

    /**
     * Flag for identifying if phploc library is between version 1.7.0 and 1.7.4
     *
     * @var bool
     */
    private $isOneSevenVersion = false;

    /**
     * @param string $suffixListOrSingleSuffix
     */
    public function setSuffixes($suffixListOrSingleSuffix)
    {
        if (stripos($suffixListOrSingleSuffix, ',')) {
            $suffixes              = explode(',', $suffixListOrSingleSuffix);
            $this->suffixesToCheck = array_map('trim', $suffixes);
        } else {
            $this->suffixesToCheck[] = trim($suffixListOrSingleSuffix);
        }
    }

    /**
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->fileToCheck = trim($file);
    }

    /**
     * @param boolean $countTests
     */
    public function setCountTests($countTests)
    {
        $this->countTests = StringHelper::booleanValue($countTests);
    }

    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->fileSets, new FileSet());

        return $this->fileSets[$num - 1];
    }

    /**
     * @param string $type
     */
    public function setReportType($type)
    {
        $this->reportType = trim($type);
    }

    /**
     * @param string $name
     */
    public function setReportName($name)
    {
        $this->reportFileName = trim($name);
    }

    /**
     * @param string $directory
     */
    public function setReportDirectory($directory)
    {
        $this->reportDirectory = trim($directory);
    }

    public function main()
    {
        /**
         * Find PHPLoc
         */
        if (!class_exists('\SebastianBergmann\PHPLOC\Analyser')) {
            if (!@include_once('SebastianBergmann/PHPLOC/autoload.php')) {
                if (!@include_once('PHPLOC/Analyser.php')) {
                    throw new BuildException(
                        'PHPLocTask depends on PHPLoc being installed and on include_path.',
                        $this->getLocation()
                    );
                } else {
                    $this->oldVersion = true;
                }
            }
        }

        $versionClass = '\\SebastianBergmann\\PHPLOC\\Version';

        if (class_exists($versionClass)
            && version_compare(constant($versionClass . '::VERSION'), '1.7.0') >= 0
            && version_compare(constant($versionClass . '::VERSION'), '2.0.0beta1') == -1
        ) {
            $this->isOneSevenVersion = true;
        }

        $this->validateProperties();

        if ($this->reportDirectory !== null && !is_dir($this->reportDirectory)) {
            $reportOutputDir = new PhingFile($this->reportDirectory);

            $logMessage = "Report output directory doesn't exist, creating: "
                        . $reportOutputDir->getAbsolutePath() . '.';

            $this->log($logMessage);
            $reportOutputDir->mkdirs();
        }

        if ($this->reportType !== 'cli') {
            $this->reportFileName .= '.' . $this->reportType;
        }

        if (count($this->fileSets) > 0) {
            foreach ($this->fileSets as $fileSet) {
                $directoryScanner = $fileSet->getDirectoryScanner($this->project);
                $files            = $directoryScanner->getIncludedFiles();
                $directory        = $fileSet->getDir($this->project)->getPath();

                foreach ($files as $file) {
                    if ($this->isFileSuffixSet($file)) {
                        $this->filesToCheck[] = $directory . DIRECTORY_SEPARATOR . $file;
                    }
                }
            }

            $this->filesToCheck = array_unique($this->filesToCheck);
        }

        $this->runPhpLocCheck();
    }

    /**
     * @throws BuildException
     */
    private function validateProperties()
    {
        if ($this->fileToCheck === null && count($this->fileSets) === 0) {
            throw new BuildException('Missing either a nested fileset or the attribute "file" set.');
        }

        if ($this->fileToCheck !== null) {
            if (!file_exists($this->fileToCheck)) {
                throw new BuildException("File to check doesn't exist.");
            }

            if (!$this->isFileSuffixSet($this->fileToCheck)) {
                throw new BuildException('Suffix of file to check is not defined in "suffixes" attribute.');
            }

            if (count($this->fileSets) > 0) {
                throw new BuildException('Either use a nested fileset or "file" attribute; not both.');
            }
        }

        if (count($this->suffixesToCheck) === 0) {
            throw new BuildException('No file suffix defined.');
        }

        if ($this->reportType === null) {
            throw new BuildException('No report type defined.');
        }

        if ($this->reportType !== null && !in_array($this->reportType, $this->acceptedReportTypes)) {
            throw new BuildException('Unaccepted report type defined.');
        }

        if ($this->reportType !== 'cli' && $this->reportDirectory === null) {
            throw new BuildException('No report output directory defined.');
        }
    }

    /**
     * @param string $filename
     *
     * @return boolean
     */
    protected function isFileSuffixSet($filename)
    {
        return in_array(pathinfo($filename, PATHINFO_EXTENSION), $this->suffixesToCheck);
    }

    protected function runPhpLocCheck()
    {
        $files = $this->getFilesToCheck();
        $count = $this->getCountForFiles($files);

        if ($this->reportType != 'cli') {
            $logMessage = 'Writing report to: '
                        . $this->reportDirectory . DIRECTORY_SEPARATOR . $this->reportFileName;

            $this->log($logMessage);
        }

        switch ($this->reportType) {
            case 'cli':
                if ($this->oldVersion || $this->isOneSevenVersion) {
                    if ($this->oldVersion) {
                        require_once 'PHPLOC/TextUI/ResultPrinter/Text.php';

                        $printerClass = 'PHPLOC_TextUI_ResultPrinter_Text';
                    } else {
                        $printerClass = '\\SebastianBergmann\\PHPLOC\\TextUI\\ResultPrinter';
                    }

                    $printer = new $printerClass;
                    $printer->printResult($count, $this->countTests);
                } else {
                    $outputClass  = '\\Symfony\\Component\\Console\\Output\\ConsoleOutput';
                    $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\Text';

                    $output  = new $outputClass;
                    $printer = new $printerClass;
                    $printer->printResult($output, $count, $this->countTests);
                }
                break;

            case 'txt':
                if ($this->oldVersion || $this->isOneSevenVersion) {
                    if ($this->oldVersion) {
                        require_once 'PHPLOC/TextUI/ResultPrinter/Text.php';

                        $printerClass = 'PHPLOC_TextUI_ResultPrinter_Text';
                    } else {
                        $printerClass = '\\SebastianBergmann\\PHPLOC\\TextUI\\ResultPrinter';
                    }

                    $printer = new $printerClass;

                    ob_start();
                    $printer->printResult($count, $this->countTests);
                    $result = ob_get_contents();
                    ob_end_clean();

                    file_put_contents($this->reportDirectory . DIRECTORY_SEPARATOR . $this->reportFileName, $result);
                } else {
                    $outputClass  = '\\Symfony\\Component\\Console\\Output\\StreamOutput';
                    $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\Text';

                    $stream  = fopen($this->reportDirectory . DIRECTORY_SEPARATOR . $this->reportFileName, 'a+');
                    $output  = new $outputClass($stream);
                    $printer = new $printerClass;
                    $printer->printResult($output, $count, $this->countTests);
                }
                break;

            case 'xml':
                if ($this->oldVersion) {
                    require_once 'PHPLOC/TextUI/ResultPrinter/XML.php';

                    $printerClass = 'PHPLOC_TextUI_ResultPrinter_XML';
                } else {
                    $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\XML';
                }

                $printer = new $printerClass;
                $printer->printResult($this->reportDirectory . DIRECTORY_SEPARATOR . $this->reportFileName, $count);
                break;

            case 'csv':
                if ($this->oldVersion) {
                    require_once 'PHPLOC/TextUI/ResultPrinter/CSV.php';

                    $printerClass = 'PHPLOC_TextUI_ResultPrinter_CSV';
                } else {
                    if ($this->isOneSevenVersion) {
                        $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\CSV';
                    } else {
                        $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\CSV\\Single';
                    }
                }

                $printer = new $printerClass;
                $printer->printResult($this->reportDirectory . DIRECTORY_SEPARATOR . $this->reportFileName, $count);
                break;
        }
    }

    /**
     * @return SplFileInfo[]
     */
    protected function getFilesToCheck()
    {
        $files = array();

        if (count($this->filesToCheck) > 0) {
            foreach ($this->filesToCheck as $file) {
                $files[] = new SplFileInfo($file);
            }
        } elseif ($this->fileToCheck !== null) {
            $files = array(new SplFileInfo($this->fileToCheck));
        }

        return $files;
    }

    /**
     * @param SplFileInfo[] $files
     *
     * @return array
     */
    protected function getCountForFiles(array $files)
    {
        $analyserClass = ($this->oldVersion ? 'PHPLOC_Analyser' : '\\SebastianBergmann\\PHPLOC\\Analyser');
        $analyser      = new $analyserClass();

        return $analyser->countFiles($files, $this->countTests);
    }
}
