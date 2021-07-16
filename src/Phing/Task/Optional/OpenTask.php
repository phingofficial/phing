<?php

namespace Phing\Task\Optional;

use Phing\Exception\BuildException;
use Phing\Io\{FileSystem, IOException, UnixFileSystem, WindowsFileSystem};
use Phing\{Project, Task};
use Phing\Task\System\ExecTask;

/**
 * Opens a file or URL in the user's preferred application.
 *
 * @author Jawira Portugal <dev@tugal.be>
 */
class OpenTask extends Task
{
    /**
     * @var string Can be a file, dir or URL
     */
    protected $path;

    /**
     * @var UnixFileSystem|WindowsFileSystem
     */
    protected $fileSystem;

    /**
     * @var ExecTask
     */
    protected $execTask;

    /**
     * Path to be opened later
     *
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Initialize dependencies
     *
     * @throws IOException
     */
    public function init(): void
    {
        $this->fileSystem = FileSystem::getFileSystem();
        $this->execTask   = new ExecTask();
    }

    /**
     * Main method
     */
    public function main(): void
    {
        if (empty($this->path)) {
            throw new BuildException('Path is required');
        }

        $executable = $this->retrieveExecutable();
        $this->openPath($executable, $this->path);
    }

    /**
     * Retrieves right executable to call according to current OS
     */
    public function retrieveExecutable(): string
    {
        $executables = ($this->fileSystem instanceof UnixFileSystem) ? ['xdg-open', 'gnome-open', 'open'] : ['start'];
        $which       = null;

        foreach ($executables as $executable) {
            $which = $this->fileSystem->which($executable, null);
            if ($which) {
                $this->log("Executable was found: $which", Project::MSG_VERBOSE);
                break;
            }
        }

        if (empty($which)) {
            new BuildException('Cannot retrieve opening executable');
        }

        return $which;
    }

    /**
     * Run executable with path as argument
     *
     * @param string $executable
     * @param string $path
     */
    protected function openPath(string $executable, string $path): void
    {
        $this->log("Opening $path");
        $this->execTask->setExecutable($executable);
        $this->execTask->createArg()->setValue($path);
        $this->execTask->setSpawn(true);
        $this->execTask->setLocation($this->getLocation());
        $this->execTask->setProject($this->getProject());
        $this->execTask->main();
    }
}
