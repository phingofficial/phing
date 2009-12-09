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

require_once 'phing/Task.php';

/**
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author  Dirk Thomas <dirk.thomas@4wdmedia.de>
 * @version $Id$
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTask extends Task {

    protected $file;    // the source file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task

    // parameters for php code sniffer
    protected $standard = 'Generic';
    protected $sniffs = array();
    protected $showWarnings = true;
    protected $verbosity = 0;
    protected $tabWidth = 0;
    protected $allowedFileExtensions = array('php');
    protected $ignorePatterns = false;
    protected $noSubdirectories = false;
    protected $configData = array();

    // parameters to customize output
    protected $showSniffs = false;
    protected $format = 'default';
    protected $formatters   = array();
    
    private $haltonerror = false;
    private $haltonwarning = false; 

    /**
     * File to be performed syntax check on
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file) {
        $this->file = $file;
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @return FileSet The created fileset object
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * Sets the standard to test for
     * @param string $standard
     */
    public function setStandard($standard)
    {
        if (DIRECTORY_SEPARATOR != '/') $standard = str_replace('/', DIRECTORY_SEPARATOR, $standard);
        $this->standard = $standard;
    }

    /**
     * Sets the sniffs which the standard should be restricted to
     * @param string $sniffs
     */
    public function setSniffs($sniffs)
    {
        $token = ' ,;';
        $sniff = strtok($sniffs, $token);
        while ($sniff !== false) {
            $this->sniffs[] = $sniff;
            $sniff = strtok($token);
        }
    }

    /**
     * Sets the flag if warnings should be shown
     * @param boolean $show
     */
    public function setShowWarnings($show)
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * Sets the verbosity level
     * @param int $level
     */
    public function setVerbosity($level)
    {
        $this->verbosity = (int)$level;
    }

    /**
     * Sets the tab width to replace tabs with spaces
     * @param int $width
     */
    public function setTabWidth($width)
    {
        $this->tabWidth = (int)$width;
    }

    /**
     * Sets the allowed file extensions when using directories instead of specific files
     * @param array $extensions
     */
    public function setAllowedFileExtensions($extensions)
    {
        $this->allowedFileExtensions = array();
        $token = ' ,;';
        $ext = strtok($extensions, $token);
        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets the ignore patterns to skip files when using directories instead of specific files
     * @param array $extensions
     */
    public function setIgnorePatterns($patterns)
    {
        $this->ignorePatterns = array();
        $token = ' ,;';
        $pattern = strtok($patterns, $token);
        while ($pattern !== false) {
            $this->ignorePatterns[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Sets the flag if subdirectories should be skipped
     * @param boolean $subdirectories
     */
    public function setNoSubdirectories($subdirectories)
    {
        $this->noSubdirectories = StringHelper::booleanValue($subdirectories);
    }

    /**
     * Creates a config parameter for this task
     *
     * @return Parameter The created parameter
     */
    public function createConfig() {
        $num = array_push($this->configData, new Parameter());
        return $this->configData[$num-1];
    }

    /**
     * Sets the flag if the used sniffs should be listed
     * @param boolean $show
     */
    public function setShowSniffs($show)
    {
        $this->showSniffs = StringHelper::booleanValue($show);
    }

    /**
     * Sets the output format
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Create object for nested formatter element.
     * @return CodeSniffer_FormatterElement
     */
    public function createFormatter () {
        $num = array_push($this->formatters, 
        new PhpCodeSnifferTask_FormatterElement());
        return $this->formatters[$num-1];
    }
    
    /**
     * Sets the haltonerror flag
     * @param boolean $value
     */
    function setHaltonerror($value)
    {
        $this->haltonerror = $value;
    }

    /**
     * Sets the haltonwarning flag
     * @param boolean $value
     */
    function setHaltonwarning($value)
    {
        $this->haltonwarning = $value;
    }
    
    /**
     * Executes PHP code sniffer against PhingFile or a FileSet
     */
    public function main() {
        if(!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (count($this->formatters) == 0) {
          // turn legacy format attribute into formatter
          $fmt = new PhpCodeSnifferTask_FormatterElement();
          $fmt->setType($this->format);
          $fmt->setUseFile(false);
          $this->formatters[] = $fmt;
        }
        
        if (!isset($this->file))
        {
            $fileList = array();
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($files as $file) {
                    $fileList[] = $dir.DIRECTORY_SEPARATOR.$file;
                }
            }
        }
        
        /* save current directory */
        $old_cwd = getcwd();

        require_once 'PHP/CodeSniffer.php';
        $codeSniffer = new PHP_CodeSniffer($this->verbosity, $this->tabWidth);
        $codeSniffer->setAllowedFileExtensions($this->allowedFileExtensions);
        if (is_array($this->ignorePatterns)) $codeSniffer->setIgnorePatterns($this->ignorePatterns);
        foreach ($this->configData as $configData) {
            $codeSniffer->setConfigData($configData->getName(), $configData->getValue(), true);
        }

        if ($this->file instanceof PhingFile) {
            $codeSniffer->process($this->file->getPath(), $this->standard, $this->sniffs, $this->noSubdirectories);

        } else {
            $codeSniffer->process($fileList, $this->standard, $this->sniffs, $this->noSubdirectories);
        }

        $this->output($codeSniffer);
        
        $report = $codeSniffer->prepareErrorReport(true);           
        
        if ($this->haltonerror && $report['totals']['errors'] > 0)
        {
            throw new BuildException('phpcodesniffer detected ' . $report['totals']['errors']. ' error' . ($report['totals']['errors'] > 1 ? 's' : ''));
        }

        if ($this->haltonwarning && $report['totals']['warnings'] > 0)
        {
            throw new BuildException('phpcodesniffer detected ' . $report['totals']['warnings'] . ' warning' . ($report['totals']['warnings'] > 1 ? 's' : ''));
        }
        
        /* reset current directory */
        chdir($old_cwd);
    }

    /**
     * Outputs the results
     * @param PHP_CodeSniffer $codeSniffer
     */
    protected function output($codeSniffer) {
        if ($this->showSniffs) {
            $sniffs = $codeSniffer->getSniffs();
            $sniffStr = '';
            foreach ($sniffs as $sniff) {
                $sniffStr .= '- ' . $sniff.PHP_EOL;
            }
            $this->log('The list of used sniffs (#' . count($sniffs) . '): ' . PHP_EOL . $sniffStr, Project::MSG_INFO);
        }

        // process output
        foreach ($this->formatters as $fe) {
          $output = '';

          switch ($fe->getType()) {
            case 'default':
              // default format goes to logs, no buffering
              $this->outputCustomFormat($codeSniffer);
              $fe->setUseFile(false);
              break;

            case 'xml':
              ob_start();
              $codeSniffer->printXMLErrorReport($this->showWarnings);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            case 'checkstyle':
              ob_start();
              $codeSniffer->printCheckstyleErrorReport($this->showWarnings);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            case 'csv':
              ob_start();
              $codeSniffer->printCSVErrorReport($this->showWarnings);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            case 'report':
              ob_start();
              $codeSniffer->printErrorReport($this->showWarnings);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            case 'summary':
              ob_start();
              $codeSniffer->printErrorReportSummary($this->showWarnings);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            case 'doc':
              ob_start();
              $codeSniffer->generateDocs($this->standard, $this->sniffs);
              $output = ob_get_contents();
              ob_end_clean();
              break;

            default:
              $this->log('Unknown output format "' . $fe->getType() . '"', Project::MSG_INFO);
              continue; //skip to next formatter in list
              break;
          } //end switch

            if (!$fe->getUseFile()) {
                // output raw to console
                echo $output;
            } else {
                // write to file
                $outputFile = $fe->getOutfile()->getPath();                  
                $check = file_put_contents($ouputFile, $output);
                if (is_bool($check) && !$check) {
                    throw new BuildException('Error writing output to ' . $outputFile);
                }
            }
        } //end foreach
    } //end output

    /**
     * Outputs the results with a custom format
     * @param PHP_CodeSniffer $codeSniffer
     */
    protected function outputCustomFormat($codeSniffer) {
        $report = $codeSniffer->prepareErrorReport($this->showWarnings);

        $files = $report['files'];
        foreach ($files as $file => $attributes) {
            $errors = $attributes['errors'];
            $warnings = $attributes['warnings'];
            $messages = $attributes['messages'];
            if ($errors > 0) {
                $this->log($file . ': ' . $errors . ' error' . ($errors > 1 ? 's' : '') . ' detected', Project::MSG_ERR);
                $this->outputCustomFormatMessages($messages, 'ERROR');
            } else {
                $this->log($file . ': No syntax errors detected', Project::MSG_VERBOSE);
            }
            if ($warnings > 0) {
                $this->log($file . ': ' . $warnings . ' warning' . ($warnings > 1 ? 's' : '') . ' detected', Project::MSG_WARN);
                $this->outputCustomFormatMessages($messages, 'WARNING');
            }
        }

        $totalErrors = $report['totals']['errors'];
        $totalWarnings = $report['totals']['warnings'];
        $this->log(count($files) . ' files where checked', Project::MSG_INFO);
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
     * @param array $messages
     * @param string $type
     */
    protected function outputCustomFormatMessages($messages, $type) {
        foreach ($messages as $line => $messagesPerLine) {
            foreach ($messagesPerLine as $column => $messagesPerColumn) {
                foreach ($messagesPerColumn as $message) {
                    $msgType = $message['type'];
                    if ($type == $msgType) {
                        $logLevel = Project::MSG_INFO;
                        if ($msgType == 'ERROR') {
                            $logLevel = Project::MSG_ERR;
                        } else if ($msgType == 'WARNING') {
                            $logLevel = Project::MSG_WARN;
                        }
                        $text = $message['message'];
                        $string = $msgType . ' in line ' . $line . ' column ' . $column . ': ' . $text;
                        $this->log($string, $logLevel);
                    }
                }
            }
        }
    }

} //end phpCodeSnifferTask

class PhpCodeSnifferTask_FormatterElement extends DataType {

  /**
   * Type of output to generate
   * @var string
   */
  protected $type      = "";

  /**
   * Output to file?
   * @var bool
   */
  protected $useFile   = true;

  /**
   * Output file.
   * @var string
   */
  protected $outfile   = "";

  /**
   * Validate config.
   */
  public function parsingComplete () {
        if(empty($this->type)) {
            throw new BuildException("Format missing required 'type' attribute.");
    }
    if ($useFile && empty($this->outfile)) {
      throw new BuildException("Format requres 'outfile' attribute when 'useFile' is true.");
    } 

  }

  public function setType ($type)  {
    $this->type = $type;
  }

  public function getType () {
    return $this->type;
  }

  public function setUseFile ($useFile) {
    $this->useFile = $useFile;
  }
  
  public function getUseFile () {
    return $this->useFile;
  }
  
  public function setOutfile (PhingFile $outfile) {
    $this->outfile = $outfile;
  }
  
  public function getOutfile () {
    return $this->outfile;
  }
  
} //end FormatterElement
