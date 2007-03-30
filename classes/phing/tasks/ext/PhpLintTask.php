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
 * A PHP lint task. Checking syntax of one or more PHP source file.
 *
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @author   Stefan Priebsch <stefan.priebsch@e-novative.de>
 * @package  phing.tasks.ext
 */
class PhpLintTask extends Task {

  protected $file;  // the source file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task

  protected $haltOnFailure = false;
  protected $hasErrors = false;

  /**
   * The haltonfailure property
   * @param boolean $aValue
   */
  public function setHaltOnFailure($aValue) {
    $this->haltOnFailure = $aValue;
  }

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
   * Execute lint check against PhingFile or a FileSet
   */
  public function main() {
    if(!isset($this->file) and count($this->filesets) == 0) {
      throw new BuildException("Missing either a nested fileset or attribute 'file' set");
    }

    if($this->file instanceof PhingFile) {
      $this->lint($this->file->getPath());
    } else { // process filesets
      $project = $this->getProject();
      foreach($this->filesets as $fs) {
	      $ds = $fs->getDirectoryScanner($project);
	      $files = $ds->getIncludedFiles();
	      $dir = $fs->getDir($this->project)->getPath();
	      foreach($files as $file) {
	        $this->lint($dir.DIRECTORY_SEPARATOR.$file);
	      }
      }
    }

    if ($this->haltOnFailure && $this->hasErrors) throw new BuildException('Syntax error(s) in PHP files');
  }

  /**
   * Performs the actual syntax check
   *
   * @param string $file
   * @return void
   */
  protected function lint($file) {
    $command = 'php -l ';
    if(file_exists($file)) {
      if(is_readable($file)) {
	$message = array();
	exec($command.$file, $message);
	if(!preg_match('/^No syntax errors detected/', $message[0])) {	  
	  if (count($messages) > 1)
	  {
	    $this->log($message[1], Project::MSG_ERR);
	  }
	  else
	  {
	    $this->log("Could not parse file", Project::MSG_ERR);
	  }
	  
      $this->hasErrors = true;
	} else {
	  $this->log($file.': No syntax errors detected', Project::MSG_INFO);
	}
      } else {
	throw new BuildException('Permission denied: '.$file);
      }
    } else {
      throw new BuildException('File not found: '.$file);
    }
  }
}

?>
