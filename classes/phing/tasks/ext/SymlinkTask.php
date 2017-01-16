<?php
/*
 *  $Id$
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

require_once "phing/Task.php";
require_once "phing/dispatch/DispatchTask.php";

/**
 * Generates symlinks based on a target / link combination.
 * Can also symlink contents of a directory, individually
 *
 * Single target symlink example:
 * <code>
 *     <symlink target="/some/shared/file" link="${project.basedir}/htdocs/my_file" />
 * </code>
 *
 * Symlink entire contents of directory
 *
 * This will go through the contents of "/my/shared/library/*"
 * and create a symlink for each entry into ${project.basedir}/library/
 * <code>
 *     <symlink link="${project.basedir}/library">
 *         <fileset dir="/my/shared/library">
 *             <include name="*" />
 *         </fileset>
 *     </symlink>
 * </code>
 *
 * @author Andrei Serdeliuc <andrei@serdeliuc.ro>
 * @extends Task
 * @version $ID$
 * @package phing.tasks.ext
 */
class SymlinkTask extends DispatchTask
{
    /** @var string $link */
    private $link;

    /** @var FileSet[] $fileSets */
    private $fileSets = [];

    /** @var string $linkFileName */
    private $linkFileName;

    /** @var  $overwrite */
    private $overwrite = false;

    /** @var bool $failonerror */
    private $failonerror = true;

    /** @var bool $executing */
    private $executing = false;

    /** @var FileUtils $FILE_UTILS */
    private static $FILE_UTILS;

    private $resource;

    /**
     * Initialize the task.
     * @throws BuildException on error.
     */
    public function init()
    {
        if (self::$FILE_UTILS === null) {
            self::$FILE_UTILS = new FileUtils();
        }
        $this->setDefaults();
    }

    /**
     * The standard method for executing any task.
     * @throws BuildException on error.
     */
    public function main()
    {
        if ($this->executing) {
            throw new BuildException('Infinite recursion detected in SymlinkTask::main()');
        }
        try {
            $this->executing = true;
            DispatchUtils::main($this);
        } finally {
            $this->executing = false;
        }
    }

    /**
     * Create a symlink.
     * @throws BuildException on error.
     */
    public function single()
    {
        try {
            if ($this->resource === null) {
                $this->handleError('Must define the resource to symlink to!');
                return;
            }
            if ($this->link === null) {
                $this->handleError('Must define the link name for symlink!');
                return;
            }
            $this->doLink($this->resource, $this->link);
        } finally {
            $this->setDefaults();
        }
    }

    /**
     * Delete a symlink.
     * @throws BuildException on error.
     */
    public function delete()
    {
        try {
            if ($this->link === null) {
                $this->handleError('Must define the link name for symlink!');
                return;
            }
            $this->log("Removing symlink: $this->link");
            self::$FILE_UTILS->resolveFile(new PhingFile('.'), $this->link)->delete();
        } catch (Exception $e) {
            $this->handleError((string)$e);
        } finally {
            $this->setDefaults();
        }
    }

    /**
     * Restore symlinks.
     * @throws BuildException on error.
     */
    public function recreate()
    {
        try {
            if (count($this->fileSets) === 0) {
                $this->handleError('File set identifying link file(s) required for action recreate');
                return;
            }
            $links = $this->loadLinks($this->fileSets);

            foreach ($links->keys() as $lnk) {
                $res = $links->getProperty($lnk);
                try {
                    $test = new PhingFile($lnk);
                    if (!$test->isLink()) {
                        $this->doLink($res, $lnk);
                    } elseif (!$test->getCanonicalPath() === (new PhingFile($res))->getCanonicalPath()) {
                        $test->delete();
                        $this->doLink($res, $lnk);
                    } // else lnk exists, do nothing
                } catch (IOException $ioe) {
                    $this->handleError('IO exception while creating link');
                }
            }
        } finally {
            $this->setDefaults();
        }
    }

    /**
     * Record symlinks.
     * @throws BuildException on error.
     */
    public function record()
    {
        try {
            if (count($this->fileSets) === 0) {
                $this->handleError('Fileset identifying links to record required');
                return;
            }
            if ($this->linkFileName === null) {
                $this->handleError('Name of file to record links in required');
                return;
            }
            // create a hashtable to group them by parent directory:
            $byDir = [];

            // get an Iterator of file objects representing links (canonical):
            foreach ($this->findLinks($this->fileSets) as $thisLink) {
                $parent = $thisLink->getParentFile();
                $v = $byDir[$parent];
                if ($v === null) {
                    $v = [];
                    $byDir[$parent] = $v;
                }
                $v[] = $thisLink;
            }
            // write a Properties file in each directory:
            foreach ($byDir as $dir => $linksInDir) {
                $linksToStore = new Properties();

                // fill up a Properties object with link and resource names:
                foreach ($linksInDir as $lnk) {
                    try {
                        $linksToStore->put($lnk->getName(), $lnk->getCanonicalPath());
                    } catch (IOException $ioe) {
                        $this->handleError("Couldn't get canonical name of parent link");
                    }
                }
                $this->writePropertyFile($linksToStore, $dir);
            }
        } finally {
            $this->setDefaults();
        }
    }

    /**
     * Return all variables to their default state for the next invocation.
     */
    private function setDefaults()
    {
        $this->resource = null;
        $this->link = null;
        $this->linkFileName = null;
        $this->failonerror = true;   // default behavior is to fail on an error
        $this->overwrite = false;    // default behavior is to not overwrite
        $this->setAction('single');      // default behavior is make a single link
        $this->fileSets = [];
    }

    /**
     * Set overwrite mode. If set to false (default)
     * the task will not overwrite existing links, and may stop the build
     * if a link already exists depending on the setting of failonerror.
     *
     * @param boolean $overwrite If true overwrite existing links.
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * Set failonerror mode. If set to true (default) the entire build fails
     * upon error; otherwise the error is logged and the build will continue.
     *
     * @param boolean $foe If true throw BuildException on error, else log it.
     */
    public function setFailOnError($foe)
    {
        $this->failonerror = $foe;
    }

    /**
     * Set the name of the link. Used when action = &quot;single&quot;.
     *
     * @param string $lnk The name for the link.
     */
    public function setLink($lnk)
    {
        $this->link = $lnk;
    }


    /**
     * Set the name of the resource to which a link should be created.
     * Used when action = &quot;single&quot;.
     *
     * @param string $src The resource to be linked.
     */
    public function setResource($src)
    {
        $this->resource = $src;
    }

    /**
     * Set the name of the file to which links will be written.
     * Used when action = &quot;record&quot;.
     *
     * @param string $lf The name of the file to write links to.
     */
    public function setLinkfilename($lf)
    {
        $this->linkFileName = $lf;
    }

    /**
     * Add a fileset to this task.
     *
     * @param FileSet $set The fileset to add.
     */
    public function addFileset(FileSet $set)
    {
        $this->fileSets[] = $set;
    }

    /**
     * Write a properties file. This method uses <code>Properties.store</code>
     * and thus may throw exceptions that occur while writing the file.
     *
     * @param Properties $properties The properties object to be written.
     * @param PhingFile $dir The directory for which we are writing the links.
     * @throws BuildException if the property file could not be written
     */
    private function writePropertyFile(Properties $properties, PhingFile $dir)
    {
        try {
            $properties->store(new PhingFile($dir, $this->linkFileName), "Symlinks from $dir");
        } catch (Exception $ioe) {
            throw new BuildException($ioe, $this->getLocation());
        }
    }

    /**
     * Handle errors based on the setting of failonerror.
     *
     * @param string $msg The message to log, or include in the
     *                  <code>BuildException</code>.
     * @throws BuildException with the message if failonerror=true
     */
    private function handleError($msg)
    {
        if ($this->failonerror) {
            throw new BuildException($msg);
        }
        $this->log($msg);
    }

    /**
     * Conduct the actual construction of a link.
     *
     * <p> The link is constructed by calling <code>Execute.runCommand</code>.
     *
     * @param string $target The path of the resource we are linking to.
     * @param string $link The name of the link we wish to make.
     * @throws BuildException when things go wrong
     */
    private function doLink($target, $link)
    {
        $linkfil = new PhingFile($link);
        if ($this->overwrite) {
            if ($linkfil->exists()) {
                try {
                    if ($linkfil->isLink() || $linkfil->isFile()) {
                        $linkfil->delete();
                        $this->log('Link removed: ' . (string)$linkfil, Project::MSG_INFO);
                    } else {
                        $linkfil->delete(true);
                        $this->log('Directory removed: ' . (string)$linkfil, Project::MSG_INFO);
                    }
                } catch (IOException $ioe) {
                    $this->getProject()->logObject(
                        $this,
                        "Unable to overwrite preexisting link or file: $link",
                        Project::MSG_INFO,
                        $ioe
                    );
                }
            }
        }

        if (!symlink($target, $link)) {
            $msg = "Could not create symlink [ $link -> $target ]";
            if ($this->failonerror) {
                throw new BuildException($msg);
            } else {
                //log at the info level, and keep going.
                $this->log($msg, Project::MSG_INFO);
            }
        }
    }

    /**
     * Find all the links in all supplied filesets.
     *
     * <p> This method is invoked when the action attribute is
     * &quot;record&quot;. This means that filesets are interpreted
     * as the directories in which links may be found.
     *
     * @param array $v The filesets specified by the user.
     * @return array A HashSet of <code>File</code> objects containing the
     *         links (with canonical parent directories).
     */
    private function findLinks($v)
    {
        $result = [];
        foreach ($v as $fs) {
            /** @var DirectoryScanner $ds */
            $ds = $fs->getDirectoryScanner($this->getProject());
            $fnd = array_merge($ds->getIncludedFiles(), $ds->getIncludedDirectories());
            $dir = $fs->getDir($this->getProject());
            foreach ($fnd as $resource) {
                try {
                    $f = new PhingFile($dir, $resource);
                    /** @var PhingFile $pf */
                    $pf = $f->getParentFile();
                    $name = $f->getName();
                    if ($pf->isLink()) {
                        $result[] = new PhingFile($pf->getCanonicalFile(), $name);
                    }
                } catch (IOException $e) {
                    $this->handleError("IOException: $resource omitted");
                }
            }
        }
        return $result;
    }

    /**
     * Load links from properties files included in one or more FileSets.
     *
     * <p> This method is only invoked when the action attribute is set to
     * &quot;recreate&quot;. The filesets passed in are assumed to specify the
     * names of the property files with the link information and the
     * subdirectories in which to look for them.
     *
     * @param array $v The <code>FileSet</code>s for this task.
     * @return Properties      The links to be made.
     */
    private function loadLinks($v)
    {
        $finalList = new Properties();
        // loop through the supplied file sets:
        foreach ($v as $fs) {
            $ds = new DirectoryScanner();
            $fs->setupDirectoryScanner($ds, $this->getProject());
            $ds->scan();
            /** @var PhingFile[] $incs */
            $incs = $ds->getIncludedFiles();
            $dir = $fs->getDir($this->getProject());

            // load included files as properties files:
            foreach ($incs as $inc) {
                $if = new PhingFile($dir, $inc);
                /** @var PhingFile $pf */
                $pf = $inc->getParentFile();
                $lnks = new Properties();
                try {
                    $lnks->load($if);
                    $pf = $pf->getCanonicalFile();
                } catch (FileNotFoundException $fnfe) {
                    $this->handleError("Unable to find {$inc}; skipping it.");
                    continue;
                } catch (IOException $ioe) {
                    $this->handleError("Unable to open $inc or its parent dir; skipping it.");
                    continue;
                }
                // Write the contents to our master list of links
                // This method assumes that all links are defined in
                // terms of absolute paths, or paths relative to the
                // working directory:
                foreach ($lnks->keys() as $key) {
                    $finalList->put((new PhingFile($pf, $key))->getAbsolutePath(), $lnks->getProperty($key));
                }
            }
        }
        return $finalList;
    }
}
