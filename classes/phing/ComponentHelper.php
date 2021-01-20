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

use Phing\Phing;

/**
 * Component creation and configuration
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @package phing
 */
class ComponentHelper
{
    public const COMPONENT_HELPER_REFERENCE = "phing.ComponentHelper";

    /**
     * @var Project
     */
    private $project;

    /**
     * task definitions for this project
     *
     * @var string[]
     */
    private $taskdefs = [];

    /**
     * type definitions for this project
     *
     * @var string[]
     */
    private $typedefs = [];

    /**
     * ComponentHelper constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @param Project $project
     * @return ComponentHelper
     */
    public static function getComponentHelper(Project $project)
    {
        if ($project === null) {
            return null;
        }

        /**
         * @var ComponentHelper $componentHelper
         */
        $componentHelper = $project->getReference(self::COMPONENT_HELPER_REFERENCE);

        if ($componentHelper !== null) {
            return $componentHelper;
        }

        $componentHelper = new ComponentHelper($project);
        $project->addReference(self::COMPONENT_HELPER_REFERENCE, $componentHelper);

        return $componentHelper;
    }

    /**
     * Initializes the default tasks and data types
     */
    public function initDefaultDefinitions()
    {
        $this->initDefaultTasks();
        $this->initDefaultDataTypes();
        $this->initCustomTasks();
        $this->initCustomDataTypes();
    }

    /**
     * Adds a task definition.
     *
     * @param string $name Name of tag.
     * @param string $class The class path to use.
     * @param string $classpath The classpat to use.
     */
    public function addTaskDefinition($name, $class, $classpath = null)
    {
        if ($class === "") {
            $this->project->log("Task $name has no class defined.", Project::MSG_ERR);
        } elseif (!isset($this->taskdefs[$name])) {
            Phing::import($class, $classpath);
            $this->taskdefs[$name] = $class;
            $this->project->log("  +Task definition: $name ($class)", Project::MSG_DEBUG);
        } else {
            $this->project->log("Task $name ($class) already registered, skipping", Project::MSG_VERBOSE);
        }
    }

    /**
     * Returns the task definitions
     *
     * @return array
     */
    public function getTaskDefinitions()
    {
        return $this->taskdefs;
    }

    /**
     * Adds a data type definition.
     *
     * @param string $typeName Name of the type.
     * @param string $typeClass The class to use.
     * @param string $classpath The classpath to use.
     */
    public function addDataTypeDefinition($typeName, $typeClass, $classpath = null)
    {
        if (!isset($this->typedefs[$typeName])) {
            Phing::import($typeClass, $classpath);
            $this->typedefs[$typeName] = $typeClass;
            $this->project->log("  +User datatype: $typeName ($typeClass)", Project::MSG_DEBUG);
        } else {
            $this->project->log("Type $typeName ($typeClass) already registered, skipping", Project::MSG_VERBOSE);
        }
    }

    public static function getElementName(Project $p = null, $o = null, $brief = false)
    {
        //if ($p === null) {
        //    TODO Project::getProject($o)
        //}

        return $p === null
            ? self::getUnmappedElementName($o, $brief)
            : self::getComponentHelper($p)->getElementName(null, $o, $brief);
    }

    private static function getUnmappedElementName($c, $brief)
    {
        $clazz = new ReflectionClass($c);
        $name = $clazz->getName();

        if ($brief) {
            return $clazz->getShortName();
        }

        return $name;
    }

    /**
     * Returns the data type definitions
     *
     * @return array
     */
    public function getDataTypeDefinitions()
    {
        return $this->typedefs;
    }

    /**
     * Create a new task instance and return reference to it.
     *
     * @param  string $taskType Task name
     * @return Task           A task object
     * @throws BuildException
     */
    public function createTask($taskType)
    {
        try {
            $classname = "";
            $tasklwr = strtolower($taskType);
            foreach ($this->taskdefs as $name => $class) {
                if (strtolower($name) === $tasklwr) {
                    $classname = $class;
                    break;
                }
            }

            if ($classname === "") {
                return null;
            }

            $o = $this->createObject($classname);

            if ($o instanceof Task) {
                $task = $o;
            } else {
                $this->project->log("  (Using TaskAdapter for: $taskType)", Project::MSG_DEBUG);
                // not a real task, try adapter
                $taskA = new TaskAdapter();
                $taskA->setProxy($o);
                $task = $taskA;
            }
            $task->setProject($this->project);
            $task->setTaskType($taskType);
            // set default value, can be changed by the user
            $task->setTaskName($taskType);
            $this->project->log("  +Task: " . $taskType, Project::MSG_DEBUG);
        } catch (Exception $t) {
            throw new BuildException("Could not create task of type: " . $taskType, $t);
        }
        // everything fine return reference
        return $task;
    }

    /**
     * Creates a new condition and returns the reference to it
     *
     * @param  string $conditionType
     * @return Condition
     * @throws BuildException
     */
    public function createCondition($conditionType)
    {
        try {
            $classname = "";
            $tasklwr = strtolower($conditionType);
            foreach ($this->typedefs as $name => $class) {
                if (strtolower($name) === $tasklwr) {
                    $classname = $class;
                    break;
                }
            }

            if ($classname === "") {
                return null;
            }

            $o = $this->createObject($classname);

            if ($o instanceof Condition) {
                return $o;
            }

            throw new BuildException("Not actually a condition");
        } catch (Exception $e) {
            throw new BuildException("Could not create condition of type: " . $conditionType, $e);
        }
    }

    private function createObject(string $classname)
    {
        if ($classname === "") {
            return null;
        }

        $cls = Phing::import($classname);

        if (!class_exists($cls)) {
            throw new BuildException(
                "Could not instantiate class $cls, even though a class was specified. (Make sure that the specified class file contains a class with the correct name.)"
            );
        }

        return new $cls();
    }

    /**
     * Create a datatype instance and return reference to it
     * See createTask() for explanation how this works
     *
     * @param  string $typeName Type name
     * @return object         A datatype object
     * @throws BuildException
     *                                 Exception
     */
    public function createDataType($typeName)
    {
        try {
            $cls = "";
            $typelwr = strtolower($typeName);
            foreach ($this->typedefs as $name => $class) {
                if (strtolower($name) === $typelwr) {
                    $cls = StringHelper::unqualify($class);
                    break;
                }
            }

            if ($cls === "") {
                return null;
            }

            if (!class_exists($cls)) {
                throw new BuildException(
                    "Could not instantiate class $cls, even though a class was specified. (Make sure that the specified class file contains a class with the correct name.)"
                );
            }

            $type = new $cls();
            $this->project->log("  +Type: $typeName", Project::MSG_DEBUG);
            if (!($type instanceof DataType)) {
                throw new Exception("$class is not an instance of phing.types.DataType");
            }
            if ($type instanceof ProjectComponent) {
                $type->setProject($this->project);
            }
        } catch (Exception $t) {
            throw new BuildException("Could not create type: $typeName", $t);
        }
        // everything fine return reference
        return $type;
    }

    private function initDefaultTasks()
    {
        $taskdefs = Phing::getResourcePath("phing/tasks/defaults.properties");

        try { // try to load taskdefs
            $props = new Properties();
            $in = new PhingFile((string) $taskdefs);

            if ($in === null) {
                throw new BuildException("Can't load default task list");
            }
            $props->load($in);

            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addTaskDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load default task list");
        }
    }

    private function initDefaultDataTypes()
    {
        $typedefs = Phing::getResourcePath("phing/types/defaults.properties");

        try { // try to load typedefs
            $props = new Properties();
            $in = new PhingFile((string) $typedefs);
            if ($in === null) {
                throw new BuildException("Can't load default datatype list");
            }
            $props->load($in);

            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addDataTypeDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load default datatype list");
        }
    }

    private function initCustomTasks()
    {
        $taskdefs = Phing::getResourcePath("custom.task.properties");
        try { // try to load typedefs
            $props = new Properties();
            $in = new PhingFile((string) $taskdefs);
            if (!$in->exists()) {
                return;
            }
            $props->load($in);
            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addTaskDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load custom task list");
        }
    }

    private function initCustomDataTypes()
    {
        $typedefs = Phing::getResourcePath("custom.type.properties");
        try { // try to load typedefs
            $props = new Properties();
            $in = new PhingFile((string) $typedefs);
            if (!$in->exists()) {
                return;
            }
            $props->load($in);
            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addDataTypeDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load custom type list");
        }
    }

    public function initSubProject(ComponentHelper $helper): void
    {
        $dataTypes = $helper->getDataTypeDefinitions();
        foreach ($dataTypes as $name => $class) {
            $this->addDataTypeDefinition($name, $class);
        }
    }
}
