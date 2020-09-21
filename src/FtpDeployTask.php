<?php
/**
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

namespace Phing\Tasks\Ext;

use MehrAlsNix\PhpFtp\Client;

/**
 * FtpDeployTask
 *
 * Deploys a set of files to a remote FTP server.
 *
 *
 * Example usage:
 * <ftpdeploy host="host" port="21" username="user" password="password" dir="public_html" mode="ascii" clearfirst="true" depends="false" filemode="" dirmode="">
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
 * @author      Jorrit Schippers <jorrit at ncode dot nl>
 * @contributor Steffen Sørensen <steffen@sublife.dk>
 * @since       2.3.1
 * @package     phing.tasks.ext
 */
class FtpDeployTask extends \Task
{
    use \FileSetAware;
    use \LogLevelAware;

    private $host;
    private $port = 21;
    private $ssl = false;
    private $username;
    private $password;
    private $dir;
    private $mode = \FTP_BINARY;
    private $clearFirst = false;
    private $passive = false;
    private $depends = false;
    private $dirmode = false;
    private $filemode = false;
    private $rawDataFallback = false;
    private $skipOnSameSize = false;

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param $dir
     */
    public function setDir($dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @param $mode
     */
    public function setMode($mode): void
    {
        switch (strtolower($mode)) {
            case 'ascii':
                $this->mode = \FTP_ASCII;
                break;
            case 'binary':
            case 'bin':
                $this->mode = \FTP_BINARY;
                break;
        }
    }

    /**
     * @param bool $passive
     */
    public function setPassive(bool $passive): void
    {
        $this->passive = $passive;
    }

    /**
     * @param bool $clearFirst
     */
    public function setClearFirst(bool $clearFirst): void
    {
        $this->clearFirst = $clearFirst;
    }

    /**
     * @param bool $depends
     */
    public function setDepends(bool $depends): void
    {
        $this->depends = $depends;
    }

    /**
     * @param string $filemode
     */
    public function setFilemode($filemode): void
    {
        $this->filemode = octdec(str_pad($filemode, 4, '0', STR_PAD_LEFT));
    }

    /**
     * @param $dirmode
     */
    public function setDirmode($dirmode): void
    {
        $this->dirmode = octdec(str_pad($dirmode, 4, '0', STR_PAD_LEFT));
    }

    /**
     * @param $fallback
     */
    public function setRawdatafallback(bool $fallback): void
    {
        $this->rawDataFallback = $fallback;
    }

    /**
     * @param bool|string|int $skipOnSameSize
     */
    public function setSkipOnSameSize($skipOnSameSize): void
    {
        $this->skipOnSameSize = \StringHelper::booleanValue($skipOnSameSize);
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        $project = $this->getProject();
        
        if ($this->ssl) {
            $ftp = new Client($this->host, $this->port, $this->ssl);
            $this->log('Use SSL connection', $this->logLevel);
        } else {
            $ftp = new Client($this->host, $this->port);
        }
        $this->log('Connected to FTP server ' . $this->host . ' on port ' . $this->port, $this->logLevel);

        try {
            $ftp->login($this->username, $this->password);
        } catch (\RuntimeException $e) {
            throw new \BuildException(
                'Could not login to FTP server ' . $this->host . ' on port ' . $this->port . ' with username ' . $this->username
            );
        }
        $this->log('Logged in to FTP server with username ' . $this->username, $this->logLevel);

        if ($this->passive) {
            $this->log('Setting passive mode', $this->logLevel);
            try {
                $ftp->setPassive();
            } catch (\RuntimeException $e) {
                $ftp->disconnect();
                throw new \BuildException('Could not set PASSIVE mode.');
            }
        }

        // append '/' to the end if necessary
        $dir = substr($this->dir, -1) === '/' ? $this->dir : $this->dir . '/';

        if ($this->clearFirst) {
            // TODO change to a loop through all files and directories within current directory
            $this->log('Clearing directory ' . $dir, $this->logLevel);
            $ftp->rm($dir, true);
        }

        // Create directory just in case
        try {
            $ftp->mkdir($dir, true);
        } catch (\RuntimeException $e) {
            $ftp->disconnect();
            throw new \BuildException('Could not create directory ' . $dir);
        }

        try {
            $ftp->cd($dir);
        } catch (\RuntimeException $e) {
            $ftp->disconnect();
            throw new \BuildException('Could not change to directory ' . $dir);
        }

        $this->log('Changed directory ' . $dir, $this->logLevel);

        $fs = \FileSystem::getFileSystem();
        $convert = $fs->getSeparator() === '\\';

        foreach ($this->filesets as $fs) {
            // Array for holding directory content informations
            $remoteFileInformations = [];

            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            foreach ($srcDirs as $dirname) {
                if ($convert) {
                    $dirname = str_replace('\\', '/', $dirname);
                }

                // Read directory informations, if file exists, else create the directory
                if (!$this->_directoryInformations($ftp, $remoteFileInformations, $dirname)) {
                    $this->log('Will create directory ' . $dirname, $this->logLevel);
                    try {
                        $ftp->mkdir($dirname, true);
                    } catch (\RuntimeException $e) {
                        $ftp->disconnect();
                        throw new \BuildException('Could not create directory ' . $dirname);
                    }
                }
                if ($this->dirmode) {
                    if ($this->dirmode === 'inherit') {
                        $mode = fileperms($dirname);
                    } else {
                        $mode = $this->dirmode;
                    }
                    $ftp->chmod($mode, $dirname);
                }
            }

            foreach ($srcFiles as $filename) {
                $file = new \PhingFile($fromDir->getAbsolutePath(), $filename);
                if ($convert) {
                    $filename = str_replace('\\', '/', $filename);
                }

                $local_filemtime = filemtime($file->getCanonicalPath());
                $remoteFileModificationTime = $remoteFileInformations[$filename]['stamp'] ?? 0;

                if (!$this->depends || ($local_filemtime > $remoteFileModificationTime)) {
                    if ($this->skipOnSameSize === true && $file->length() === $ftp->size($filename)) {
                        $this->log('Skipped ' . $file->getCanonicalPath(), $this->logLevel);
                        continue;
                    }

                    $this->log('Will copy ' . $file->getCanonicalPath() . ' to ' . $filename, $this->logLevel);
                    try {
                        $ftp->put($file->getCanonicalPath(), $filename, true, $this->mode);
                    } catch (\RuntimeException $e) {
                        $ftp->disconnect();
                        throw new \BuildException('Could not deploy file ' . $filename);
                    }
                }
                if ($this->filemode) {
                    if ($this->filemode === 'inherit') {
                        $mode = fileperms($filename);
                    } else {
                        $mode = $this->filemode;
                    }
                    $ftp->chmod($mode, $filename);
                }
            }
        }

        $ftp->disconnect();
        $this->log('Disconnected from FTP server', $this->logLevel);
    }

    /**
     * @param Client $ftp
     * @param $remoteFileInformations
     * @param $directory
     * @return bool
     */
    private function _directoryInformations(Client $ftp, &$remoteFileInformations, $directory): bool
    {
        try {
            $content = $ftp->ls($directory);
        } catch (\RuntimeException $e) {
            if ($this->rawDataFallback) {
                try {
                    $content = $ftp->ls($directory, Client::RAWLIST);
                } catch (\RuntimeException $e) {
                    return false;
                }
            }
            $content = $this->parseRawFtpContent($content, $directory);
        }

        if (count($content) === 0) {
            return false;
        }

        if (!empty($directory)) {
            $directory .= '/';
        }
        foreach ($content as $val) {
            if ($val['name'] !== '.' && $val['name'] !== '..') {
                $remoteFileInformations[$directory . $val['name']] = $val;
            }
        }

        return true;
    }

    /**
     * @param $content
     * @param null $directory
     * @return array
     */
    private function parseRawFtpContent($content, $directory = null): array
    {
        if (!is_array($content)) {
            return [];
        }

        $this->log('Start parsing FTP_RAW_DATA in ' . $directory);
        $retval = [];
        foreach ($content as $rawInfo) {
            $rawInfo = explode(' ', $rawInfo);
            $rawInfo2 = [];
            foreach ($rawInfo as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $rawInfo2[] = $part;
                }
            }

            $date = date_parse($rawInfo2[5] . ' ' . $rawInfo2[6] . ' ' . $rawInfo2[7]);
            if ($date['year'] === false) {
                $date['year'] = date('Y');
            }
            $date = mktime(
                $date['hour'],
                $date['minute'],
                $date['second'],
                $date['month'],
                $date['day'],
                $date['year']
            );

            $retval[] = [
                'name' => $rawInfo2[8],
                'rights' => substr($rawInfo2[0], 1),
                'user' => $rawInfo2[2],
                'group' => $rawInfo2[3],
                'date' => $date,
                'stamp' => $date,
                'is_dir' => strpos($rawInfo2[0], 'd') === 0,
                'files_inside' => (int) $rawInfo2[1],
                'size' => (int) $rawInfo2[4],
            ];
        }

        $this->log('Finished parsing FTP_RAW_DATA in ' . $directory);

        return $retval;
    }
}
