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
 * Executes a command on the shell.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.tasks.system
 */
class ExecTask extends Task
{
    use LogLevelAware;

    public const INVALID = PHP_INT_MAX;

    private $exitValue = self::INVALID;

    /**
     * Command to be executed
     *
     * @var string
     */
    protected $realCommand;

    /**
     * Commandline managing object
     *
     * @var Commandline
     */
    protected $commandline;

    /**
     * Working directory.
     *
     * @var PhingFile
     */
    protected $dir;

    protected $currdir;

    /**
     * Operating system.
     *
     * @var string
     */
    protected $os;

    /**
     * Whether to escape shell command using escapeshellcmd().
     *
     * @var bool
     */
    protected $escape = false;

    /**
     * Where to direct output.
     *
     * @var PhingFile
     */
    protected $output;

    /**
     * Whether to use PHP's passthru() function instead of exec()
     *
     * @var bool
     */
    protected $passthru = false;

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     *
     * @var bool
     */
    protected $logOutput = false;

    /**
     * Where to direct error output.
     *
     * @var PhingFile
     */
    protected $error;

    /**
     * If spawn is set then [unix] programs will redirect stdout and add '&'.
     *
     * @var bool
     */
    protected $spawn = false;

    /**
     * Property name to set with return value from exec call.
     *
     * @var string
     */
    protected $returnProperty;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Whether to check the return code.
     *
     * @var bool|null
     */
    protected $checkreturn = false;

    /**
     * @var string
     */
    private $osFamily;
    private $executable;
    private $resolveExecutable = false;
    private $searchPath        = false;

    /**
     * @var Environment
     */
    private $env;

    /**
     * @throws BuildException
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandline = new Commandline();
        $this->env         = new Environment();
    }

    /**
     * Main method: wraps execute() command.
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    public function main(): void
    {
        if (!$this->isValidOs()) {
            return;
        }

        try {
            $this->commandline->setExecutable($this->resolveExecutable($this->executable, $this->searchPath));
        } catch (IOException | NullPointerException $e) {
            throw new BuildException($e);
        }

        $this->prepare();
        $this->buildCommand();
        [$return, $output] = $this->executeCommand();
        $this->cleanup($return, $output);
    }

    /**
     * Prepares the command building and execution, i.e.
     * changes to the specified directory.
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws BuildException
     */
    protected function prepare(): void
    {
        if ($this->dir === null) {
            $this->dir = $this->getProject()->getBasedir();
        }

        if ($this->commandline->getExecutable() === null) {
            throw new BuildException(
                'ExecTask: Please provide "executable"'
            );
        }

        // expand any symbolic links first
        try {
            if (!$this->dir->getCanonicalFile()->exists()) {
                throw new BuildException(
                    "The directory '" . (string) $this->dir . "' does not exist"
                );
            }
            if (!$this->dir->getCanonicalFile()->isDirectory()) {
                throw new BuildException(
                    "'" . (string) $this->dir . "' is not a directory"
                );
            }
        } catch (IOException $e) {
            throw new BuildException(
                "'" . (string) $this->dir . "' is not a readable directory"
            );
        }
        $this->currdir = getcwd();
        @chdir($this->dir->getPath());

        $this->commandline->setEscape($this->escape);
    }

    /**
     * @param int|null $exitValue
     *
     * @return bool
     */
    public function isFailure(?int $exitValue = null): bool
    {
        if ($exitValue === null) {
            $exitValue = $this->getExitValue();
        }

        return $exitValue !== 0;
    }

    /**
     * Builds the full command to execute and stores it in $command.
     *
     * @uses   $command
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    protected function buildCommand(): void
    {
        if ($this->error !== null) {
            $this->realCommand .= ' 2> ' . escapeshellarg($this->error->getPath());
            $this->log(
                'Writing error output to: ' . $this->error->getPath(),
                $this->logLevel
            );
        }

        if ($this->output !== null) {
            $this->realCommand .= ' 1> ' . escapeshellarg($this->output->getPath());
            $this->log(
                'Writing standard output to: ' . $this->output->getPath(),
                $this->logLevel
            );
        } elseif ($this->spawn) {
            $this->realCommand .= ' 1>/dev/null';
            $this->log('Sending output to /dev/null', $this->logLevel);
        }

        // If neither output nor error are being written to file
        // then we'll redirect error to stdout so that we can dump
        // it to screen below.

        if ($this->output === null && $this->error === null && $this->passthru === false) {
            $this->realCommand .= ' 2>&1';
        }

        // we ignore the spawn boolean for windows
        if ($this->spawn) {
            $this->realCommand .= ' &';
        }

        $this->realCommand = (string) $this->commandline . $this->realCommand;
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @return array array(return code, array with output)
     *
     * @throws BuildException
     * @throws Exception
     */
    protected function executeCommand(): array
    {
        $cmdl = $this->realCommand;

        $this->log('Executing command: ' . $cmdl, $this->logLevel);

        $output = [];
        $return = null;

        if ($this->passthru) {
            passthru($cmdl, $return);
        } else {
            exec($cmdl, $output, $return);
        }

        return [$return, $output];
    }

    /**
     * Runs all tasks after command execution:
     * - change working directory back
     * - log output
     * - verify return value
     *
     * @param int|null   $return Return code
     * @param array|null $output Array with command output
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    protected function cleanup(?int $return = null, ?array $output = null): void
    {
        if ($this->dir !== null) {
            @chdir($this->currdir);
        }

        $outloglevel = $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE;
        foreach ($output as $line) {
            $this->log($line, $outloglevel);
        }

        $this->maybeSetReturnPropertyValue($return);

        if ($this->outputProperty) {
            $this->project->setProperty(
                $this->outputProperty,
                implode("\n", $output)
            );
        }

        $this->setExitValue($return);

        if ($return !== 0) {
            if ($this->checkreturn) {
                throw new BuildException($this->getTaskType() . ' returned: ' . $return, $this->getLocation());
            }
            $this->log('Result: ' . $return, Project::MSG_ERR);
        }
    }

    /**
     * Set the exit value.
     *
     * @param int $value exit value of the process.
     *
     * @return void
     */
    protected function setExitValue(int $value): void
    {
        $this->exitValue = $value;
    }

    /**
     * Query the exit value of the process.
     *
     * @return int the exit value or self::INVALID if no exit value has
     *             been received.
     */
    public function getExitValue(): int
    {
        return $this->exitValue;
    }

    /**
     * The command to use.
     *
     * @param string $command String or string-compatible (e.g. w/ __toString()).
     *
     * @return void
     *
     * @throws BuildException
     * @throws Exception
     */
    public function setCommand(string $command): void
    {
        $this->log(
            "The command attribute is deprecated.\nPlease use the executable attribute and nested arg elements.",
            Project::MSG_WARN
        );
        $this->commandline = new Commandline($command);
        $this->executable  = $this->commandline->getExecutable();
    }

    /**
     * The executable to use.
     *
     * @param string|bool $value String or string-compatible (e.g. w/ __toString()).
     *
     * @return void
     */
    public function setExecutable($value): void
    {
        if (is_bool($value)) {
            $value = $value === true ? 'true' : 'false';
        }
        $this->executable = $value;
        $this->commandline->setExecutable($value);
    }

    /**
     * Whether to use escapeshellcmd() to escape command.
     *
     * @param bool $escape If the command shall be escaped or not
     *
     * @return void
     */
    public function setEscape(bool $escape): void
    {
        $this->escape = $escape;
    }

    /**
     * Specify the working directory for executing this command.
     *
     * @param PhingFile $dir Working directory
     *
     * @return void
     */
    public function setDir(PhingFile $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * Specify OS (or multiple OS) that must match in order to execute this command.
     *
     * @param string $os Operating system string (e.g. "Linux")
     *
     * @return void
     */
    public function setOs(string $os): void
    {
        $this->os = (string) $os;
    }

    /**
     * List of operating systems on which the command may be executed.
     *
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * Restrict this execution to a single OS Family
     *
     * @param string $osFamily the family to restrict to.
     *
     * @return void
     */
    public function setOsFamily(string $osFamily): void
    {
        $this->osFamily = strtolower($osFamily);
    }

    /**
     * Restrict this execution to a single OS Family
     *
     * @return string
     */
    public function getOsFamily(): string
    {
        return $this->osFamily;
    }

    /**
     * File to which output should be written.
     *
     * @param PhingFile $f Output log file
     *
     * @return void
     */
    public function setOutput(PhingFile $f): void
    {
        $this->output = $f;
    }

    /**
     * File to which error output should be written.
     *
     * @param PhingFile $f Error log file
     *
     * @return void
     */
    public function setError(PhingFile $f): void
    {
        $this->error = $f;
    }

    /**
     * Whether to use PHP's passthru() function instead of exec()
     *
     * @param bool $passthru If passthru shall be used
     *
     * @return void
     */
    public function setPassthru(bool $passthru): void
    {
        $this->passthru = $passthru;
    }

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     *
     * @param bool $logOutput If output shall be logged visibly
     *
     * @return void
     */
    public function setLogoutput(bool $logOutput): void
    {
        $this->logOutput = $logOutput;
    }

    /**
     * Whether to suppress all output and run in the background.
     *
     * @param bool $spawn If the command is to be run in the background
     *
     * @return void
     */
    public function setSpawn(bool $spawn): void
    {
        $this->spawn = $spawn;
    }

    /**
     * Whether to check the return code.
     *
     * @param bool|null $checkreturn If the return code shall be checked
     *
     * @return void
     */
    public function setCheckreturn(?bool $checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * The name of property to set to return value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setReturnProperty(string $prop): void
    {
        $this->returnProperty = $prop;
    }

    /**
     * @param int $return
     *
     * @return void
     *
     * @throws Exception
     */
    protected function maybeSetReturnPropertyValue(int $return): void
    {
        if ($this->returnProperty) {
            $this->getProject()->setNewProperty($this->returnProperty, $return);
        }
    }

    /**
     * The name of property to set to output value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setOutputProperty(string $prop): void
    {
        $this->outputProperty = $prop;
    }

    /**
     * Add an environment variable to the launched process.
     *
     * @param EnvVariable $var new environment variable.
     *
     * @return void
     */
    public function addEnv(EnvVariable $var): void
    {
        $this->env->addVariable($var);
    }

    /**
     * Creates a nested <arg> tag.
     *
     * @return CommandlineArgument Argument object
     */
    public function createArg(): CommandlineArgument
    {
        return $this->commandline->createArgument();
    }

    /**
     * Is this the OS the user wanted?
     *
     * @return bool
     * <ul>
     * <li>
     * <li><code>true</code> if the os and osfamily attributes are null.</li>
     * <li><code>true</code> if osfamily is set, and the os family and must match
     * that of the current OS, according to the logic of
     * {@link Os#isOs(String, String, String, String)}, and the result of the
     * <code>os</code> attribute must also evaluate true.
     * </li>
     * <li>
     * <code>true</code> if os is set, and the system.property os.name
     * is found in the os attribute,</li>
     * <li><code>false</code> otherwise.</li>
     * </ul>
     *
     * @throws Exception
     */
    protected function isValidOs(): bool
    {
        //hand osfamily off to OsCondition class, if set
        if ($this->osFamily !== null && !OsCondition::isFamily($this->osFamily)) {
            return false;
        }
        //the Exec OS check is different from Os.isOs(), which
        //probes for a specific OS. Instead it searches the os field
        //for the current os.name
        $myos = Phing::getProperty('os.name');
        $this->log('Current OS is ' . $myos, Project::MSG_VERBOSE);
        if (($this->os !== null) && (strpos($this->os, $myos) === false)) {
            // this command will be executed only on the specified OS
            $this->log(
                'This OS, ' . $myos
                . ' was not found in the specified list of valid OSes: ' . $this->os,
                Project::MSG_VERBOSE
            );
            return false;
        }
        return true;
    }

    /**
     * Set whether to attempt to resolve the executable to a file.
     *
     * @param bool $resolveExecutable if true, attempt to resolve the
     * path of the executable.
     *
     * @return void
     */
    public function setResolveExecutable(bool $resolveExecutable): void
    {
        $this->resolveExecutable = $resolveExecutable;
    }

    /**
     * Set whether to search nested, then
     * system PATH environment variables for the executable.
     *
     * @param bool $searchPath if true, search PATHs.
     *
     * @return void
     */
    public function setSearchPath(bool $searchPath): void
    {
        $this->searchPath = $searchPath;
    }

    /**
     * Indicates whether to attempt to resolve the executable to a
     * file.
     *
     * @return bool the resolveExecutable flag
     */
    public function getResolveExecutable(): bool
    {
        return $this->resolveExecutable;
    }

    /**
     * The method attempts to figure out where the executable is so that we can feed
     * the full path. We first try basedir, then the exec dir, and then
     * fallback to the straight executable name (i.e. on the path).
     *
     * @param string|null $exec           The name of the executable.
     * @param bool        $mustSearchPath If true, the executable will be looked up in
     *                                    the PATH environment and the absolute path
     *                                    is returned.
     *
     * @return string the executable as a full path if it can be determined.
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    protected function resolveExecutable(?string $exec, bool $mustSearchPath): ?string
    {
        if (!$this->resolveExecutable) {
            return $exec;
        }
        // try to find the executable
        $executableFile = $this->getProject()->resolveFile($exec);
        if ($executableFile->exists()) {
            return $executableFile->getAbsolutePath();
        }
        // now try to resolve against the dir if given
        if ($this->dir !== null) {
            $executableFile = (new FileUtils())->resolveFile($this->dir, $exec);
            if ($executableFile->exists()) {
                return $executableFile->getAbsolutePath();
            }
        }
        // couldn't find it - must be on path
        if ($mustSearchPath) {
            $p           = null;
            $environment = $this->env->getVariables();
            if ($environment !== null) {
                foreach ($environment as $env) {
                    if ($this->isPath($env)) {
                        $p = new Path($this->getProject(), $this->getPath($env));
                        break;
                    }
                }
            }
            if ($p === null) {
                $p = new Path($this->getProject(), getenv('path'));
            }
            if ($p !== null) {
                $dirs = $p->listPaths();
                foreach ($dirs as $dir) {
                    $executableFile = (new FileUtils())->resolveFile(new PhingFile($dir), $exec);
                    if ($executableFile->exists()) {
                        return $executableFile->getAbsolutePath();
                    }
                }
            }
        }

        return $exec;
    }

    /**
     * @param string $line
     *
     * @return bool
     */
    private function isPath(string $line): bool
    {
        return StringHelper::startsWith('PATH=', $line) || StringHelper::startsWith('Path=', $line);
    }

    /**
     * @param string|array $value
     *
     * @return string
     */
    private function getPath($value): string
    {
        if (is_string($value)) {
            return StringHelper::substring($value, strlen('PATH='));
        }

        if (is_array($value)) {
            $p = $value['PATH'];
            return $p ?? $value['Path'];
        }

        throw new InvalidArgumentException('$value should be of type array or string.');
    }
}
