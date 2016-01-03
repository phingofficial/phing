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
require_once 'phing/system/io/FileSystem.php';
include_once 'phing/mappers/FileNameMapper.php';
include_once 'phing/tasks/system/PhingTask.php';

/**
 * <foreach> task
 *
 * Task definition for the foreach task.  This task takes a list with
 * delimited values, and executes a target with set param.
 *
 * Usage:
 * <foreach list="values" target="targ" param="name" delimiter="|" />
 *
 * Attributes:
 * list        --> The list of values to process, with the delimiter character,
 *                 indicated by the "delimiter" attribute, separating each value.
 * target      --> The target to call for each token, passing the token as the
 *                 parameter with the name indicated by the "param" attribute.
 * param       --> The name of the parameter to pass the tokens in as to the
 *                 target.
 * delimiter   --> The delimiter string that separates the values in the "list"
 *                 parameter.  The default is ",".
 * parallel    --> Wether to run the tasks concurrently. This required the
 *                 php pcntl. If the extension is not installed, the tasks are
 *                 executed sequentially.
 * threadcount --> Maximum number of threads / processes to use (when parallel
 *                 is enabled). The default is set to the number of cpu's, if
 *                 it can be determined (cat /proc/cpuinfo), otherwise "2"
 *
 *
 * @author    Jason Hines <jason@greenhell.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @author    Matthias Krauser <matthias@krauser.eu>
 * @version   $Id$
 * @package   phing.tasks.system
 */
class ForeachTask extends Task
{

    /** Delimter-separated list of values to process. */
    private $list;

    /** Name of parameter to pass to callee */
    private $param;

    /** Name of absolute path parameter to pass to callee */
    private $absparam;

    /** Delimiter that separates items in $list */
    private $delimiter = ',';

    /** Try to run the tasks concurrently */
    private $parallel = false;

    /** Maximum number of threads / processes  */
    private $threadcount = null;

    /**
     * Helper to create and manage thread when executing tasks concurrently
     * @var DocBlox_Parallel_Manager
     */
    protected $parallelManager;

    /**
     * PhingCallTask that will be invoked w/ calleeTarget.
     * @var PhingCallTask
     */
    protected $callee;

    /** Array of filesets */
    protected $filesets = array();

    /** Instance of mapper **/
    protected $mapperElement;

    /**
     * Array of filelists
     * @var array
     */
    protected $filelists = array();

    /**
     * Target to execute.
     * @var string
     */
    protected $calleeTarget;

    /**
     * Total number of files processed
     * @var integer
     */
    protected $total_files = 0;

    /**
     * Total number of directories processed
     * @var integer
     */
    protected $total_dirs = 0;

    public function init()
    {
        $this->callee = $this->project->createTask("phingcall");
        $this->callee->setOwningTarget($this->getOwningTarget());
        $this->callee->setTaskName($this->getTaskName());
        $this->callee->setLocation($this->getLocation());
        $this->callee->init();
    }

    /**
     * This method does the work.
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        if ($this->list === null && count($this->filesets) == 0 && count($this->filelists) == 0) {
            throw new BuildException("Need either list, nested fileset or nested filelist to iterate through");
        }
        if ($this->param === null) {
            throw new BuildException("You must supply a property name to set on each iteration in param");
        }
        if ($this->calleeTarget === null) {
            throw new BuildException("You must supply a target to perform");
        }

        if ($this->parallel === true) {
            @include_once 'phing/contrib/DocBlox/Parallel/Manager.php';
            @include_once 'phing/contrib/DocBlox/Parallel/Worker.php';
            @include_once 'phing/contrib/DocBlox/Parallel/WorkerPipe.php';
            if (!class_exists('DocBlox_Parallel_Worker')) {
                throw new BuildException(
                    'Concurrent execution depends on DocBlox being installed and on include_path.',
                    $this->getLocation()
                );
            }

            $this->parallelManager = new DocBlox_Parallel_Manager();
            if ($this->threadcount !== null) {
                $this->parallelManager->setProcessLimit($this->threadcount);
            }
        }

        $mapper = null;

        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        if (trim($this->list)) {
            $arr = explode($this->delimiter, $this->list);
            $total_entries = 0;

            foreach ($arr as $value) {
                $value = trim($value);
                $premapped = '';
                if ($mapper !== null) {
                    $premapped = $value;
                    $value = $mapper->main($value);
                    if ($value === null) {
                        continue;
                    }
                    $value = array_shift($value);
                }
                $this->log(
                    "Setting param '$this->param' to value '$value'" . ($premapped ? " (mapped from '$premapped')" : ''),
                    Project::MSG_VERBOSE
                );

                $callee = $this->createCallee();
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->param);
                $prop->setValue($value);

                if ($this->parallel === true) {
                    $worker = new DocBlox_Parallel_Worker(
                        array($callee, 'main'),
                        array($callee)
                    );
                    $this->parallelManager->addWorker($worker);
                } else {
                    $callee->main();
                }
                $total_entries++;
            }
        }

        // filelists
        foreach ($this->filelists as $fl) {
            $srcFiles = $fl->getFiles($this->project);

            $this->process(
                $fl->getDir($this->project),
                $srcFiles,
                array()
            );
        }

        // filesets
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $this->process(
                $fs->getDir($this->project),
                $srcFiles,
                $srcDirs
            );
        }

        if ($this->parallel === true) {
            $this->parallelManager->execute();
        }

        if ($this->list === null) {
            $this->log(
                "Processed {$this->total_dirs} directories and {$this->total_files} files",
                Project::MSG_VERBOSE
            );
        } else {
            $this->log(
                "Processed $total_entries entr" . ($total_entries > 1 ? 'ies' : 'y') . " in list",
                Project::MSG_VERBOSE
            );
        }
    }

    /**
     * Processes a list of files & directories
     *
     * @param Task $callee
     * @param PhingFile $fromDir
     * @param array $srcFiles
     * @param array $srcDirs
     * @param DocBlox_Parallel_Worker $this ->parallelManager
     */
    protected function process(PhingFile $fromDir, $srcFiles, $srcDirs)
    {
        $mapper = null;

        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        $filecount = count($srcFiles);
        $this->total_files += $filecount;


        for ($j = 0; $j < $filecount; $j++) {
            $callee = $this->createCallee();

            $invoke = $this->configureCallee($callee, $fromDir, $srcFiles[$j], $mapper);

            if($invoke === true) {
                $this->invokeCallee($callee);
            }
        }

        $dircount = count($srcDirs);
        $this->total_dirs += $dircount;

        for ($j = 0; $j < $dircount; $j++) {
            $callee = $this->createCallee();

            $invoke = $this->configureCallee($callee, $fromDir, $srcDirs[$j], $mapper);

            if($invoke === true) {
                $this->invokeCallee($callee);
            }
        }
    }

    /**
     * @return Task
     */
    private function createCallee()
    {
        $callee = $this->project->createTask("phingcall");
        $callee->setOwningTarget($this->getOwningTarget());
        $callee->setTaskName($this->getTaskName());
        $callee->setLocation($this->getLocation());

        $callee->init();

        $callee->setTarget($this->calleeTarget);
        $callee->setInheritAll(true);
        $callee->setInheritRefs(true);

        return $callee;
    }

    /**
     * @param Taks $callee
     * @param string $value
     * @param Mapper $mapper
     * @return boolean
     */
    private function configureCallee(Task $callee, $fromDir, $value, Mapper $mapper = null)
    {
        $premapped = "";

        if ($this->absparam) {
            $prop = $callee->createProperty();
            $prop->setOverride(true);
            $prop->setName($this->absparam);
            $prop->setValue($fromDir . FileSystem::getFileSystem()->getSeparator() . $value);
        }

        if ($mapper !== null) {
            $premapped = $value;
            $value = $mapper->main($value);
            if ($value === null) {
                return false;
            }
            $value = array_shift($value);
        }

        if ($this->param) {
            $this->log(
                "Setting param '$this->param' to value '$value'" . ($premapped ? " (mapped from '$premapped')" : ''),
                Project::MSG_VERBOSE
            );
            $prop = $callee->createProperty();
            $prop->setOverride(true);
            $prop->setName($this->param);
            $prop->setValue($value);
        }

        return true;
    }

    /**
     * @param Task $callee
     */
    protected function invokeCallee(Task $callee)
    {
        if ($this->parallel === true) {
            $worker = new DocBlox_Parallel_Worker(
                array($callee, 'main'),
                array($callee)
            );

            $this->parallelManager->addWorker($worker);
        } else {
            $callee->main();
        }
    }

    /**
     * @param $list
     */
    public function setList($list)
    {
        $this->list = (string)$list;
    }

    /**
     * @param $target
     */
    public function setTarget($target)
    {
        $this->calleeTarget = (string)$target;
    }

    /**
     * @param $param
     */
    public function setParam($param)
    {
        $this->param = (string)$param;
    }

    /**
     * @param $absparam
     */
    public function setAbsparam($absparam)
    {
        $this->absparam = (string)$absparam;
    }

    /**
     * @param $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = (string)$delimiter;
    }

    /**
     * @param boolean $parallel
     */
    public function setParallel($parallel)
    {
        $this->parallel = (boolean)$parallel;
    }

    /**
     * Sets the maximum number of threads / processes to use
     * @param int $threadcount
     */
    public function setthreadcount($threadcount)
    {
        $this->threadcount = (int)$threadcount;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @return object         The created Mapper type object
     * @throws BuildException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException("Cannot define more than one mapper", $this->location);
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * @return Property
     */
    public function createProperty()
    {
        return $this->callee->createProperty();
    }

    /**
     * Supports embedded <filelist> element.
     * @return FileList
     */
    public function createFileList()
    {
        $num = array_push($this->filelists, new FileList());

        return $this->filelists[$num - 1];
    }
}
