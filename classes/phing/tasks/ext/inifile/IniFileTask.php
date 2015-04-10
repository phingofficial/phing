<?php
/**
 * INI file modification task for Phing, the PHP build tool.
 *
 * Based on http://ant-contrib.sourceforge.net/tasks/tasks/inifile.html
 *
 * PHP version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */

require_once 'IniFileSet.php';
require_once 'IniFileRemove.php';
require_once 'IniFileConfig.php';

/**
 * InifileTask
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     InifileTask.php
 */
class InifileTask extends Task
{
    /**
     * Source file
     *
     * @var string|null
     */
    protected $source = null;
    /**
     * Dest file
     *
     * @var string|null
     */
    protected $dest = null;
    /**
     * Sets
     *
     * @var array
     */
    protected $sets = array();
    /**
     * Removals
     *
     * @var array
     */
    protected $removals = array();

    /**
     * IniFileConfig instance
     *
     * @var IniFileConfig
     */
    protected $ini = null;

    /**
     * Taskname for logger
     * @var string
     */
    protected $taskName = 'IniFile';

    /**
     * The main entry point method.
     *
     * @return void
     */
    public function main()
    {
        $this->ini = new IniFileConfig();
        if (!is_null($this->source) && is_null($this->dest)) {
            $this->ini->read($this->source);
        } elseif (!is_null($this->dest)) {
            $this->ini->read($this->dest);
        }
        $this->enumerateSets();
        $this->enumerateRemoves();
        if (!is_null($this->dest)) {
            $this->ini->write($this->dest);
        } elseif (!is_null($this->source)) {
            $this->ini->write($this->source);
        }
    }

    /**
     * Work through all Set commands.
     *
     * @return void
     */
    public function enumerateSets()
    {
        foreach ($this->sets as $set) {
            $value = $set->getValue();
            $key = $set->getProperty();
            $section = $set->getSection();
            $operation = $set->getOperation();
            if ($value !== null) {
                try {
                    $this->ini->set($section, $key, $value);
                } catch (Exception $ex) {
                    $this->log(
                        "Error setting value for section '" . $section .
                        "', key '" . $key ."'"
                    );
                    $this->log($ex->getMessage(), Project::MSG_DEBUG);
                }
            } elseif ($operation !== null) {
                $v = $this->ini->get($section, $key);
                // value might be wrapped in quotes with a semicolon at the end
                if (!is_numeric($v)) {
                    if (preg_match('/^"(\d*)";?$/', $v, $match)) {
                        $v = $match[1];
                    } elseif (preg_match("/^'(\d*)';?$/", $v, $match)) {
                        $v = $match[1];
                    } else {
                        $this->log(
                            "Value $v is not numeric. Skipping $operation operation."
                        );
                        continue;
                    }
                }
                if ($operation == '+') {
                    ++$v;
                } elseif ($operation == '-') {
                    --$v;
                }
                try {
                    $this->ini->set($section, $key, $v);
                } catch (Exception $ex) {
                    $this->log(
                        "Error setting value for section '" . $section .
                        "', key '" . $key ."'"
                    );
                    $this->log($ex->getMessage(), Project::MSG_DEBUG);
                }
            } else {
                echo "Value and operation is null";
            }
        }
    }

    /**
     * Work through all Remove commands.
     *
     * @return void
     */
    public function enumerateRemoves()
    {
        foreach ($this->removals as $remove) {
            $key = $remove->getProperty();
            $section = $remove->getSection();
            $this->ini->remove($section, $key);
        }
    }

    /**
     * Set Source property
     *
     * @param string $source Name of originating ini file to parse
     *
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Set Dest property
     *
     * @param string $dest Destination filename to write ini contents to.
     *
     * @return void
     */
    public function setDest($dest)
    {
        $this->dest = $dest;
    }

    /**
     * Create a Set method
     *
     * @return IniFileSet
     */
    public function createSet()
    {
        $set = new IniFileSet();
        $this->sets[] = $set;
        return $set;
    }

    /**
     * Create a Remove method
     *
     * @return IniFileRemove
     */
    public function createRemove()
    {
        $remove = new IniFileRemove();
        $this->removals[] = $remove;
        return $remove;
    }
}
