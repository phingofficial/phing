<?php
/**
 * $Id: CoverageSetupTask.php,v 1.9 2005/05/26 13:10:52 mrook Exp $
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
require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/Writer.php';
require_once 'phing/system/util/Properties.php';

/**
 * Initializes a code coverage database
 *
 * @author Michiel Rook <michiel@trendserver.nl>
 * @version $Id: CoverageSetupTask.php,v 1.9 2005/05/26 13:10:52 mrook Exp $
 * @package phing.tasks.ext.coverage
 * @since 2.1.0
 */
class CoverageSetupTask extends Task
{
	/** the list of filesets containing the .php filename rules */
	private $filesets = array();

	/** the filename of the coverage database */
	private $database = "coverage.db";

	/**
	 * Add a new fileset containing the .php files to process
	 *
	 * @param FileSet the new fileset containing .php files
	 */
	function addFileSet(FileSet $fileset)
	{
		$this->filesets[] = $fileset;
	}

	/**
	 * Sets the filename of the coverage database to use
	 *
	 * @param string the filename of the database
	 */
	function setDatabase($database)
	{
		$this->database = $database;
	}

	/**
	 * Iterate over all filesets and return the filename of all files
	 * that end with .php. This is to avoid loading an xml file
	 * for example.
	 *
	 * @return array an array of (basedir, filenames) pairs
	 */
	private function getFilenames()
	{
		$files = array();

		foreach ($this->filesets as $fileset)
		{
			$ds = $fileset->getDirectoryScanner($this->project);
			$ds->scan();

			$includedFiles = $ds->getIncludedFiles();

			foreach ($includedFiles as $file)
			{
				if (strstr($file, ".php"))
				{
					$fs = new PhingFile(basename($ds->getBaseDir()), $file);
					
					$files[] = array('key' => strtolower($fs->getAbsolutePath()), 'fullname' => $fs->getAbsolutePath(), 'basename' => $file);
				}
			}
		}

		return $files;
	}
	
	function init()
	{
		include_once 'PHPUnit2/Framework/TestCase.php';
		if (!class_exists('PHPUnit2_Framework_TestCase')) {
			throw new Exception("PHPUnit2Task depends on PEAR PHPUnit2 package being installed.");
		}
	}

	function main()
	{
		$files = $this->getFilenames();

		$props = new Properties();

		foreach ($files as $file)
		{
			$basename = $file['basename'];
			$fullname = $file['fullname'];
			$filename = $file['key'];
			
			$props->setProperty($filename, serialize(array('basename' => $basename, 'fullname' => $fullname, 'coverage' => array())));
		}

		$dbfile = new PhingFile($this->database);

		$props->store($dbfile);

		$this->project->setProperty('coverage.database', $dbfile->getAbsolutePath());
	}
}
?>
