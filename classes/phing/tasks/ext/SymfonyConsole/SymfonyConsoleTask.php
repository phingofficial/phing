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

declare(strict_types=1);

/**
 * Symfony Console Task
 *
 * @author  nuno costa <nuno@francodacosta.com>
 * @license GPL
 * @package phing.tasks.ext.symfony
 */
class SymfonyConsoleTask extends Task
{
    /**
     * @var Arg[] a collection of Arg objects
     */
    private $args = [];

    /**
     * @var string the Symfony console command to execute
     */
    private $command = null;

    /**
     * @var string path to symfony console application
     */
    private $console = 'app/console';

    /**
     * @var string property to be set
     */
    private $propertyName = null;

    /**
     * Whether to check the return code.
     *
     * @var bool
     */
    private $checkreturn = false;

    /**
     * Is the symfony cli debug mode set? (true by default)
     *
     * @var bool
     */
    private $debug = true;

    /**
     * @var bool $silent
     */
    private $silent = false;

    /**
     * sets the symfony console command to execute
     *
     * @param string $command
     *
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * return the symfony console command to execute
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * sets the path to symfony console application
     *
     * @param string $console
     *
     * @return void
     */
    public function setConsole(string $console): void
    {
        $this->console = $console;
    }

    /**
     * returns the path to symfony console application
     *
     * @return string
     */
    public function getConsole(): string
    {
        return $this->console;
    }

    /**
     * Set the name of the property to store the application output in
     *
     * @param string $property
     *
     * @return void
     */
    public function setPropertyName(string $property): void
    {
        $this->propertyName = $property;
    }

    /**
     * Whether to check the return code.
     *
     * @param bool $checkreturn If the return code shall be checked
     *
     * @return void
     */
    public function setCheckreturn(bool $checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * Whether to set the symfony cli debug mode
     *
     * @param bool $debug If the symfony cli debug mode is set
     *
     * @return void
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Get if the symfony cli debug mode is set
     *
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSilent(bool $flag): void
    {
        $this->silent = $flag;
    }

    /**
     * @return bool
     */
    public function getSilent(): bool
    {
        return $this->silent;
    }

    /**
     * appends an arg tag to the arguments stack
     *
     * @return Arg Argument object
     */
    public function createArg(): Arg
    {
        $num = array_push($this->args, new Arg());

        return $this->args[$num - 1];
    }

    /**
     * return the argumments passed to this task
     *
     * @return Arg[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Check if the no-debug option was added via args
     *
     * @return bool
     */
    private function isNoDebugArgPresent(): bool
    {
        foreach ($this->args as $arg) {
            if ($arg->getName() == 'no-debug') {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the command string to be executed
     *
     * @return string
     */
    public function getCmdString(): string
    {
        // Add no-debug arg if it isn't already present
        if (!$this->debug && !$this->isNoDebugArgPresent()) {
            $this->createArg()->setName('no-debug');
        }
        $cmd = [
            Commandline::quoteArgument($this->console),
            $this->command,
            implode(' ', $this->args),
        ];
        $cmd = implode(' ', $cmd);

        return $cmd;
    }

    /**
     * executes the synfony console application
     *
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        $cmd = $this->getCmdString();

        $this->silent ?: $this->log('executing ' . $cmd);
        $return = null;
        $output = [];
        exec($cmd, $output, $return);

        $lines = implode("\r\n", $output);

        $this->silent ?: $this->log($lines, Project::MSG_INFO);

        if ($this->propertyName != null) {
            $this->project->setProperty($this->propertyName, $lines);
        }

        if ($return != 0 && $this->checkreturn) {
            $this->log('Task exited with code: ' . $return, Project::MSG_ERR);
            throw new BuildException('SymfonyConsole execution failed');
        }
    }
}
