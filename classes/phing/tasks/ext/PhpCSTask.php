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

use Phing\Exception\BuildException;

/**
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext
 */
class PhpCSTask extends Task
{
    use LogLevelAware;
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    private $file;

    /**
     * The
     *
     * @var array
     */
    protected $files = [];


    /** @var Commandline */
    private $cmd;

    /** @var bool */
    private $cache = false;

    /** @var bool */
    private $ignoreAnnotations = false;

    /** @var bool */
    private $checkreturn = false;

    /** @var string */
    private $bin = 'phpcs';

    public function __construct()
    {
        $this->cmd = new Commandline();
        $this->logLevelName = 'info';
        parent::__construct();
    }

    public function getCommandline(): Commandline
    {
        return $this->cmd;
    }

    /**
     * @param bool $cache
     */
    public function setCache(bool $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param bool $ignore
     */
    public function setIgnoreAnnotations(bool $ignore): void
    {
        $this->ignoreAnnotations = $ignore;
    }

    /**
     * @param bool $checkreturn
     */
    public function setCheckreturn(bool $checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * @param string $bin
     */
    public function setBin(string $bin): void
    {
        $this->bin = $bin;
    }

    /**
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
    }

    public function main()
    {
        if ($this->file === null && count($this->filesets) == 0) {
            throw new BuildException('Missing both attribute "file" and "fileset".');
        }
        if ($this->file === null) {
            // check filesets, and compile a list of files for phpcs to analyse
            foreach ($this->filesets as $fileset) {
                $files = $fileset->getIterator();
                foreach ($files as $file) {
                    $this->files[] = $file;
                }
            }
        }

        $toExecute = $this->getCommandline();

        $this->cache
            ? $toExecute->createArgument()->setValue('--cache')
            : $toExecute->createArgument()->setValue('--no-cache');

        if ($this->ignoreAnnotations) {
            $toExecute->createArgument()->setValue('--ignore-annotations');
        }

        if ($this->file !== null) {
            $toExecute->createArgument()->setFile($this->file);
        } else {
            foreach ($this->files as $file) {
                $toExecute->createArgument()->setFile(new PhingFile($file));
            }
        }

        $exe = new ExecTask();
        $exe->setProject($this->getProject());
        $exe->setLocation($this->getLocation());
        $exe->setOwningTarget($this->target);
        $exe->setTaskName($this->getTaskName());
        $exe->setExecutable($this->bin);
        $exe->setCheckreturn($this->checkreturn);
        $exe->setLevel($this->logLevelName);
        $exe->setExecutable($toExecute->getExecutable());
        $exe->createArg()->setLine(implode(' ', $toExecute->getArguments()));
        $exe->main();
    }
}
