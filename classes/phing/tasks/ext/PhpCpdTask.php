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
 * A PHP CPD task. Checking PHP files for Copy&Paste code.
 *
 * @author  Timo Haberkern <timo.haberkern@fantastic-bits.de>
 * @version $Id$
 * @package phing.tasks.ext
 */
class PhpCpdTask extends Task {

    protected $file;    // the source file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task

    // parameters for php cpd
    protected $minLines = 5;
	protected $minTokens = 70;

    // parameters to customize output
    protected $format = 'default';
    protected $formatters   = array();	
    
    private $haltonerror = false;


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
     * Sets the flag if warnings should be shown
     * @param boolean $show
     */
    public function setShowWarnings($show)
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }
	
	/**
     * Sets the minimum number of identical lines (default: 5).
     * @param integer $lines
     */
    public function setMinLines($lines) 
    {
	    $this->minLines = $lines;
    }

	/**
     * Sets the  minimum number of identical tokens (default: 70).
     * @param integer $tokens
     */
    public function setMinTokens($tokens) 
    {
	    $this->minTokens = $tokens;
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
        new PhpCpdTask_FormatterElement());
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
     * Executes PHP code sniffer against PhingFile or a FileSet
     */
    public function main() {
        if(!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (count($this->formatters) == 0) {
          // turn legacy format attribute into formatter
          $fmt = new PhpCpdTask_FormatterElement();
          $fmt->setType($this->format);
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
					if (is_file($dir.DIRECTORY_SEPARATOR.$file))
					{
						$splFileInfo = new SplFileInfo($dir.DIRECTORY_SEPARATOR.$file);
						$fileList[] = $splFileInfo;
					}
                }
            }
        }
		else 
		{
			$dir = $this->getProject()->getBaseDir().DIRECTORY_SEPARATOR;
			$splFileInfo = new SplFileInfo($dir.DIRECTORY_SEPARATOR.$this->file);
			$fileList[] = $splFileInfo;
		}
		
        /* save current directory */
        $old_cwd = getcwd();

        require 'PHPCPD/Detector.php';
        $clones = PHPCPD_Detector::copyPasteDetection($fileList, $this->minLines, $this->minTokens);
		
        $this->output($clones);
        
        if ($this->haltonerror && count($clones) > 0)
        {
            throw new BuildException('phpcpd detected ' . count($clones). ' clones');
        }
        
        /* reset current directory */
        chdir($old_cwd);
    }

    /**
     * Outputs the results
     * @param PHPCPD_CloneMap $clones
     */
    protected function output($clones) {
        require 'PHPCPD/Log/XML/PMD.php';		
		require 'PHPCPD/TextUI/ResultPrinter.php';		

        // process output
        foreach ($this->formatters as $fe) {
            $output = '';            

            switch ($fe->getType()) {
                case 'default':
                    // default format goes to logs, no buffering
                    ob_start();
                    $printer = new PHPCPD_TextUI_ResultPrinter;
                    $printer->printResult($clones, $this->getProject()->getBaseDir());
                    $output = ob_get_contents();
                    ob_end_clean();

                    if (!$fe->getUseFile()) {
                        echo $output;
                    } else {
                        $outputFile = $fe->getOutfile()->getPath();
                        $check = file_put_contents($outputFile, $output);
                    }
                    break;

                case 'pmd':
                    $pmd = new PHPCPD_Log_XML_PMD($outputFile);
                    $pmd->processClones($clones);
                    break;

                default:
                    $this->log('Unknown output format "' . $fe->getType() . '"', Project::MSG_INFO);
                    continue; //skip to next formatter in list
                    break;
            } //end switch
        } //end foreach
    } //end output
} //end PhpCpdTask

class PhpCpdTask_FormatterElement extends DataType {

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
