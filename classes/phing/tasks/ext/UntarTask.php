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

require_once 'phing/tasks/ext/ExtractBaseTask.php';

/**
 * Extracts one or several tar archives using PEAR Archive_Tar
 *
 * @author    Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @version   $Revision: 1.0 $
 * @package   phing.tasks.ext
 * @since     2.2.0
 */
class UntarTask extends ExtractBaseTask {
    
    /**
     * Ensures that PEAR lib exists.
     */
    public function init() {
        include_once 'Archive/Tar.php';
        if (!class_exists('Archive_Tar')) {
            throw new BuildException("You must have installed the PEAR Archive_Tar class in order to use UntarTask.");
        }
    }
    
    protected function extractArchive(PhingFile $tarfile)
    {
        $this->log("Extracting tar file: " . $tarfile->__toString() . ' to ' . $this->destDir->__toString(), PROJECT_MSG_INFO);
        
    	try {
        	$tar = $this->initTar($tarfile);
        	if(!$tar->extract($this->destDir->getAbsolutePath())) {
        	   throw new BuildException('Failed to extract tar file: ' . $tar->errorInfo(true));
        	}
        } catch (IOException $ioe) {
            $msg = "Could not extract tar file: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }
    
    /**
     * @param array $files array of filenames
     * @param PhingFile $dir
     * @return boolean
     */
    protected function isDestinationUpToDate(PhingFile $tarfile) {
        if (!$tarfile->exists()) {
        	throw new BuildException("Could not find file " . $tarfile->__toString() . " to untar.");
        }
        
        $tar = $this->initTar($tarfile);
        $tarContents = $tar->listContent();
        if(is_array($tarContents)) {
            /* Get first file/dir to match against destination directory path to find
               when the file was last unziped */
            $firstPathInfo = current($tarContents);
            $firstPath = new PhingFile($this->destDir, $firstPathInfo['filename']);
            
            $fileSystem = FileSystem::getFileSystem();
            if(!$firstPath->exists() || $fileSystem->compareMTimes($tarfile->getCanonicalPath(), $firstPath->getCanonicalPath()) == 1) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Init a Archive_Tar class with correct compression for the given file.
     *
     * @param PhingFile $tarfile
     * @return Archive_Tar the tar class instance
     */
    private function initTar(PhingFile $tarfile)
    {
        $compression = null;
        $tarfileName = $tarfile->getName();
        $mode = substr($tarfileName, strrpos($tarfileName, '.'));
        switch($mode) {
            case '.gz':
                $compression = 'gz';
                break;
            case '.bz2':
                $compression = 'bz2';
                break;
            case '.tar':
                break;
            default:
                $this->log('Ignoring unknown compression mode: ' . $mode, PROJECT_MSG_WARN);
        }
        
    	return new Archive_Tar($tarfile->getAbsolutePath(), $compression);
    }
}