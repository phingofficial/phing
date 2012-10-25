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
 * Executes Sass for a particular file or directory.
 *
 * @author    Paul Stuart <pstuart2@gmail.com>
 * @version   $Id$
 * @package   phing.tasks.ext
 */
class SassTask extends Task {

	/**
	 * The Sass executable.
	 * @var string
	 */
	protected $executable = "sass";

	public function setExecutable($executable)
	{
		$this->executable = $executable;
	}

	/**
	 * Set to true if we want to only process files with the set extfilter.
	 * @var bool
	 */
	protected $verifyext = false;

	public function setVerifyext($verifyext)
	{
		$this->verifyext = $verifyext;
	}

	/**
	 * The ext type we are looking for when Verifyext is set to true.
	 *
	 * More than likely should be ".scss" or ".sass".
	 *
	 * @var string
	 */
	protected $extfilter = "";

	public function setExtfilter($extfilter)
	{
		$this->extfilter = $extfilter;
	}

	/**
	 * When true we will remove the current file ext and replace with newext.
	 * @var bool
	 */
	protected $replaceext = true;

	public function setReplacext($replaceext)
	{
		$this->replaceext = $replaceext;
	}

	/**
	 * The new ext our files will have.
	 * @var string
	 */
	protected $newext = ".css";

	public function setNewext($newext)
	{
		$this->newext = $newext;
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

	public function setOutputpath($outputpath)
	{
		$this->outputpath = $outputpath;
	}

	/**
	 * Indicates if we want to keep the directory structure of the files.
	 *
	 * When set to true we will keep the directory structure. So any input
	 * files in subdirectories will have their output file in that same
	 * sub-directory. If false, all output files will be put in the path
	 * defined by outputpath or in the directory top directory of the fileset.
	 *
	 * @var bool
	 */
	protected $keepsubdirectories = true;

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

	public function init()
	{
	}

	public function main()
	{
		if (strlen($this->executable) < 0) {
			throw new BuildException("'executable' must be defined.");
		}

		if ($this->replaceext && strlen($this->newext) < 0) {
			throw new BuildException("'newext' must be defined if 'replaceext' is true.");
		}

		if (empty($this->filesets)) {
			throw new BuildException("Missing either a nested fileset or attribute 'file'");
		}

		foreach($this->filesets as $fs) {
			$ds = $fs->getDirectoryScanner($this->project);
			$files = $ds->getIncludedFiles();
			$dir = $fs->getDir($this->project)->getPath();

			// If our output path is not defined then set it to the path of our fileset.
			if (strlen($this->outputpath) < 0) {
				$this->outputpath = $dir;
			}

			foreach($files as $file) {
				$path = $dir;
				$fullFilePath = $dir.DIRECTORY_SEPARATOR.$file;

				$this->log("Sassing path: {$path} file: {$file}");
			}
		}
	}

	protected function buildOutputFilePath($inputFile)
	{
		$outputFile = $this->outputpath.DIRECTORY_SEPARATOR;

		if ($this->keepsubdirectories === true) {
			$outputFile .= $inputFile;
		} else {
			// Explode the inputFile to strip any pathing off.
			$parts = explode(DIRECTORY_SEPARATOR, $inputFile);
			$numParts = sizeof($parts);
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
	 */
	protected function executeCommand($inputFile, $outputFile)
	{
		$output = array();
		$return = null;

		$fullCommand = $this->executable . " --force {$inputFile} {$outputFile}";

		$this->log("Executing: {$fullCommand}");
		exec($fullCommand, $output, $return);

		return array($return, $output);
	}
}
