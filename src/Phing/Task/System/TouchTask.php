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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;
use Phing\Type\Mapper;

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
     * @var File $file
     */
    private $file;
    private $seconds = -1;
    private $dateTime;
    private $fileUtils;
    private $mkdirs = false;
    private $verbose = true;

    /** @var Mapper $mapperElement */
    private $mapperElement;

    /**
     *
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
     * @param  File $file
     * @return void
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * The new modification time of the file in milliseconds since midnight
     * Jan 1 1970. Negative values are not accepted nor are values less than
     * 1000. Note that PHP is actually based on seconds so the value passed
     * in will be divided by 1000.
     *
     * Optional, default=now
     *
     * @param  $millis
     * @return void
     */
    public function setMillis($millis)
    {
        if ($millis >= 0) {
            if ($millis >= 1000) {
                $this->seconds = (int) $millis / 1000;
            } else {
                throw new BuildException("Millis less than 1000 would be treated as 0");
            }
        } else {
            throw new BuildException("Millis attribute cannot be negative");
        }
    }

    /**
     * the new modification time of the file
     * in seconds since midnight Jan 1 1970.
     * Optional, default=now
     *
     * @param  $seconds
     * @return void
     */
    public function setSeconds($seconds)
    {
        if ($seconds >= 0) {
            $this->seconds = (int) $seconds;
        } else {
            throw new BuildException("Seconds attribute cannot be negative");
        }
    }

    /**
     * the new modification time of the file
     * in the format MM/DD/YYYY HH:MM AM or PM;
     * Optional, default=now
     *
     * @param  $dateTime
     * @return void
     */
    public function setDatetime($dateTime)
    {
        $timestmap = strtotime($dateTime);
        if (false !== $timestmap) {
            $this->dateTime = (string) $dateTime;
            $this->setSeconds($timestmap);
        } else {
            throw new BuildException("Date of ${dateTime} cannot be parsed correctly. It should be in a format parsable by PHP's strtotime() function." . PHP_EOL);
        }
    }

    /**
     * Set whether nonexistent parent directories should be created
     * when touching new files.
     *
     * @param boolean $mkdirs whether to create parent directories.
     */
    public function setMkdirs($mkdirs)
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * Set whether the touch task will report every file it creates;
     * defaults to <code>true</code>.
     *
     * @param boolean $verbose flag.
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * Execute the touch operation.
     *
     * @throws BuildException
     * @throws IOException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException("Cannot define more than one mapper", $this->getLocation());
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    protected function checkConfiguration()
    {
        $savedSeconds = $this->seconds;

        if ($this->file === null && count($this->filesets) === 0 && count($this->filelists) === 0) {
            throw new BuildException("Specify at least one source - a file, a fileset or a filelist.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Use a fileset to touch directories.");
        }

        $this->log(
            "Setting seconds to " . $savedSeconds . " from datetime attribute",
            ($this->seconds < 0 ? Project::MSG_DEBUG : Project::MSG_VERBOSE)
        );

        $this->seconds = $savedSeconds;
    }

    /**
     * Execute the touch operation.
     * @throws BuildException
     */
    public function main()
    {
        $this->checkConfiguration();
        $this->_touch();
    }

    /**
     * Does the actual work.
     */
    public function _touch()
    {
        if ($this->file !== null) {
            if (!$this->file->exists()) {
                $this->log(
                    "Creating " . $this->file,
                    $this->verbose ? Project::MSG_INFO : Project::MSG_VERBOSE
                );
                try { // try to create file
                    $this->file->createNewFile($this->mkdirs);
                } catch (IOException  $ioe) {
                    throw new BuildException(
                        "Error creating new file " . $this->file,
                        $ioe,
                        $this->getLocation()
                    );
                }
            }
        }

        $resetSeconds = false;
        if ($this->seconds < 0) {
            $resetSeconds = true;
            // Note: this function actually returns seconds, not milliseconds (e.g. 1606505920.2657)
            $this->seconds = Phing::currentTimeMillis();
        }

        if ($this->file !== null) {
            $this->touchFile($this->file);
        }

        $project = $this->getProject();
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            for ($j = 0, $_j = count($srcFiles); $j < $_j; $j++) {
                foreach ($this->getMappedFileNames((string) $srcFiles[$j]) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }

            for ($j = 0, $_j = count($srcDirs); $j < $_j; $j++) {
                foreach ($this->getMappedFileNames((string) $srcDirs[$j]) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }
        }

        // deal with the filelists
        foreach ($this->filelists as $fl) {
            $fromDir = $fl->getDir($this->getProject());

            $srcFiles = $fl->getFiles($this->getProject());

            for ($j = 0, $_j = count($srcFiles); $j < $_j; $j++) {
                foreach ($this->getMappedFileNames((string) $srcFiles[$j]) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }
        }

        if ($resetSeconds) {
            $this->seconds = -1;
        }
    }

    private function getMappedFileNames($file)
    {
        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
            $results = $mapper->main($file);
            if ($results === null) {
                return '';
            }
            $fileNames = $results;
        } else {
            $fileNames = [$file];
        }

        return $fileNames;
    }

    /**
     * @param $file
     * @throws BuildException
     */
    private function touchFile(File $file)
    {
        if (!$file->canWrite()) {
            throw new BuildException("Can not change modification date of read-only file " . (string) $file);
        }
        $file->setLastModified($this->seconds);
    }
}
