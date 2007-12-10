<?php
/**
 * $Id: SummaryPHPUnit2ResultFormatter.php 142 2007-02-04 14:06:00Z mrook $
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

require_once "phing/Task.php";
require_once 'phing/system/io/PhingFile.php';

/**
 * ManifestTask
 * 
 * Generates a simple MANIFEST file with optional checksums
 * 
 * @author David Persson <davidpersson at qeweurope dot org>
 * @since 2.3.1
 */
class ManifestTask extends Task
{
	var $taskname = 'manifest';
	
    /**
     * The target file passed in the buildfile.
     */
    private $file = null;
    
	private $filesets = array();

    /**
     * Enable/Disable checksuming (md5)
     */
	private $checksum = false;
	

    /**
     * The setter for the attribute "file"
     */
    public function setFile(PhingFile $file) {
        $this->file = $file;
    }

    /**
     * The setter for the attribute "checksum"
     */
    public function setChecksum($bool) {
        $this->checksum = $bool;
    }
    
    /**
     * Nested creator, creates a FileSet for this task
     *
     * @access  public
     * @return  object  The created fileset object
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * The init method: Do init steps.
     */
    public function init() {
      // nothing to do here
    }

    /**
     * do the work
     */
    public function main() {
      	$project = $this->getProject();
        $this->validateAttributes();
        
		$this->log("Building Manifest: " . $this->file->__toString(), Project::MSG_INFO);
		
		foreach($this->filesets as $fs) {
			
			$dir = $fs->getDir($this->project);

            $ds = $fs->getDirectoryScanner($project);
            $fromDir  = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();			

			foreach($ds->getIncludedFiles() as $file_path) {
				$line = $file_path;
				if($this->checksum) {
					$line .= "\t".md5_file($dir.'/'.$file_path);
				}
				$line .= "\n";
				$manifest[] = $line;
			}
			
		}
		
		file_put_contents($this->file,$manifest);
        
    }

    /**
     * Validates attributes coming in from XML
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function validateAttributes() {
    
        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("Specify at least sources and destination - a file or a fileset.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Destination file cannot be a directory.");
        }
        
    }     
}
?>
