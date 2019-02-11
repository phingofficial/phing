<?php

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

    private $mode = 0100644;

    private $userName = "";
    private $groupName = "";
    private $prefix = "";
    private $fullpath = "";
    private $preserveLeadingSlashes = false;

    /**
     * Get a list of files and directories specified in the fileset.
     *
     * @param Project $p
     * @param bool $includeEmpty
     *
     * @return array a list of file and directory names, relative to
     *               the baseDir for the project.
     *
     * @throws BuildException
     * @throws Exception
     */
    protected function getFiles($includeEmpty = true, ...$options)
    {
        if ($this->files === null) {
            $ds = $this->getDirectoryScanner($this->getProject());
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
                    if ($dir != "" && $dir !== "." && !in_array($dir, $implicitDirs)) {
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
     */
    public function setMode($octalString)
    {
        $octal = (int) $octalString;
        $this->mode = 0100000 | $octal;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * The username for the tar entry
     * This is not the same as the UID, which is
     * not currently set by the task.
     *
     * @param $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * The groupname for the tar entry; optional, default=""
     * This is not the same as the GID, which is
     * not currently set by the task.
     *
     * @param $groupName
     */
    public function setGroup($groupName)
    {
        $this->groupName = $groupName;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->groupName;
    }

    /**
     * If the prefix attribute is set, all files in the fileset
     * are prefixed with that path in the archive.
     * optional.
     *
     * @param bool $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * If the fullpath attribute is set, the file in the fileset
     * is written with that path in the archive. The prefix attribute,
     * if specified, is ignored. It is an error to have more than one file specified in
     * such a fileset.
     *
     * @param $fullpath
     */
    public function setFullpath($fullpath)
    {
        $this->fullpath = $fullpath;
    }

    /**
     * @return string
     */
    public function getFullpath()
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
    public function setPreserveLeadingSlashes($b)
    {
        $this->preserveLeadingSlashes = (boolean) $b;
    }

    /**
     * @return bool
     */
    public function getPreserveLeadingSlashes()
    {
        return $this->preserveLeadingSlashes;
    }
}
