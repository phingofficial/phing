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

require_once 'phing/tasks/system/MatchingTask.php';

/**
 * Base class for extracting tasks such as Unzip and Untar.
 *
 * @author    Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @version   $Revision: 1.0 $
 * @package   phing.tasks.ext
 * @since     2.2.0
 */
abstract class ExtractBaseTask extends MatchingTask {
    protected $file;
    protected $destDir;
    protected $filesets = array(); // all fileset objects assigned to this task

    /**
     * Add a new fileset.
     * @return FileSet
     */
    public function createFileSet() {
        $this->fileset = new FileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }

    /**
     * Set the name of the zip file to extract.
     * @param PhingFile $file zip file to extract
     */
    public function setFile(PhingFile $file) {
        $this->file = $file;
    }

    /**
     * This is the base directory to look in for things to zip.
     * @param PhingFile $baseDir
     */
    public function setDestDir(PhingFile $destDir) {
        $this->destDir = $destDir;
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main() {
    
        $this->validateAttributes();
        
        $destinationFileSet = new FileSet();
        $destinationFileSet->setDir($this->destDir);
        $destinationDirScanner = $destinationFileSet->getDirectoryScanner($this->project);
        $destinationFiles = $destinationDirScanner->getIncludedFiles();
        
        $filesToExtract = array();
        if ($this->file !== null) {
            if(!$this->isDestinationUpToDate($this->file)) {
                $filesToExtract[] = $this->file;
            } else {
            	$this->log('Nothing to do: ' . $this->destDir->getAbsolutePath() . ' is up to date for ' .  $this->file->getCanonicalPath(), PROJECT_MSG_INFO);
            }
        }
        
        foreach($this->filesets as $compressedArchiveFileset) {
            $compressedArchiveDirScanner = $compressedArchiveFileset->getDirectoryScanner($this->project);
            $compressedArchiveFiles = $compressedArchiveDirScanner->getIncludedFiles();
            $compressedArchiveDir = $compressedArchiveFileset->getDir($this->project);
            
            foreach ($compressedArchiveFiles as $compressedArchiveFilePath) {
                $compressedArchiveFile = new PhingFile($compressedArchiveDir, $compressedArchiveFilePath);
                if($compressedArchiveFile->isDirectory())
                {
                    throw new BuildException($compressedArchiveFile->getAbsolutePath() . ' compressed archive cannot be a directory.');
                }
                
            	if(!$this->isDestinationUpToDate($compressedArchiveFile)) {
            	   $filesToExtract[] = $compressedArchiveFile;
            	} else {
            		$this->log('Nothing to do: ' . $this->destDir->getAbsolutePath() . ' is up to date for ' .  $compressedArchiveFile->getCanonicalPath(), PROJECT_MSG_INFO);
            	}
            }
        }
        
        foreach ($filesToExtract as $compressedArchiveFile) {
            $this->extractArchive($compressedArchiveFile);
        }
    }
    
    abstract protected function extractArchive(PhingFile $compressedArchiveFile);
    
    abstract protected function isDestinationUpToDate(PhingFile $compressedArchiveFile);
    
    /**
     * Validates attributes coming in from XML
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function validateAttributes() {
    
        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("Specify at least one source compressed archive - a file or a fileset.");
        }

        if ($this->destDir === null) {
            throw new BuildException("Destdir must be set.");
        }
        
        if ($this->destDir !== null && $this->destDir->exists() && !$this->destDir->isDirectory()) {
            throw new BuildException("Destdir must be a directory.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Compressed archive file cannot be a directory.");
        }
        
        if ($this->file !== null && !$this->file->exists()) {
        	throw new BuildException("Could not find compressed archive file " . $this->file->__toString() . " to extract.");
        }
    }
    
}