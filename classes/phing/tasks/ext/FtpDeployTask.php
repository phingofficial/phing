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

require_once 'phing/Task.php';

/**
 * FtpDeployTask
 * 
 * Deploys a set of files to a remote FTP server.
 * 
 * 
 * Example usage:
 * <ftpdeploy host="host" port="21" username="user" password="password" dir="public_html" mode="ascii" clearfirst="true">
 *   <fileset dir=".">
 *     <include name="**"/>
 *     <exclude name="phing"/>
 *     <exclude name="build.xml"/>
 *     <exclude name="images/**.png"/>
 *     <exclude name="images/**.gif"/>
 *     <exclude name="images/**.jpg"/>
 *   </fileset>
 * </ftpdeploy>
 *
 * @author Jorrit Schippers <jorrit at ncode dot nl>
 * @version $Id$
 * @since 2.3.1
 * @package  phing.tasks.ext
 */
class FtpDeployTask extends Task
{
    private $host = null;
    private $port = 21;
    private $username = null;
    private $password = null;
    private $dir = null;
    private $filesets;
    private $completeDirMap;
    private $mode = FTP_BINARY;
    private $clearFirst = false;
    private $passive = false;
    
    public function __construct() {
        $this->filesets = array();
        $this->completeDirMap = array();
    }
    
    public function setHost($host) {
        $this->host = $host;
    }
    
    public function setPort($port) {
        $this->port = (int) $port;
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function setDir($dir) {
        $this->dir = $dir;
    }
    
    public function setMode($mode) {
        switch(strtolower($mode)) {
            case 'ascii':
                $this->mode = FTP_ASCII;
                break;
            case 'binary':
            case 'bin':
                $this->mode = FTP_BINARY;
                break;
        }
    }
    
    public function setPassive($passive)
    {
        $this->passive = (bool) $passive;
    }
    
    public function setClearFirst($clearFirst) {
        $this->clearFirst = (bool) $clearFirst;
    }
    
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }
    
    /**
     * The init method: check if Net_FTP is available
     */
    public function init() {
        require_once 'PEAR.php';

        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach($paths as $path) {
            if(file_exists($path.DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR.'FTP.php')) {
                return true;
            }
        }
        throw new BuildException('The FTP Deploy task requires the Net_FTP PEAR package.');
    }
    
    /**
     * The main entry point method.
     */
    public function main() {
        $project = $this->getProject();
        
        require_once 'Net/FTP.php';
        $ftp = new Net_FTP($this->host, $this->port);
        $ret = $ftp->connect();
        if(@PEAR::isError($ret)) {
            throw new BuildException('Could not connect to FTP server '.$this->host.' on port '.$this->port.': '.$ret->getMessage());
        } else {
            $this->log('Connected to FTP server ' . $this->host . ' on port ' . $this->port, Project::MSG_VERBOSE);
        }
        
        $ret = $ftp->login($this->username, $this->password);
        if(@PEAR::isError($ret)) {
            throw new BuildException('Could not login to FTP server '.$this->host.' on port '.$this->port.' with username '.$this->username.': '.$ret->getMessage());
        } else {
            $this->log('Logged in to FTP server with username ' . $this->username, Project::MSG_VERBOSE);
        }
        
        if ($this->passive) {
            $this->log('Setting passive mode', Project::MSG_INFO);
            $ret = $ftp->setPassive();
            if(@PEAR::isError($ret)) {
                $ftp->disconnect();
                throw new BuildException('Could not set PASSIVE mode: '.$ret->getMessage());
            }
        }

        // append '/' to the end if necessary
        $dir = substr($this->dir, -1) == '/' ? $this->dir : $this->dir.'/';
        
        if($this->clearFirst) {
            // TODO change to a loop through all files and directories within current directory
            $this->log('Clearing directory '.$dir, Project::MSG_INFO);
            $ftp->rm($dir, true);
        }
        
        // Create directory just in case
        $ret = $ftp->mkdir($dir, true);
        if(@PEAR::isError($ret)) {
            $ftp->disconnect();
            throw new BuildException('Could not create directory '.$dir.': '.$ret->getMessage());
        }
        
        $ret = $ftp->cd($dir);
        if(@PEAR::isError($ret)) {
            $ftp->disconnect();
            throw new BuildException('Could not change to directory '.$dir.': '.$ret->getMessage());
        } else {
            $this->log('Changed directory ' . $dir, Project::MSG_VERBOSE);
        }
        
        $fs = FileSystem::getFileSystem();
        $convert = $fs->getSeparator() == '\\';
        
        foreach($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir  = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();
            foreach($srcDirs as $dirname) {
                if($convert)
                    $dirname = str_replace('\\', '/', $dirname);
                $this->log('Will create directory '.$dirname, Project::MSG_VERBOSE);
                $ret = $ftp->mkdir($dirname, true);
                if(@PEAR::isError($ret)) {
                    $ftp->disconnect();
                    throw new BuildException('Could not create directory '.$dirname.': '.$ret->getMessage());
                }
            }
            foreach($srcFiles as $filename) {
                $file = new PhingFile($fromDir->getAbsolutePath(), $filename);
                if($convert)
                    $filename = str_replace('\\', '/', $filename);
                $this->log('Will copy '.$file->getCanonicalPath().' to '.$filename, Project::MSG_VERBOSE);
                $ret = $ftp->put($file->getCanonicalPath(), $filename, true, $this->mode);
                if(@PEAR::isError($ret)) {
                    $ftp->disconnect();
                    throw new BuildException('Could not deploy file '.$filename.': '.$ret->getMessage());
                }
            }
        }
        
        $ftp->disconnect();
        $this->log('Disconnected from FTP server', Project::MSG_VERBOSE);
    }
}
?>
