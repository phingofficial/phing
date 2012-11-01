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
 * Executes Sass for a particular fileset.
 *
 * @author    Paul Stuart <pstuart2@gmail.com>
 * @version   $Id$
 * @package   phing.tasks.ext
 */
class SassTask extends Task {

	/**
	 * Contains the path info of our file to allow us to parse.
	 * @var array
	 */
	protected $pathInfo = null;

	/**
	 * This flag means 'note errors to the output, but keep going'
	 * @var bool
	 */
	protected $failonerror = true;

	/**
	 * Sets the failonerror flag. Default: true
	 *
	 * @param bool $failonerror
	 *
	 * @access public
	 */
	public function setFailonerror($failonerror) {
		$this->failonerror = $failonerror;
	}

	/**
	 * The Sass executable.
	 * @var string
	 */
	protected $executable = "sass";

	/**
	 * Sets the executable to use for sass. Default: sass
	 *
	 * The default assumes sass is in your path. If not you can provide the full
	 * path to sass.
	 *
	 * @param string $executable
	 *
	 * @access public
	 */
	public function setExecutable($executable)
	{
		$this->executable = $executable;
	}

	/**
	 * The ext type we are looking for when Verifyext is set to true.
	 *
	 * More than likely should be "scss" or "sass".
	 *
	 * @var string
	 */
	protected $extfilter = "";

	/**
	 * Sets the extfilter. Default: <none>
	 *
	 * This will filter the fileset to only process files that match
	 * this extension. This could also be done with the fileset.
	 *
	 * @param string $extfilter
	 *
	 * @access public
	 */
	public function setExtfilter($extfilter)
	{
		$this->extfilter = trim($extfilter, " .");
	}

	/**
	 * Additional flags to pass to sass.
	 *
	 * @var string
	 */
	protected $flags = "";

	/**
	 * Additional flags to pass to sass.
	 *
	 * Command will be:
	 * sass {$flags} {$inputfile} {$outputfile}
	 *
	 * @param string $flags
	 *
	 * @access public
	 */
	public function setFlags($flags)
	{
		$this->flags = trim($flags);
	}

	/**
	 * When true we will remove the current file ext.
	 * @var bool
	 */
	protected $removeoldext = true;

	/**
	 * Sets the removeoldext flag. Default: true
	 *
	 * This will cause us to strip the existing extension off the output
	 * file.
	 *
	 * @param bool $removeoldext
	 *
	 * @access public
	 */
	public function setRemoveoldext($removeoldext)
	{
		$this->removeoldext = $removeoldext;
	}

	/**
	 * The new ext our files will have.
	 * @var string
	 */
	protected $newext = "css";

	/**
	 * Sets the newext value. Default: css
	 *
	 * This is the extension we will add on to the output file regardless
	 * of if we remove the old one or not.
	 *
	 * @param string $newext
	 *
	 * @access public
	 */
	public function setNewext($newext)
	{
		$this->newext = trim($newext, " .");
	}

	/**
	 * The path to send our output files to.
	 *
	 * If not defined they will be created in the same directory the
	 * input is from.
	 *
	 * @var string
	 */
	protected $outputpath = "";

	/**
	 * Sets the outputpath value. Default: <none>
	 *
	 * This will force the output path to be something other than
	 * the path of the fileset used.
	 *
	 * @param string $outputpath
	 *
	 * @access public
	 */
	public function setOutputpath($outputpath)
	{
		$this->outputpath = rtrim(trim($outputpath), DIRECTORY_SEPARATOR);
	}

	/**
	 * Indicates if we want to keep the directory structure of the files.
	 *
	 * @var bool
	 */
	protected $keepsubdirectories = true;

	/**
	 * Sets the keepsubdirectories value. Default: true
	 *
	 * When set to true we will keep the directory structure. So any input
	 * files in subdirectories will have their output file in that same
	 * sub-directory. If false, all output files will be put in the path
	 * defined by outputpath or in the directory top directory of the fileset.
	 *
	 * @param bool $keepsubdirectories
	 *
	 * @access public
	 */
	public function setKeepsubdirectories($keepsubdirectories)
	{
		$this->keepsubdirectories = $keepsubdirectories;
	}


	/**
	 * The fileset we will be running Sass on.
	 * @var array
	 */
	protected $filesets = array();

	/**
	 * Nested creator, creates a FileSet for this task
	 *
	 * @return FileSet The created fileset object
	 */
	public function createFileSet()
	{
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}

	/**
	 * Init
	 *
	 * @access public
	 */
	public function init()
	{
	}

	/**
	 * Our main execution of the task.
	 *
	 * @throws BuildException
	 * @throws Exception
	 *
	 * @access public
	 */
	public function main()
	{
		if (strlen($this->executable) < 0) {
			throw new BuildException("'executable' must be defined.");
		}

		if (empty($this->filesets)) {
			throw new BuildException("Missing either a nested fileset or attribute 'file'");
		}

		$specifiedOutputPath = (strlen($this->outputpath) > 0);

		foreach($this->filesets as $fs) {
			$ds = $fs->getDirectoryScanner($this->project);
			$files = $ds->getIncludedFiles();
			$dir = $fs->getDir($this->project)->getPath();

			// If our output path is not defined then set it to the path of our fileset.
			if ($specifiedOutputPath === false) {
				$this->outputpath = $dir;
			}

			foreach($files as $file) {

				$fullFilePath = $dir.DIRECTORY_SEPARATOR.$file;
				$this->pathInfo = pathinfo($file);

				if (strlen($this->extfilter) == 0 || $this->extfilter == $this->pathInfo['extension']) {
					$outputFile = $this->buildOutputFilePath($file);
					$output = null;

					try {
						$output = $this->executeCommand($fullFilePath, $outputFile);
						if ($output[0] !== 0 && $this->failonerror) {
							throw new BuildException("Result returned as not 0. Result: {$output[0]}");
						}
					} catch (Exception $e) {
						if ($this->failonerror) {
							throw $e;
						} else {
							$this->log("Result: {$output[0]}");
						}
					}
				}
			}
		}
	}

	/**
	 * Builds the full path to the output file based on our settings.
	 *
	 * @param string $inputFile
	 *
	 * @return string
	 *
	 * @access protected
	 */
	protected function buildOutputFilePath($inputFile)
	{
		$outputFile = $this->outputpath.DIRECTORY_SEPARATOR;

		$subpath = trim($this->pathInfo['dirname'], " .");

		if ($this->keepsubdirectories === true && strlen($subpath) > 0) {
			$outputFile .= $subpath.DIRECTORY_SEPARATOR;
		}

		$outputFile .= $this->pathInfo['filename'];

		if (!$this->removeoldext) {
			$outputFile .= "." . $this->pathInfo['extension'];
		}

		if (strlen($this->newext) > 0) {
			$outputFile .= "." . $this->newext;
		}

		return $outputFile;
	}

	/**
	 * Executes the command and returns return code and output.
	 *
	 * @param $inputFile
	 * @param $outputFile
	 *
	 * @return array array(return code, array with output)
	 *
	 * @access protected
	 */
	protected function executeCommand($inputFile, $outputFile)
	{
		// Prevent over-writing existing file.
		if ($inputFile == $outputFile) {
			throw new BuildException("Input file and output file are the same!");
		}

		$output = array();
		$return = null;

		$fullCommand = $this->executable;

		if (strlen($this->flags) > 0) {
			$fullCommand .= " {$this->flags}";
		}

		$fullCommand .= " {$inputFile} {$outputFile}";

		$this->log("Executing: {$fullCommand}");
		exec($fullCommand, $output, $return);

		return array($return, $output);
	}
}
