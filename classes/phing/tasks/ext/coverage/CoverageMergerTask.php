<?php
/**
 * $Id$
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

namespace phing::tasks::ext::coverage;
use phing::BuildException;
use phing::Task;
use phing::Project;
use phing::types::FileSet;

/**
 * Merges code coverage snippets into a code coverage database
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.coverage
 * @since 2.1.0
 */
class CoverageMergerTask extends Task
{
	/** the list of filesets containing the .php filename rules */
	private $filesets = array();

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
	 * Iterate over all filesets and return all the filenames.
	 *
	 * @return array an array of filenames
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
				$fs = new File(basename($ds->getBaseDir()), $file);
					
				$files[] = $fs->getAbsolutePath();
			}
		}

		return $files;
	}
	
	function main()
	{
		$files = $this->getFilenames();
		
		$this->log("Merging " . count($files) . " coverage files");

		foreach ($files as $file)
		{
			$coverageInformation = unserialize(file_get_contents($file));
			
			CoverageMerger::merge($this->project, array($coverageInformation));
		}
	}
}
?>
