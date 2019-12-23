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
 * This is a class that represents a recorder. This is the listener to the
 * build process.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RecorderEntry implements BuildLogger, SubBuildListener
{
    /**
     * The name of the file associated with this recorder entry.
     *
     * @var string $filename
     */
    private $filename = null;

    /**
     * The state of the recorder (recorder on or off).
     *
     * @var bool $record
     */
    private $record = true;

    /**
     * The current verbosity level to record at.
     *
     * @var int
     */
    private $loglevel;

    /**
     * The output OutputStream to record to.
     *
     * @var OutputStream $out
     */
    private $out = null;

    /**
     * The start time of the last know target.
     *
     * @var float
     */
    private $targetStartTime;

    /**
     * Strip task banners if true.
     *
     * @var bool
     */
    private $emacsMode = false;

    /**
     * project instance the recorder is associated with
     *
     * @var Project $project
     */
    private $project;

    /**
     * @param string $name The name of this recorder (used as the filename).
     */
    public function __construct(string $name)
    {
        $this->targetStartTime = Phing::currentTimeMillis();
        $this->filename        = $name;
        $this->loglevel        = Project::MSG_INFO;
    }

    /**
     * @return string the name of the file the output is sent to.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Turns off or on this recorder.
     *
     * @param bool|null $state True for on, false for off, null for no change.
     *
     * @return void
     *
     * @throws IOException
     */
    public function setRecordState(?bool $state): void
    {
        if ($state != null) {
            $this->flush();
            $this->record = StringHelper::booleanValue($state);
        }
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function buildStarted(BuildEvent $event): void
    {
        $this->log('> BUILD STARTED', Project::MSG_DEBUG);
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function buildFinished(BuildEvent $event): void
    {
        $this->log('< BUILD FINISHED', Project::MSG_DEBUG);

        if ($this->record && $this->out != null) {
            $error = $event->getException();

            if ($error == null) {
                $this->out->write(Phing::getProperty('line.separator') . 'BUILD SUCCESSFUL' . PHP_EOL);
            } else {
                $this->out->write(
                    Phing::getProperty('line.separator') . 'BUILD FAILED'
                    . Phing::getProperty('line.separator') . PHP_EOL
                );
                $this->out->write($error->getTraceAsString());
            }
        }
        $this->cleanup();
    }

    /**
     * Cleans up any resources held by this recorder entry at the end
     * of a subbuild if it has been created for the subbuild's project
     * instance.
     *
     * @param BuildEvent $event the buildFinished event
     *
     * @return void
     *
     * @throws IOException
     */
    public function subBuildFinished(BuildEvent $event): void
    {
        if ($event->getProject() === $this->project) {
            $this->cleanup();
        }
    }

    /**
     * Empty implementation to satisfy the BuildListener interface.
     *
     * @param BuildEvent $event the buildStarted event
     *
     * @return void
     */
    public function subBuildStarted(BuildEvent $event): void
    {
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function targetStarted(BuildEvent $event): void
    {
        $this->log('>> TARGET STARTED -- ' . $event->getTarget()->getName(), Project::MSG_DEBUG);
        $this->log(
            Phing::getProperty('line.separator') . $event->getTarget()->getName() . ':',
            Project::MSG_INFO
        );
        $this->targetStartTime = Phing::currentTimeMillis();
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function targetFinished(BuildEvent $event): void
    {
        $this->log('<< TARGET FINISHED -- ' . $event->getTarget()->getName(), Project::MSG_DEBUG);

        $time = DefaultLogger::formatTime(Phing::currentTimeMillis() - $this->targetStartTime);

        $this->log($event->getTarget()->getName() . ':  duration ' . $time, Project::MSG_VERBOSE);
        flush();
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function taskStarted(BuildEvent $event): void
    {
        $this->log('>>> TASK STARTED -- ' . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function taskFinished(BuildEvent $event): void
    {
        $this->log('<<< TASK FINISHED -- ' . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
        $this->flush();
    }

    /**
     * @param BuildEvent $event
     *
     * @return void
     *
     * @throws IOException
     */
    public function messageLogged(BuildEvent $event): void
    {
        $this->log('--- MESSAGE LOGGED', Project::MSG_DEBUG);

        $buf = '';

        if ($event->getTask() != null) {
            $name = $event->getTask()->getTaskName();

            if (!$this->emacsMode) {
                $label = '[' . $name . '] ';
                $size  = DefaultLogger::LEFT_COLUMN_SIZE - strlen($label);

                for ($i = 0; $i < $size; $i++) {
                    $buf .= ' ';
                }
                $buf .= $label;
            }
        }
        $buf .= $event->getMessage();

        $this->log($buf, $event->getPriority());
    }

    /**
     * The thing that actually sends the information to the output.
     *
     * @param string $mesg  The message to log.
     * @param int    $level The verbosity level of the message.
     *
     * @return void
     *
     * @throws IOException
     */
    private function log(string $mesg, int $level): void
    {
        if ($this->record && ($level <= $this->loglevel) && $this->out != null) {
            $this->out->write($mesg . PHP_EOL);
        }
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    private function flush(): void
    {
        if ($this->record && $this->out != null) {
            $this->out->flush();
        }
    }

    /**
     * @param int $level
     *
     * @return void
     */
    public function setMessageOutputLevel(int $level): void
    {
        if ($level >= Project::MSG_ERR && $level <= Project::MSG_DEBUG) {
            $this->loglevel = $level;
        }
    }

    /**
     * @param OutputStream $output
     *
     * @return void
     *
     * @throws IOException
     */
    public function setOutputStream(OutputStream $output): void
    {
        $this->closeFile();
        $this->out = $output;
    }

    /**
     * @param bool $emacsMode
     *
     * @return void
     */
    public function setEmacsMode(bool $emacsMode): void
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     * @param OutputStream $err
     *
     * @return void
     *
     * @throws IOException
     */
    public function setErrorStream(OutputStream $err): void
    {
        $this->setOutputStream($err);
    }

    /**
     * Set the project associated with this recorder entry.
     *
     * @param Project $project the project instance
     *
     * @return void
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
        if ($this->project != null) {
            $this->project->addBuildListener($this);
        }
    }

    /**
     * Get the project associated with this recorder entry.
     *
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function cleanup(): void
    {
        $this->closeFile();
        if ($this->project != null) {
            $this->project->removeBuildListener($this);
        }
        $this->project = null;
    }

    /**
     * Initially opens the file associated with this recorder.
     * Used by Recorder.
     *
     * @param bool $append Indicates if output must be appended to the logfile or that
     *                     the logfile should be overwritten.
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function openFile(bool $append): void
    {
        $this->openFileImpl($append);
    }

    /**
     * Closes the file associated with this recorder.
     * Used by Recorder.
     *
     * @return void
     *
     * @throws IOException
     */
    public function closeFile(): void
    {
        if ($this->out != null) {
            $this->out->close();
            $this->out = null;
        }
    }

    /**
     * Re-opens the file associated with this recorder.
     * Used by Recorder.
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function reopenFile(): void
    {
        $this->openFileImpl(true);
    }

    /**
     * @param bool $append
     *
     * @return void
     *
     * @throws Exception
     */
    private function openFileImpl(bool $append): void
    {
        if ($this->out == null) {
            try {
                $this->out = new FileOutputStream($this->filename, $append);
            } catch (IOException $ioe) {
                throw new BuildException('Problems opening file using a recorder entry', $ioe);
            }
        }
    }
}
