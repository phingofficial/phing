<?php

/**
 * $Id: PHPDocumentorTask.php 144 2007-02-05 15:19:00Z hans $
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
 * Task to run PhpDocumentor.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: PHPDocumentorTask.php 144 2007-02-05 15:19:00Z hans $
 * @package phing.tasks.ext.phpdoc
 */	
class PhPDocumentorTask extends Task
{
	
	/**
	 * Title for browser window / package index.
	 *
	 * @var string
	 */
	private $title;
	
	/**
	 * The target directory for output files.
	 *
	 * @var PhingFile
	 */
	private $destdir;

	/**
	 * Filesets for files to parse.
	 *
	 * @var array FileSet[]
	 */
	private $filesets = array();
		
	/**
	 * Package output format.
	 *
	 * @var string 
	 */
	private $output;

	/**
	 * Whether to generate sourcecode for each file parsed.
	 *
	 * @var boolean
	 */
	private $linksource = false;
	
	/**
	 * Whether to parse private members.
	 *
	 * @var boolean
	 */
	private $parsePrivate = false;

	/**
	 * Whether to parse hidden files.
	 *
	 * @var boolean
	 */
	private $parseHiddenFiles = false;
	
	/**
	 * Whether to use javadoc descriptions (more primitive).
	 *
	 * @var boolean
	 */
	private $javadocDesc = false;
	
	/**
	 * Base directory for locating template files.
	 *
	 * @var PhingFile
	 */
	private $templateBase;
	
	/**
	 * Wheter to suppress output.
	 * 
	 * @var boolean
	 */
	private $quiet = false;
	
	/**
	 * Comma-separated list of packages to output.
	 * @var string
	 */
	private $packages;
	
	/**
	 * Set the title for the generated documentation
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Set the destination directory for the generated documentation
	 */
	public function setDestdir(PhingFile $destdir) {
		$this->destdir = $destdir;
	}

	/**
	 * Set the output format (e.g. HTML:Smarty:PHP).
	 * @param string $output
	 */		
	public function setOutput($output) {
		$this->output = $output;
	}

	/**
	 * Set whether to generate sourcecode for each file parsed
	 * @param boolean
	 */
	public function setLinksource($linksource) {
		$this->linksource = $linksource;
	}

	/**
	 * Should private members/classes be documented
	 * @param boolean
	 */
	public function setParseprivate($parseprivate) {
		$this->parseprivate = $parseprivate;
	}
	
	/**
	 * WHether to parse hidden files.
	 *
	 * @param boolean $parsehidden
	 */
	public function setParsehiddenfiles($parsehidden) {
		$this->parseHiddenFiles = $parsehidden;
	}
	
	/**
	 * Whether to use javadoc descriptions (more primitive).
	 * @param boolean
	 */
	public function setJavadocdesc($javadoc) {
		$this->javadocDesc = $javadoc;
	}
	
	/**
	 * Set (comma-separated) list of packages to output.
	 *
	 * @param string $packages
	 */
	public function setPackages($packages)
	{
		$this->packages = $packages;
	}
	    
    /**
	 * Creates a FileSet.
	 * @return FileSet
	 */
    public function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }         
 	
    /**
     * Searches include_path for PhpDocumentor install and adjusts include_path appropriately.
     * @throws BuildException - if unable to find PhpDocumentor on include_path
     */
    protected function findPhpDocumentorInstall()
    {
    	$found = null;
    	foreach(explode(PATH_SEPARATOR, get_include_path()) as $path) {
    		$testpath = $path . DIRECTORY_SEPARATOR . 'PhpDocumentor';
    		if (file_exists($testpath)) {
    			$found = $testpath;
    			break;
    		}
    	}
    	if (!$found) {
    		throw new BuildException("PhpDocumentor task depends on PhpDocumentor being installed and on include_path.", $this->getLocation());
    	}
    	// otherwise, adjust the include_path to path to include the PhpDocumentor directory ... 
		set_include_path(get_include_path() . PATH_SEPARATOR . $found);
		include_once ("phpDocumentor/Setup.inc.php");
		if (!class_exists('phpDocumentor_setup')) {
			throw new BuildException("Error including PhpDocumentor setup class file.");
		}
    }
    
	/**
	 * Load the necessary environment for running PhpDoc.
	 *
	 * @throws BuildException - if the phpdoc classes can't be loaded.
	 */
	public function init()
	{
		$this->findPhpDocumentorInstall();
        include_once 'phing/tasks/ext/phpdoc/PhingPhpDocumentorSetup.php';
	}
	
	/**
	 * Main entrypoint of the task
	 */
	function main()
	{
		$this->validate();
		$phpdoc = new PhingPhpDocumentorSetup();
		$this->setPhpDocumentorOptions($phpdoc);
		//$phpdoc->readCommandLineSettings();
		$phpdoc->setupConverters($this->output);
		$phpdoc->createDocs();		
	}
	
	/**
	 * Validates that necessary minimum options have been set.
	 * @throws BuildException if validation doesn't pass
	 */
	protected function validate()
	{
		if (!$this->destdir) {
			throw new BuildException("You must specify a destdir for phpdoc.", $this->getLocation());
		}
		if (!$this->output) {
			throw new BuildException("You must specify an output format for phpdoc (e.g. HTML:frames:default).", $this->getLocation());
		}
		if (empty($this->filesets)) {
			throw new BuildException("You have not specified any files to include (<fileset>) for phpdoc.", $this->getLocation());
		}
	}
	
	/**
	 * Sets the options on the passed-in phpdoc setup object.
	 * @param PhingPhpDocumentorSetup $phpdoc
	 */
	protected function setPhpDocumentorOptions(PhingPhpDocumentorSetup $phpdoc)
	{
		
		// Title MUST be set first ... (because it re-initializes the internal state of the PhpDocu renderer)
		if ($this->title) {
			$phpdoc->setTitle($this->title);
		}
		
		if ($this->parseprivate) {
			$phpdoc->parsePrivate();
		}
		
		if ($this->parseHiddenFiles) {
			$phpdoc->parseHiddenFiles();
		}
		
		if ($this->javadocDesc) {
			$phpdoc->setJavadocDesc();
		}
		
		if ($this->quiet) {
			$phpdoc->setQuietMode();
		}
		
		if ($this->destdir) {
			$this->log("Setting target dir to: " . $this->destdir->getAbsolutePath());
			$phpdoc->setTargetDir($this->destdir->getAbsolutePath());
		}
		
		
		
		if ($this->packages) {
			$phpdoc->setPackageOutput($this->packages);
		}
		
		if ($this->templateBase) {
			$phpdoc->setTemplateBase($this->templateBase->getAbsolutePath());
		}
		
		if ($this->linksource) {
			$phpdoc->setGenerateSourcecode($this->linksource);
		}
		
		// append any files in filesets
		$filesToParse = array();
		foreach($this->filesets as $fs) {		    
	        $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
	        foreach($files as $filename) {
	        	 $f = new PhingFile($fs->getDir($this->project), $filename);
	        	 $filesToParse[] = $f->getAbsolutePath();
	        }
		}
		
		//print_r(implode(",", $filesToParse));
		$phpdoc->setFilesToParse(implode(",", $filesToParse));
		
	}
	
}
