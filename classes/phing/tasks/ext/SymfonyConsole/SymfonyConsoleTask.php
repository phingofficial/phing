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
require_once dirname(__FILE__) . "/Arg.php";

/**
 * Symfony Console Task
 * @author nuno costa <nuno@francodacosta.com>
 * @license GPL
 * @version $Id$
 * @package phing.tasks.ext.symfony
 */
class SymfonyConsoleTask extends Task
{

    /**
     *
     * @var Array of Arg a collection of Arg objects
     */
    private $args = array();

    /**
     *
     * @var string the Symfony console command to execute
     */
    private $command = null;

    /**
     *
     * @var string path to symfony console application
     */
    private $console = 'php app/console';


    /**
     * sets the symfony console command to execute
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * return the symfony console command to execute
     * @return String
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * sets the path to symfony console application
     * @param string $console
     */
    public function setConsole($console)
    {
        $this->console = $console;
    }

    /**
     * returns the path to symfony console application
     * @return string
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * appends an arg tag to the arguments stack
     *
     * @return Arg Argument object
     */

    public function createArg()
    {
        $num = array_push($this->args, new Arg());
        return $this->args[$num-1];
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
    public function getCmdString() {
        $cmd = array(
                $this->console,
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
        passthru ($cmd);
    }
}
