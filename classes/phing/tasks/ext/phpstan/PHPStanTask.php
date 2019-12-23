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

class PHPStanTask extends Task
{
    use FileSetAware;

    /**
     * @var string
     */
    private $executable = 'phpstan';

    /**
     * @var string
     */
    private $command = 'analyse';

    /**
     * @var bool
     */
    private $help;

    /**
     * @var bool
     */
    private $quiet;

    /**
     * @var bool
     */
    private $version;

    /**
     * @var bool
     */
    private $ansi;

    /**
     * @var bool
     */
    private $noAnsi;

    /**
     * @var bool
     */
    private $noInteraction;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $level;

    /**
     * @var bool
     */
    private $noProgress;

    /**
     * @var bool
     */
    private $checkreturn;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $autoloadFile;

    /**
     * @var string
     */
    private $errorFormat;

    /**
     * @var string
     */
    private $memoryLimit;

    /**
     * @var string
     */
    private $format;

    /**
     * @var bool
     */
    private $raw;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string Analyse command paths
     */
    private $paths;

    /**
     * @var string Help command command name
     */
    private $commandName;

    /**
     * @var Commandline
     */
    private $cmd;

    public function __construct()
    {
        $this->cmd = new Commandline();
        parent::__construct();
    }

    /**
     * @return Commandline
     */
    public function getCommandline(): Commandline
    {
        return $this->cmd;
    }

    /**
     * @return string
     */
    public function getExecutable(): string
    {
        return $this->executable;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return bool|null
     */
    public function isHelp(): ?bool
    {
        return $this->help;
    }

    /**
     * @return bool|null
     */
    public function isQuiet(): ?bool
    {
        return $this->quiet;
    }

    /**
     * @return bool|null
     */
    public function isVersion(): ?bool
    {
        return $this->version;
    }

    /**
     * @return bool|null
     */
    public function isAnsi(): ?bool
    {
        return $this->ansi;
    }

    /**
     * @return bool|null
     */
    public function isNoAnsi(): ?bool
    {
        return $this->noAnsi;
    }

    /**
     * @return bool|null
     */
    public function isNoInteraction(): ?bool
    {
        return $this->noInteraction;
    }

    /**
     * @return bool|null
     */
    public function isVerbose(): ?bool
    {
        return $this->verbose;
    }

    /**
     * @return string|null
     */
    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    /**
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return $this->level;
    }

    /**
     * @return bool|null
     */
    public function isNoProgress(): ?bool
    {
        return $this->noProgress;
    }

    /**
     * @return bool|null
     */
    public function isCheckreturn(): ?bool
    {
        return $this->checkreturn;
    }

    /**
     * @return bool|null
     */
    public function isDebug(): ?bool
    {
        return $this->debug;
    }

    /**
     * @return string|null
     */
    public function getAutoloadFile(): ?string
    {
        return $this->autoloadFile;
    }

    /**
     * @return string|null
     */
    public function getErrorFormat(): ?string
    {
        return $this->errorFormat;
    }

    /**
     * @return string|null
     */
    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return bool|null
     */
    public function isRaw(): ?bool
    {
        return $this->raw;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getPaths(): ?string
    {
        return $this->paths;
    }

    /**
     * @return string|null
     */
    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    /**
     * @param string $executable
     *
     * @return void
     */
    public function setExecutable(string $executable): void
    {
        $this->executable = $executable;
    }

    /**
     * @param string $command
     *
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * @param bool $help
     *
     * @return void
     */
    public function setHelp(bool $help): void
    {
        $this->help = $help;
    }

    /**
     * @param bool $quiet
     *
     * @return void
     */
    public function setQuiet(bool $quiet): void
    {
        $this->quiet = $quiet;
    }

    /**
     * @param bool $version
     *
     * @return void
     */
    public function setVersion(bool $version): void
    {
        $this->version = $version;
    }

    /**
     * @param bool $ansi
     *
     * @return void
     */
    public function setAnsi(bool $ansi): void
    {
        $this->ansi = $ansi;
    }

    /**
     * @param bool $noAnsi
     *
     * @return void
     */
    public function setNoAnsi(bool $noAnsi): void
    {
        $this->noAnsi = $noAnsi;
    }

    /**
     * @param bool $noInteraction
     *
     * @return void
     */
    public function setNoInteraction(bool $noInteraction): void
    {
        $this->noInteraction = $noInteraction;
    }

    /**
     * @param bool $verbose
     *
     * @return void
     */
    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    /**
     * @param string $configuration
     *
     * @return void
     */
    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $level
     *
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * @param bool $noProgress
     *
     * @return void
     */
    public function setNoProgress(bool $noProgress): void
    {
        $this->noProgress = $noProgress;
    }

    /**
     * @param bool $checkreturn
     *
     * @return void
     */
    public function setCheckreturn(bool $checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @param string $autoloadFile
     *
     * @return void
     */
    public function setAutoloadFile(string $autoloadFile): void
    {
        $this->autoloadFile = $autoloadFile;
    }

    /**
     * @param string $errorFormat
     *
     * @return void
     */
    public function setErrorFormat(string $errorFormat): void
    {
        $this->errorFormat = $errorFormat;
    }

    /**
     * @param string $memoryLimit
     *
     * @return void
     */
    public function setMemoryLimit(string $memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @param string $format
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @param bool $raw
     *
     * @return void
     */
    public function setRaw(bool $raw): void
    {
        $this->raw = $raw;
    }

    /**
     * @param string $namespace
     *
     * @return void
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @param string $paths
     *
     * @return void
     */
    public function setPaths(string $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @param string $commandName
     *
     * @return void
     */
    public function setCommandName(string $commandName): void
    {
        $this->commandName = $commandName;
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function main(): void
    {
        $commandBuilder = (new PHPStanCommandBuilderFactory())->createBuilder($this);
        $commandBuilder->build($this);

        $toExecute = $this->cmd;

        $exe = new ExecTask();
        $exe->setExecutable($toExecute->getExecutable());
        $exe->createArg()->setLine(implode(' ', $toExecute->getArguments()));
        $exe->setCheckreturn($this->checkreturn);
        $exe->setLocation($this->getLocation());
        $exe->setProject($this->getProject());
        $exe->setLevel('info');
        $exe->main();
    }
}
