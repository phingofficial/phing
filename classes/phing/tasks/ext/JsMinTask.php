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

require_once 'phing/Task.php';

/**
 * Task to minimy javascript files.
 *
 * @author   Frank Kleine <mikey@stubbles.net>
 * @version  $Revision$
 * @package  phing.tasks.ext
 */
class stubJsMinTask extends Task
{
    /**
     * path to JSmin
     *
     * @var  string
     */
    protected $jsMinPath;
    /**
     * the source files
     *
     * @var  FileSet
     */
    protected $filesets    = array();
    /**
     * Whether the build should fail, if
     * errors occured
     *
     * @var boolean
     */
    protected $failonerror = false;
    /**
     * directory to put minified javascript files into
     *
     * @var  string
     */
    protected $targetDir;

    /**
     * sets the path where JSmin can be found
     *
     * @param  string  $jsMinPath
     */
    public function setJsMinPath($jsMinPath)
    {
        $this->jsMinPath = $jsMinPath;
    }

    /**
     *  Nested creator, adds a set of files (nested fileset attribute).
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num - 1];
    }

    /**
     * Whether the build should fail, if an error occured.
     *
     * @param boolean $value
     */
    public function setFailonerror($value)
    {
        $this->failonerror = $value;
    }

    /**
     * sets the directory where minified javascript files should be put inot
     *
     * @param  string  $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        return true;
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        if (class_exists('JSMin', false) == false) {
            require_once $this->jsMinPath;
        }
        
        foreach ($this->filesets as $fs) {
            try {
                $files    = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
                $fullPath = realpath($fs->getDir($this->project));
                foreach ($files as $file) {
                    $this->log('Minifying file ' . $file);
                    try {
                        $target = $this->targetDir . '/' . str_replace($fullPath, '', str_replace('.js', '-min.js', $file));
                        if (file_exists(dirname($target)) == false) {
                            mkdir(dirname($target), 0700, true);
                        }
                        
                        file_put_contents($target, JSMin::minify(file_get_contents($fullPath . '/' . $file)));
                    } catch (JSMinException $jsme) {
                        $this->log("Could not minify file $file: " . $jsme->getMessage(), Project::MSG_ERR);
                    }
                }
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                } else {
                    $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }
    }
}
?>