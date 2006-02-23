<?php
require_once 'phing/Task.php';

/**
 * A PHP lint task. Checking syntax of one or more PHP source file.
 *
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @package  phing.tasks.ext
 */
class PhpLintTask extends Task {

  protected $file;  // the source file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task

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
	  $this->log($message[1], PROJECT_MSG_ERR);
	} else {
	  $this->log($file.': No syntax errors detected', PROJECT_MSG_INFO);
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