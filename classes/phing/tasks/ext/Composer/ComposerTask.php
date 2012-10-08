<?php

require_once "phing/Task.php";
require_once dirname(__FILE__) . "/Arg.php";

use Phing\Tasks\Ext\Composer\Arg;
/**
 * Composer Task
 * Run composer straight from phing
 *
 * @author nuno costa <nuno@francodacosta.com>
 * @license MIT
 *
 */
class ComposerTask extends \Task
{
    /**
     * @var string the path to php interperter
     */
    private $php = 'php';
    /**
     *
     * @var Array of Arg a collection of Arg objects
     */
    private $args = array();

    /**
     *
     * @var string the Composer command to execute
     */
    private $command = null;

    /**
     *
     * @var string path to Composer application
     */
    private $composer = 'composer.phar';

    /**
     * Sets the path to php executable.
     *
     * @param string $php
     */
    public function setPhp($php)
    {
        $this->php = $php;
    }

    /**
     * gets the path to php executable.
     *
     * @return string
     */
    public function getPhp()
    {
        return $this->php;
    }
    /**
     * sets the Composer command to execute
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * return the Composer command to execute
     * @return String
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * sets the path to Composer application
     * @param string $console
     */
    public function setComposer($console)
    {
        $this->composer = $console;
    }

    /**
     * returns the path to Composer application
     * @return string
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * appends an arg tag to the arguments stack
     *
     * @return Arg Argument object
     */

    public function createArg()
    {
        $num = array_push($this->args, new Arg());
        return $this->args[$num - 1];
    }

    /**
     * return the argumments passed to this task
     * @return array of Arg()
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Gets the command string to be executed
     * @return string
     */
    public function getCmdString()
    {
        $cmd = array(
                $this->php,
                $this->composer,
                $this->command,
                implode(' ', $this->args)
        );
        $cmd = implode(' ', $cmd);
        return $cmd;
    }
    /**
     * executes the synfony consile application
     */
    public function main()
    {

        $cmd = $this->getCmdString();
        $this->log("executing $cmd");


        $composerFile = new SplFileInfo($this->getComposer());
        if (false === $composerFile->isExecutable()
                || false === $composerFile->isFile()) {
            throw new BuildException(sprintf('Composer binary not found, path is "%s"', $composerFile));
        }

        $return = 0;
        passthru($cmd, $return);

        if ($return > 0) {
            throw new BuildException("Composer execution failed");
        }
    }
}
