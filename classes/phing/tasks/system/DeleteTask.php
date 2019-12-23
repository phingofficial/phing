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
 * Deletes a file or directory, or set of files defined by a fileset.
 *
 * @package phing.tasks.system
 */
class DeleteTask extends Task
{
    use ResourceAware;

    /**
     * @var PhingFile
     */
    protected $file;

    /**
     * @var PhingFile
     */
    protected $dir;

    /**
     * @var bool
     */
    protected $includeEmpty = false;

    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var bool
     */
    protected $failonerror = false;

    /**
     * @var int
     */
    protected $verbosity = Project::MSG_VERBOSE;

    /**
     * Set the name of a single file to be removed.
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
     * Set the directory from which files are to be deleted.
     *
     * @param PhingFile $dir
     *
     * @return void
     */
    public function setDir(PhingFile $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * Used to force listing of all names of deleted files.
     *
     * @param bool $verbosity
     *
     * @return void
     */
    public function setVerbose(bool $verbosity): void
    {
        if ($verbosity) {
            $this->verbosity = Project::MSG_INFO;
        } else {
            $this->verbosity = Project::MSG_VERBOSE;
        }
    }

    /**
     * If the file does not exist, do not display a diagnostic
     * message or modify the exit status to reflect an error.
     * This means that if a file or directory cannot be deleted,
     * then no error is reported. This setting emulates the
     * -f option to the Unix rm command. Default is false
     * meaning things are verbose
     *
     * @param bool $bool
     *
     * @return void
     */
    public function setQuiet(bool $bool): void
    {
        $this->quiet = $bool;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }

    /**
     * this flag means 'note errors to the output, but keep going'
     *
     * @param bool $bool
     *
     * @return void
     */
    public function setFailOnError(bool $bool): void
    {
        $this->failonerror = $bool;
    }

    /**
     * Used to delete empty directories.
     *
     * @param bool $includeEmpty
     *
     * @return void
     */
    public function setIncludeEmptyDirs(bool $includeEmpty): void
    {
        $this->includeEmpty = (bool) $includeEmpty;
    }

    /**
     * Delete the file(s).
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function main(): void
    {
        if (
            $this->file === null
            && $this->dir === null
            && count($this->dirsets) === 0
            && count($this->filesets) === 0
            && count($this->filelists) === 0
        ) {
            throw new BuildException(
                'At least one of the file or dir attributes, or a fileset, filelist or dirset element must be set.'
            );
        }

        if ($this->quiet && $this->failonerror) {
            throw new BuildException('quiet and failonerror cannot both be set to true', $this->getLocation());
        }

        // delete a single file
        if ($this->file !== null) {
            if ($this->file->exists()) {
                if ($this->file->isDirectory()) {
                    $this->log(
                        'Directory ' . $this->file->__toString() . ' cannot be removed using the file attribute. Use dir instead.'
                    );
                } else {
                    $this->log('Deleting: ' . $this->file->__toString());
                    try {
                        $this->file->delete();
                    } catch (Throwable $e) {
                        $message = 'Unable to delete file ' . $this->file->__toString() . ': ' . $e->getMessage();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        }

                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }
            } else {
                $message = 'Could not find file ' . $this->file->getAbsolutePath() . ' to delete.';

                if ($this->failonerror) {
                    throw new BuildException($message);
                }

                $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
            }
        }

        if ($this->dir !== null) {
            $this->dirsets[] = $this->dir;
        }
        foreach ($this->dirsets as $dirset) {
            if (!$dirset instanceof PhingFile) {
                $ds      = $dirset->getDirectoryScanner($this->getProject());
                $dirs    = $ds->getIncludedDirectories();
                $baseDir = $ds->getBasedir();
            } else {
                $dirs[0] = $dirset;
            }
            foreach ($dirs as $dir) {
                if (!$dir instanceof PhingFile) {
                    $dir = new PhingFile($baseDir, $dir);
                }
                if ($dir->exists() && $dir->isDirectory()) {
                    if ($this->verbosity === Project::MSG_VERBOSE) {
                        $this->log('Deleting directory ' . $dir->__toString());
                    }
                    $this->removeDir($dir);
                } else {
                    $message = 'Directory ' . $dir->getAbsolutePath() . ' does not exist or is not a directory.';

                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
                }
            }
        }

        // delete the files in the filelists
        foreach ($this->filelists as $fl) {
            try {
                $files = $fl->getFiles($this->project);
                $this->removeFiles($fl->getDir($this->project), $files, $empty = []);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                }

                $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }

        // delete the files in the filesets
        foreach ($this->filesets as $fs) {
            try {
                $ds    = $fs->getDirectoryScanner($this->project);
                $files = $ds->getIncludedFiles();
                $dirs  = $ds->getIncludedDirectories();
                $this->removeFiles($fs->getDir($this->project), $files, $dirs);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                }

                $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }
    }

    /**
     * Recursively removes a directory.
     *
     * @param PhingFile $d The directory to remove.
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    private function removeDir(PhingFile $d): void
    {
        $list = $d->listDir();
        if ($list === null) {
            $list = [];
        }

        foreach ($list as $s) {
            $f = new PhingFile($d, $s);
            if ($f->isDirectory()) {
                $this->removeDir($f);
            } else {
                $this->log('Deleting ' . $f->__toString(), $this->verbosity);
                try {
                    $f->delete();
                } catch (Throwable $e) {
                    $message = 'Unable to delete file ' . $f->__toString() . ': ' . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }
        $this->log('Deleting directory ' . $d->getAbsolutePath(), $this->verbosity);
        try {
            $d->delete();
        } catch (Throwable $e) {
            $message = 'Unable to delete directory ' . $d->__toString() . ': ' . $e->getMessage();
            if ($this->failonerror) {
                throw new BuildException($message);
            }

            $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
        }
    }

    /**
     * remove an array of files in a directory, and a list of subdirectories
     * which will only be deleted if 'includeEmpty' is true
     *
     * @param PhingFile $d     directory to work from
     * @param array     $files array of files to delete; can be of zero length
     * @param array     $dirs  array of directories to delete; can of zero length
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    private function removeFiles(PhingFile $d, array &$files, array &$dirs): void
    {
        if (count($files) > 0) {
            $this->log('Deleting ' . count($files) . ' files from ' . $d->__toString());
            for ($j = 0, $_j = count($files); $j < $_j; $j++) {
                $f = new PhingFile($d, $files[$j]);
                $this->log('Deleting ' . $f->getAbsolutePath(), $this->verbosity);
                try {
                    $f->delete();
                } catch (Throwable $e) {
                    $message = 'Unable to delete file ' . $f->__toString() . ': ' . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }

        if (count($dirs) > 0 && $this->includeEmpty) {
            $dirCount = 0;
            for ($j = count($dirs) - 1; $j >= 0; --$j) {
                $dir      = new PhingFile($d, $dirs[$j]);
                $dirFiles = $dir->listDir();
                if ($dirFiles === null || count($dirFiles) === 0) {
                    $this->log('Deleting ' . $dir->__toString(), $this->verbosity);
                    try {
                        $dir->delete();
                        $dirCount++;
                    } catch (Throwable $e) {
                        $message = 'Unable to delete directory ' . $dir->__toString();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        }

                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }
            }
            if ($dirCount > 0) {
                $this->log(sprintf('Deleted %d director%s from %s', $dirCount, ($dirCount == 1 ? 'y' : 'ies'), $d->__toString()));
            }
        }
    }
}
