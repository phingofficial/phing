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

/**
 * Composer Task
 * Run composer straight from phing
 *
 * @author nuno costa <nuno@francodacosta.com>
 * @license MIT
 * @version $Id$
 * @package phing.tasks.ext
 */
class ComposerTask extends Task
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
     * @var Commandline
     */
    private $commandLine =null;
    /**
     *
     * @var string path to Composer application
     */
    private $composer = 'composer.phar';

    public function __construct()
    {
        $this->commandLine = new Commandline();
    }

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
     * creates a nested arg task
     *
     * @return Arg Argument object
     */

    public function createArg()
    {
        return $this->commandLine->createArgument();
    }

    /**
     * Gets the command string to be executed
     * @return string
     */
    private function prepareCommand()
    {
        $this->commandLine->setExecutable($this->getPhp());

        $composerCommand = $this->commandLine->createArgument(true);
        $composerCommand->setValue($this->getCommand());

        $composerPath = $this->commandLine->createArgument(true);
        $composerPath->setValue($this->getComposer());

    }
    /**
     * executes the synfony console application
     */
    public function main()
    {

        $this->prepareCommand();
        $this->log("executing $this->commandLine");

        $composerFile = new SplFileInfo($this->getComposer());
        if (false === $composerFile->isExecutable()
                || false === $composerFile->isFile()) {
            throw new BuildException(sprintf('Composer binary not found, path is "%s"', $composerFile));
        }

        $return = 0;
        passthru($this->commandLine, $return);

        if ($return > 0) {
            throw new BuildException("Composer execution failed");
        }
    }
}
