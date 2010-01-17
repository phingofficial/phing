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
 * list      --> The list of values to process, with the delimiter character,
 *               indicated by the "delimiter" attribute, separating each value.
 * target    --> The target to call for each token, passing the token as the
 *               parameter with the name indicated by the "param" attribute.
 * param     --> The name of the parameter to pass the tokens in as to the
 *               target.
 * delimiter --> The delimiter string that separates the values in the "list"
 *               parameter.  The default is ",".
 *
 * @author    Jason Hines <jason@greenhell.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision$
 * @package   phing.tasks.system
 */
class ForeachTask extends Task {
    
    /** Delimter-separated list of values to process. */
    private $list;
    
    /** Name of parameter to pass to callee */
    private $param;
    
    /** Name of absolute path parameter to pass to callee */
    private $absparam;
    
    /** Delimiter that separates items in $list */
    private $delimiter = ',';
    
    /**
     * PhingCallTask that will be invoked w/ calleeTarget.
     * @var PhingCallTask
     */
    private $callee;
    
    /** Array of filesets */
    private $filesets = array();
    
    /**
     * Target to execute.
     * @var string
     */
    private $calleeTarget;

    function init() {
        $this->callee = $this->project->createTask("phingcall");
        $this->callee->setOwningTarget($this->getOwningTarget());
        $this->callee->setTaskName($this->getTaskName());
        $this->callee->setLocation($this->getLocation());
        $this->callee->init();
    }

    /**
     * This method does the work.
     * @return void
     */   
    function main() {
        if ($this->list === null && count($this->filesets) == 0) {
            throw new BuildException("Need either list or nested fileset to iterate through");
        }
        if ($this->param === null) {
            throw new BuildException("You must supply a property name to set on each iteration in param");
        }
        if ($this->calleeTarget === null) {
            throw new BuildException("You must supply a target to perform");
        }

        $callee = $this->callee;
        $callee->setTarget($this->calleeTarget);
        $callee->setInheritAll(true);
        $callee->setInheritRefs(true);
        
        if (trim($this->list)) {
            $arr = explode($this->delimiter, $this->list);
        
            foreach ($arr as $value) {
                $value = trim($value);
                $this->log("Setting param '$this->param' to value '$value'", Project::MSG_VERBOSE);
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->param);
                $prop->setValue($value);
                $callee->main();
            }
        }

        $total_files = 0;
        $total_dirs  = 0;

        // filesets
        foreach ($this->filesets as $fs) {
            $ds       = $fs->getDirectoryScanner($this->project);
            $fromDir  = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();

            $this->log(count($srcDirs) . ' directories and ' . count($srcFiles) . ' files', Project::MSG_VERBOSE);

            $filecount = count($srcFiles);
            $total_files = $total_files + $filecount;
            for ($j = 0; $j < $filecount; $j++) {
                $value = $srcFiles[$j];
                if ($this->param) {
                    $this->log("Setting param '$this->param' to value '$value'", Project::MSG_VERBOSE);
                    $prop = $callee->createProperty();
                    $prop->setOverride(true);
                    $prop->setName($this->param);
                    $prop->setValue($value);
                }

                if ($this->absparam) {
                    $prop = $callee->createProperty();
                    $prop->setOverride(true);
                    $prop->setName($this->absparam);
                    $prop->setValue($fromDir . FileSystem::getFileSystem()->getSeparator() . $value);
                }

                $callee->main();
            }

            $dircount = count($srcDirs);
            $total_dirs = $total_dirs + $dircount;
            for ($j = 0; $j <  $dircount; $j++) {
                $value = $srcDirs[$j];
                if ($this->param) {
                    $this->log("Setting param '$this->param' to value '$value'", Project::MSG_VERBOSE);
                    $prop = $callee->createProperty();
                    $prop->setOverride(true);
                    $prop->setName($this->param);
                    $prop->setValue($value);
                }

                if ($this->absparam) {
                    $prop = $callee->createProperty();
                    $prop->setOverride(true);
                    $prop->setName($this->absparam);
                    $prop->setValue($fromDir . FileSystem::getFileSystem()->getSeparator() . $value);
                }

                $callee->main();
            }
        }
    }

    function setList($list) {
        $this->list = (string) $list;
    }

    function setTarget($target) {
        $this->calleeTarget = (string) $target;
    }

    function setParam($param) {
        $this->param = (string) $param;
    }

    function setAbsparam($absparam) {
        $this->absparam = (string) $absparam;
    }

    function setDelimiter($delimiter) {
        $this->delimiter = (string) $delimiter;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * @return Property
     */
    function createProperty() {
        return $this->callee->createProperty();
    }

}
