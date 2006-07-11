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

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/MergeMapper.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/lib/Zip.php';

/**
 * Creates a zip archive using PEAR Archive_Zip (which is presently unreleased
 * and included with Phing).
 *
 * @author    Michiel Rook <michiel.rook@gmail.com>
 * @version   $Revision: 1.2 $
 * @package   phing.tasks.ext
 * @since     2.1.0
 */
class ZipTask extends MatchingTask {
    
    private $zipFile;
    private $baseDir;

    private $filesets = array();
    private $fileSetFiles = array();

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
     * Set is the name/location of where to create the zip file.
     * @param PhingFile $destFile The output of the zip
     */
    public function setDestFile(PhingFile $destFile) {
        $this->zipFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to zip.
     * @param PhingFile $baseDir
     */
    public function setBasedir(PhingFile $baseDir) {
        $this->baseDir = $baseDir;
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main() {
    
        if ($this->zipFile === null) {
            throw new BuildException("zipfile attribute must be set!", $this->getLocation());
        }

        if ($this->zipFile->exists() && $this->zipFile->isDirectory()) {
            throw new BuildException("zipfile is a directory!", $this->getLocation());
        }

        if ($this->zipFile->exists() && !$this->zipFile->canWrite()) {
            throw new BuildException("Can not write to the specified zipfile!", $this->getLocation());
        }

        // shouldn't need to clone, since the entries in filesets
        // themselves won't be modified -- only elements will be added
        $savedFileSets = $this->filesets;
        
        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException("basedir does not exist!", $this->getLocation());
                }

                // add the main fileset to the list of filesets to process.
                $mainFileSet = new FileSet($this->fileset);
                $mainFileSet->setDir($this->baseDir);
                $this->filesets[] = $mainFileSet;
            }

            if (empty($this->filesets)) {
                throw new BuildException("You must supply either a basedir "
                                         . "attribute or some nested filesets.",
                                         $this->getLocation());
            }                        
            
            // check if zip is out of date with respect to each
            // fileset
            $upToDate = true;
            foreach($this->filesets as $fs) {
            	$ds = $fs->getDirectoryScanner($this->project);
            	$files = $ds->getIncludedFiles();
                if (!$this->archiveIsUpToDate($files, $fs->getDir($this->project))) {
                    $upToDate = false;
                }
                for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
                    if ($this->zipFile->equals(new PhingFile($fs->getDir($this->project), $files[$i]))) {
                        throw new BuildException("A zip file cannot include itself", $this->getLocation());
                    }
                }
            }
            
            if ($upToDate) {
                $this->log("Nothing to do: " . $this->zipFile->__toString() . " is up to date.", PROJECT_MSG_INFO);
                return;
            }

            $this->log("Building zip: " . $this->zipFile->__toString(), PROJECT_MSG_INFO);
            
            $zip = new Archive_Zip($this->zipFile->getAbsolutePath());
            
            foreach($this->filesets as $fs) {                                
            	$ds = $fs->getDirectoryScanner($this->project);
            	$files = $ds->getIncludedFiles();

                // FIXME 
                // Current model is only adding directories implicitly.  This
                // won't add any empty directories.  Perhaps modify FileSet::getFiles()
                // to also include empty directories.  Not high priority, since non-inclusion
                // of empty dirs is probably not unexpected behavior for ZipTask.
                $fsBasedir = $fs->getDir($this->project);
                $filesToZip = array();
                for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
                    $f = new PhingFile($fsBasedir, $files[$i]);
                    $filesToZip[] = $f->getAbsolutePath();                        
                }
                $zip->add($filesToZip, array('remove_path' => $fsBasedir->getCanonicalPath()));
            }
                         
                
        } catch (IOException $ioe) {
                $msg = "Problem creating ZIP: " . $ioe->getMessage();
                $this->filesets = $savedFileSets;
                throw new BuildException($msg, $ioe, $this->getLocation());
        }
        
        $this->filesets = $savedFileSets;
    }
           
    /**
     * @param array $files array of filenames
     * @param PhingFile $dir
     * @return boolean
     */
    protected function archiveIsUpToDate($files, $dir) {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->zipFile->getAbsolutePath());
        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }
   
}
