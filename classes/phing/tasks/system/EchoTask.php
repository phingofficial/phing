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

declare(strict_types=1);

/**
 * Echos a message to the logging system or to a file
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Andreas Aderhold, andi@binarycloud.com
 * @package phing.tasks.system
 */
class EchoTask extends Task
{
    use DirSetAware;
    use FileSetAware;

    protected $msg = '';

    protected $file = '';

    protected $append = false;

    /**
     * @var string
     */
    protected $level = 'info';

    /**
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        switch ($this->level) {
            case 'error':
                $loglevel = Project::MSG_ERR;
                break;
            case 'warning':
                $loglevel = Project::MSG_WARN;
                break;
            case 'verbose':
                $loglevel = Project::MSG_VERBOSE;
                break;
            case 'debug':
                $loglevel = Project::MSG_DEBUG;
                break;
            case 'info':
            default:
                $loglevel = Project::MSG_INFO;
                break;
        }

        $this->filesets = array_merge($this->filesets, $this->dirsets);

        if (count($this->filesets)) {
            if (trim(substr($this->msg, -1)) != '') {
                $this->msg .= "\n";
            }
            $this->msg .= $this->getFilesetsMsg();
        }

        if (empty($this->file)) {
            $this->log($this->msg, $loglevel);
        } else {
            if ($this->append) {
                $handle = fopen($this->file, 'a');
            } else {
                $handle = fopen($this->file, 'w');
            }

            fwrite($handle, $this->msg);

            fclose($handle);
        }
    }

    /**
     * Merges all filesets into a string to be echoed out
     *
     * @return string String to echo
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    protected function getFilesetsMsg(): string
    {
        $project = $this->getProject();
        $msg     = '';
        foreach ($this->filesets as $fs) {
            $ds       = $fs->getDirectoryScanner($project);
            $fromDir  = $fs->getDir($project);
            $srcDirs  = $ds->getIncludedDirectories();
            $srcFiles = $ds->getIncludedFiles();
            $msg     .= 'Directory: ' . $fromDir . ' => '
                . realpath((string) $fromDir) . "\n";
            foreach ($srcDirs as $dir) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $dir;
                $msg    .= $relPath . "\n";
            }
            foreach ($srcFiles as $file) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $file;
                $msg    .= $relPath . "\n";
            }
        }

        return $msg;
    }

    /**
     * setter for file
     *
     * @param string $file
     *
     * @return void
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * setter for level
     *
     * @param string $level
     *
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * setter for append
     *
     * @param bool $append
     *
     * @return void
     */
    public function setAppend(bool $append): void
    {
        $this->append = $append;
    }

    /**
     * setter for message
     *
     * @param string $msg
     *
     * @return void
     */
    public function setMsg(string $msg): void
    {
        $this->setMessage($msg);
    }

    /**
     * alias setter
     *
     * @param string $msg
     *
     * @return void
     */
    public function setMessage(string $msg): void
    {
        $this->msg = $msg;
    }

    /**
     * Supporting the <echo>Message</echo> syntax.
     *
     * @param string $msg
     *
     * @return void
     */
    public function addText(string $msg): void
    {
        $this->msg = $msg;
    }
}
