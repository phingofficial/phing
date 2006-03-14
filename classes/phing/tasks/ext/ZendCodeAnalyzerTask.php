<?php
require_once 'phing/Task.php';

/**
 * ZendCodeAnalyzerTask analyze PHP source code using the ZendCodeAnalyzer included in Zend Studio 5.1
 * 
 * Available warnings:
 * 
 * zend-error                 var: Deprecated. Please use the public/private/protected modifiers
 * var-arg-unused             Function argument 'parser' is never used.
 * var-once                   Variable 'comment' encountered only once. May be a typo?
 * var-value-unused           Value assigned to variable 'args' is never used
 * return-empty-val           Function 'set_object' has both empty return and return with value.
 * if-if-else                 In if-if-else construction else relates to the closest if. Use braces to make the code clearer.
 * bool-assign                Assignment seen where boolean expression is expected. Did you mean '==' instead of '='?
 * var-use-before-def         Variable 'matches' is used before it was assigned.
 * empty-cond                 Condition without a body
 * var-ref-notmodified        Function parameter 'parser' is passed by reference but never modified. Consider passing by value.
 * include-var                include/require with user-accessible variable can be dangerous. Consider using constant instead.
 * expr-unused                Expression result is never used
 * bad-escape                 Bad escape sequence: \/, did you mean \\/?
 * var-use-before-def-global  Global variable 'argv' is used without being assigned. You are probably relying on register_globals feature of PHP. Note that this feature is off by default.
 * call-time-ref              Call-time reference is deprecated. Define function as accepting parameter by reference instead.
 * return-noref               Function 'loadlintschema' returns reference but the value is not assigned by reference. Maybe you meant '=&' instead of '='?
 * unreach-code               Unreachable code in function 'loginUser'.
 * var-global-unused          Global variable 'fixErrors' is defined but never used.
 * break-depth                Break/continue with depth more than current nesting level. 
 * 
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @package  phing.tasks.ext
 */
class ZendCodeAnalyzerTask extends Task {
  
  protected $analyzerPath = ""; // Path to ZendCodeAnalyzer binary
  protected $file = "";  // the source file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task
  protected $warnings = array();
  protected $counter = 0;
  protected $disable = array();
  protected $enable = array();
  
  /**
   * File to be analyzed
   * 
   * @param PhingFile $file
   */
  public function setFile(PhingFile $file) {
    $this->file = $file;
  }
  
  /**
   * Path to ZendCodeAnalyzer binary
   *
   * @param string $analyzerPath
   */
  public function setAnalyzerPath($analyzerPath) {
    $this->analyzerPath = $analyzerPath;
  }
  
  /**
   * Disable warning levels. Seperate warning levels with ','
   *
   * @param string $disable
   */
  public function setDisable($disable) {
    $this->disable = explode(",", $disable);
  }
  
  /**
   * Enable warning levels. Seperate warning levels with ','
   *
   * @param string $enable
   */
  public function setEnable($enable) {
    $this->enable = explode(",", $enable);
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
   * Analyze against PhingFile or a FileSet
   */
  public function main() {
    if(!isset($this->analyzerPath)) {
      throw new BuildException("Missing attribute 'analyzerPath'");
    }
    if(!isset($this->file) and count($this->filesets) == 0) {
      throw new BuildException("Missing either a nested fileset or attribute 'file' set");
    }
    
    if($this->file instanceof PhingFile) {
      $this->analyze($this->file->getPath());
    } else { // process filesets
      $project = $this->getProject();
      foreach($this->filesets as $fs) {
      	$ds = $fs->getDirectoryScanner($project);
      	$files = $ds->getIncludedFiles();
      	$dir = $fs->getDir($this->project)->getPath();
      	foreach($files as $file) {
	  $this->analyze($dir.DIRECTORY_SEPARATOR.$file);
      	}
      }
    }
    $this->log("Number of findings: ".$this->counter, PROJECT_MSG_INFO);
  }

  /**
   * Analyze file
   *
   * @param string $file
   * @return void
   */
  protected function analyze($file) {
    if(file_exists($file)) {
      if(is_readable($file)) {
      	
      	// Construct shell command
      	$cmd = $this->analyzerPath." ";
      	foreach($this->enable as $enable) { // Enable warning levels
      		$cmd .= " --enable $enable ";
      	}
      	foreach($this->disable as $disable) { // Disable warning levels
      		$cmd .= " --disable $disable ";
      	}
      	$cmd .= "$file 2>&1";
      	
      	// Execute command
      	$result = shell_exec($cmd);
      	$result = explode("\n", $result);
      	for($i=2, $size=count($result); $i<($size-1); $i++) {
	  $this->counter++;
	  $this->log($result[$i], PROJECT_MSG_WARN);
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