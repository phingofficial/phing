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
 * Touch a file and/or fileset(s); corresponds to the Unix touch command.
 *
 * If the file to touch doesn't exist, an empty one is created.
 *
 * @package phing.tasks.system
 */
class TouchTask extends Task
{
    use FileListAware;
    use FileSetAware;

    /**
     * @var PhingFile $file
     */
    private $file;
    private $millis = -1;
    private $dateTime;

    /**
     * @var FileUtils
     */
    private $fileUtils;
    private $mkdirs  = false;
    private $verbose = true;

    /**
     * @throws IOException
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileUtils = new FileUtils();
    }

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     *
     * @param PhingFile $file
     *
     * @return void
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
    }

    /**
     * the new modification time of the file
     * in milliseconds since midnight Jan 1 1970.
     * Optional, default=now
     *
     * @param int|string $millis
     *
     * @return void
     */
    public function setMillis($millis): void
    {
        $this->millis = (int) $millis;
    }

    /**
     * the new modification time of the file
     * in the format MM/DD/YYYY HH:MM AM or PM;
     * Optional, default=now
     *
     * @param string $dateTime
     *
     * @return void
     */
    public function setDatetime(string $dateTime): void
    {
        $this->dateTime = (string) $dateTime;
    }

    /**
     * Set whether nonexistent parent directories should be created
     * when touching new files.
     *
     * @param bool $mkdirs whether to create parent directories.
     *
     * @return void
     */
    public function setMkdirs(bool $mkdirs): void
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * Set whether the touch task will report every file it creates;
     * defaults to <code>true</code>.
     *
     * @param bool $verbose flag.
     *
     * @return void
     */
    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    /**
     * Execute the touch operation.
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     */
    public function main(): void
    {
        $savedMillis = $this->millis;

        if ($this->file === null && count($this->filesets) === 0 && count($this->filelists) === 0) {
            throw new BuildException('Specify at least one source - a file, a fileset or a filelist.');
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException('Use a fileset to touch directories.');
        }

        try { // try to touch file
            if ($this->dateTime !== null) {
                $this->setMillis(strtotime($this->dateTime));
                if ($this->millis < 0) {
                    throw new BuildException(sprintf('Date of %s results in negative milliseconds value relative to epoch (January 1, 1970, 00:00:00 GMT).', $this->dateTime));
                }
            }
            $this->_touch();
        } catch (Throwable $ex) {
            throw new BuildException('Error touch()ing file', $ex, $this->getLocation());
        }

        $this->millis = $savedMillis;
    }

    /**
     * Does the actual work.
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function _touch(): void
    {
        if ($this->file !== null) {
            if (!$this->file->exists()) {
                $this->log(
                    'Creating ' . $this->file->__toString(),
                    $this->verbose ? Project::MSG_INFO : Project::MSG_VERBOSE
                );
                try { // try to create file
                    $this->file->createNewFile($this->mkdirs);
                } catch (IOException  $ioe) {
                    throw new BuildException(
                        'Error creating new file ' . $this->file->__toString(),
                        $ioe,
                        $this->getLocation()
                    );
                }
            }
        }

        $resetMillis = false;
        if ($this->millis < 0) {
            $resetMillis  = true;
            $this->millis = Phing::currentTimeMillis();
        }

        if ($this->file !== null) {
            $this->touchFile($this->file);
        }

        // deal with the filesets
        foreach ($this->filesets as $fs) {
            $ds      = $fs->getDirectoryScanner($this->getProject());
            $fromDir = $fs->getDir($this->getProject());

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();

            for ($j = 0, $_j = count($srcFiles); $j < $_j; $j++) {
                $this->touchFile(new PhingFile($fromDir, (string) $srcFiles[$j]));
            }

            for ($j = 0, $_j = count($srcDirs); $j < $_j; $j++) {
                $this->touchFile(new PhingFile($fromDir, (string) $srcDirs[$j]));
            }
        }

        // deal with the filelists
        foreach ($this->filelists as $fl) {
            $fromDir = $fl->getDir($this->getProject());

            $srcFiles = $fl->getFiles($this->getProject());

            for ($j = 0, $_j = count($srcFiles); $j < $_j; $j++) {
                $this->touchFile(new PhingFile($fromDir, (string) $srcFiles[$j]));
            }
        }

        if ($resetMillis) {
            $this->millis = -1;
        }
    }

    /**
     * @param PhingFile $file
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException
     * @throws Exception
     */
    private function touchFile(PhingFile $file): void
    {
        if (!$file->canWrite()) {
            throw new BuildException('Can not change modification date of read-only file ' . $file->__toString());
        }
        $file->setLastModified($this->millis);
    }
}
