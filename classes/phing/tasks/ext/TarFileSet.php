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
 * This is a FileSet with the option to specify permissions.
 *
 * Permissions are currently not implemented by PEAR Archive_Tar,
 * but hopefully they will be in the future.
 *
 * @package phing.tasks.ext
 */
class TarFileSet extends FileSet
{
    private $files = null;

    /**
     * @var int
     */
    private $mode = 0100644;

    /**
     * @var string
     */
    private $userName = '';

    /**
     * @var string
     */
    private $groupName = '';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $fullpath = '';

    /**
     * @var bool
     */
    private $preserveLeadingSlashes = false;

    /**
     * Get a list of files and directories specified in the fileset.
     *
     * @param bool $includeEmpty
     *
     * @return array A list of file and directory names, relative to
     *               the baseDir for the project.
     *
     * @throws BuildException
     * @throws Exception
     */
    protected function getFiles(bool $includeEmpty = true): array
    {
        if ($this->files === null) {
            $ds          = $this->getDirectoryScanner($this->getProject());
            $this->files = $ds->getIncludedFiles();

            if ($includeEmpty) {
                // first any empty directories that will not be implicitly added by any of the files
                $implicitDirs = [];
                foreach ($this->files as $file) {
                    $implicitDirs[] = dirname($file);
                }

                $incDirs = $ds->getIncludedDirectories();

                // we'll need to add to that list of implicit dirs any directories
                // that contain other *directories* (and not files), since otherwise
                // we get duplicate directories in the resulting tar
                foreach ($incDirs as $dir) {
                    foreach ($incDirs as $dircheck) {
                        if (!empty($dir) && $dir == dirname($dircheck)) {
                            $implicitDirs[] = $dir;
                        }
                    }
                }

                $implicitDirs = array_unique($implicitDirs);

                // Now add any empty dirs (dirs not covered by the implicit dirs)
                // to the files array.

                foreach ($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
                    if ($dir != '' && $dir !== '.' && !in_array($dir, $implicitDirs)) {
                        // it's an empty dir, so we'll add it.
                        $this->files[] = $dir;
                    }
                }
            } // if $includeEmpty
        } // if ($this->files===null)

        return $this->files;
    }

    /**
     * A 3 digit octal string, specify the user, group and
     * other modes in the standard Unix fashion;
     * optional, default=0644
     *
     * @param string $octalString
     *
     * @return void
     */
    public function setMode(string $octalString): void
    {
        $octal      = (int) $octalString;
        $this->mode = 0100000 | $octal;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * The username for the tar entry
     * This is not the same as the UID, which is
     * not currently set by the task.
     *
     * @param string $userName
     *
     * @return void
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * The groupname for the tar entry; optional, default=""
     * This is not the same as the GID, which is
     * not currently set by the task.
     *
     * @param string $groupName
     *
     * @return void
     */
    public function setGroup(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->groupName;
    }

    /**
     * If the prefix attribute is set, all files in the fileset
     * are prefixed with that path in the archive.
     * optional.
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * If the fullpath attribute is set, the file in the fileset
     * is written with that path in the archive. The prefix attribute,
     * if specified, is ignored. It is an error to have more than one file specified in
     * such a fileset.
     *
     * @param string $fullpath
     *
     * @return void
     */
    public function setFullpath(string $fullpath): void
    {
        $this->fullpath = $fullpath;
    }

    /**
     * @return string
     */
    public function getFullpath(): string
    {
        return $this->fullpath;
    }

    /**
     * Flag to indicates whether leading `/'s` should
     * be preserved in the file names.
     * Optional, default is <code>false</code>.
     *
     * @param bool $b
     *
     * @return void
     */
    public function setPreserveLeadingSlashes(bool $b): void
    {
        $this->preserveLeadingSlashes = (bool) $b;
    }

    /**
     * @return bool
     */
    public function getPreserveLeadingSlashes(): bool
    {
        return $this->preserveLeadingSlashes;
    }
}
