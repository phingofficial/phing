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
 *  Utility class that collects the functionality of the various
 *  scanDir methods that have been scattered in several tasks before.
 *
 *  The only method returns an array of source files. The array is a
 *  subset of the files given as a parameter and holds only those that
 *  are newer than their corresponding target files.
 *
 * @package phing.util
 */
class SourceFileScanner
{
    /**
     * @var FileUtils
     */
    private $fileUtils;

    /**
     * @var Task this class is working for -- for logging purposes.
     */
    private $task;

    /**
     * @param Task $task The task we should log messages through
     *
     * @throws IOException
     */
    public function __construct(Task $task)
    {
        $this->task      = $task;
        $this->fileUtils = new FileUtils();
    }

    /**
     * Restrict the given set of files to those that are newer than
     * their corresponding target files.
     *
     * @param iterable       $files   the original set of files
     * @param PhingFile      $srcDir  all files are relative to this directory
     * @param PhingFile|null $destDir target files live here. if null file names
     *                                returned by the mapper are assumed to be absolute.
     * @param FilenameMapper $mapper  knows how to construct a target file names from
     *                                source file names.
     * @param bool           $force   Boolean that determines if the files should be
     *                                forced to be copied.
     *
     * @return array
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    public function restrict(iterable &$files, PhingFile $srcDir, ?PhingFile $destDir, FileNameMapper $mapper, bool $force = false): array
    {
        $now        = time();
        $targetList = '';

        /*
          If we're on Windows, we have to munge the time up to 2 secs to
          be able to check file modification times.
          (Windows has a max resolution of two secs for modification times)
        */
        $osname = strtolower(Phing::getProperty('os.name'));

        // indexOf()
        $index = ($res = strpos($osname, 'win')) === false ? -1 : $res;
        if ($index >= 0) {
            $now += 2000;
        }

        $v = [];

        for ($i = 0, $size = count($files); $i < $size; $i++) {
            $targets = $mapper->main((string) $files[$i]);
            if (empty($targets)) {
                $this->task->log($files[$i] . " skipped - don't know how to handle it", Project::MSG_VERBOSE);
                continue;
            }

            $src = null;
            try {
                if ($srcDir === null) {
                    $src = new PhingFile($files[$i]);
                } else {
                    $src = $this->fileUtils->resolveFile($srcDir, (string) $files[$i]);
                }

                if ($src->lastModified() > $now) {
                    $this->task->log(
                        'Warning: ' . $files[$i] . ' modified in the future (' . $src->lastModified() . ' > ' . $now . ')',
                        Project::MSG_WARN
                    );
                }
            } catch (IOException $ioe) {
                $this->task->log('Unable to read file ' . $files[$i] . ' (skipping): ' . $ioe->getMessage());
                continue;
            }

            $added      = false;
            $targetList = '';

            for ($j = 0, $_j = count($targets); (!$added && $j < $_j); $j++) {
                $dest = null;
                if ($destDir === null) {
                    $dest = new PhingFile($targets[$j]);
                } else {
                    $dest = $this->fileUtils->resolveFile($destDir, $targets[$j]);
                }

                if (!$dest->exists()) {
                    $this->task->log(
                        ($files[$i] ?: '.') . ' added as ' . $dest->__toString() . " doesn't exist.",
                        Project::MSG_VERBOSE
                    );
                    $v[]   = $files[$i];
                    $added = true;
                } elseif ($src->lastModified() > $dest->lastModified()) {
                    $this->task->log(
                        $files[$i] . ' added as ' . $dest->__toString() . ' is outdated.',
                        Project::MSG_VERBOSE
                    );
                    $v[]   = $files[$i];
                    $added = true;
                } elseif ($force === true) {
                    $this->task->log(
                        $files[$i] . ' added as ' . $dest->__toString() . ' is forced to be overwritten.',
                        Project::MSG_VERBOSE
                    );
                    $v[]   = $files[$i];
                    $added = true;
                } else {
                    if (strlen($targetList) > 0) {
                        $targetList .= ', ';
                    }
                    $targetList .= $dest->getAbsolutePath();
                }
            }

            if (!$added) {
                $this->task->log(
                    $files[$i] . ' omitted as ' . $targetList . ' ' . (count(
                        $targets
                    ) === 1 ? ' is ' : ' are ') . 'up to date.',
                    Project::MSG_VERBOSE
                );
            }
        }

        return $v;
    }

    /**
     * Convenience layer on top of restrict that returns the source
     * files as PhingFile objects (containing absolute paths if srcDir is
     * absolute).
     *
     * @param iterable       $files
     * @param PhingFile      $srcDir
     * @param PhingFile      $destDir
     * @param FileNameMapper $mapper
     *
     * @return array
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function restrictAsFiles(iterable &$files, PhingFile $srcDir, PhingFile $destDir, FileNameMapper &$mapper): array
    {
        $res    = $this->restrict($files, $srcDir, $destDir, $mapper);
        $result = [];
        for ($i = 0, $resultsCount = count($res); $i < $resultsCount; $i++) {
            $result[$i] = new PhingFile($srcDir, $res[$i]);
        }

        return $result;
    }
}
