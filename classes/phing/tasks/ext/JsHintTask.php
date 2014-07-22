<?php
/*
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
 * JsHintTask
 *
 * Checks the JavaScript code using JSHint
 * See http://www.jshint.com/
 *
 * @author Martin Hujer <mhujer@gmail.com>
 * @package phing.tasks.ext
 * @version $Id$
 * @since 2.6.2
 */
class JsHintTask extends Task
{

    /**
     * The source file (from xml attribute)
     * 
     * @var string
     */
    protected $file;

    /**
     * All fileset objects assigned to this task
     * 
     * @var unknown
     */
    protected $filesets = array();

    /**
     * Should the build fail on JSHint errors
     * 
     * @var boolean
     */
    private $haltOnError = false;
    
    /**
     * Should the build fail on JSHint warnings
     *
     * @var boolean
     */
    private $haltOnWarning = false;

    /**
     * Path where the the report in Checkstyle format should be saved
     * 
     * @var string
     */
    private $checkstyleReportPath;

    /**
     * File to be performed syntax check on
     * 
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file) {
        $this->file = $file;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @return void
     */
    public function addFileSet(FileSet $fs) {
        $this->filesets[] = $fs;
    }

    public function setHaltOnError($haltOnError) {
        $this->haltOnError = $haltOnError;
    }

    public function setHaltOnWarning($haltOnWarning) {
        $this->haltOnWarning = $haltOnWarning;
    }

    public function setCheckstyleReportPath($checkstyleReportPath) {
        $this->checkstyleReportPath = $checkstyleReportPath;
    }

    public function main() {
        if (!isset($this->file) && count($this->filesets) === 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }
        
        if (!isset($this->file)) {
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
        } else {
            $fileList = array($this->file);
        }
        
        $this->_checkJsHintIsInstalled();

        $command = 'jshint --reporter=checkstyle ' . implode(' ', $fileList);
        $output = array();
        exec($command, $output);
        $output = implode(PHP_EOL, $output);
        $xml = simplexml_load_string($output);

        $projectBasedir = $this->_getProjectBasedir();
        $errorsCount = 0;
        $warningsCount = 0;
        foreach ($xml->file as $file) {
            $fileAttributes = $file->attributes();
            $fileName = (string) $fileAttributes['name'];
            foreach ($file->error as $error) {
                $attrs = current((array) $error->attributes());
                
                if ($attrs['severity'] === 'error') {
                    $errorsCount++;
                } elseif ($attrs['severity'] === 'warning') {
                    $warningsCount++;
                } else {
                    throw new BuildException(sprintf('Unknown severity "%s"', $attrs['severity']));
                }
                $e = sprintf(
                    '%s: line %d, col %d, %s',
                    str_replace($projectBasedir, '', $fileName),
                    $attrs['line'],
                    $attrs['column'],
                    $attrs['message']
                );
                $this->log($e);
            }
        }
        
        $message = sprintf(
            'JSHint detected %d errors and %d warnings.',
            $errorsCount,
            $warningsCount
        );
        if ($this->haltOnError && $errorsCount) {
            throw new BuildException($message);
        } elseif ($this->haltOnWarning && $warningsCount) {
            throw new BuildException($message);
        } else {
            $this->log('');
            $this->log($message);
        }
        
        if ($this->checkstyleReportPath) {
            file_put_contents($this->checkstyleReportPath, $output);
            $this->log('');
            $this->log('Checkstyle report saved to ' . $this->checkstyleReportPath);
        }
    }
    
    /**
     * @return Path to the project basedir
     */
    private function _getProjectBasedir() {
        return $this->getProject()->getBaseDir()->getAbsolutePath() . DIRECTORY_SEPARATOR;
    }

    /**
     * Checks, wheter the JSHint can be executed
     */
    private function _checkJsHintIsInstalled() {
        exec('jshint -v', $output, $return);
        if ($return !== 0) {
            throw new BuildException('JSHint is not installed!');
        }
    }
}
