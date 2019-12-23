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
 * Adds a listener to the current build process that records the
 * output to a file.
 * <p>Several recorders can exist at the same time.  Each recorder is
 * associated with a file.  The filename is used as a unique identifier for
 * the recorders.  The first call to the recorder task with an unused filename
 * will create a recorder (using the parameters provided) and add it to the
 * listeners of the build.  All subsequent calls to the recorder task using
 * this filename will modify that recorders state (recording or not) or other
 * properties (like logging level).</p>
 * <p>Some technical issues: the file's print stream is flushed for &quot;finished&quot;
 * events (buildFinished, targetFinished and taskFinished), and is closed on
 * a buildFinished event.</p>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RecorderTask extends Task implements SubBuildListener
{
    /**
     * The name of the file to record to.
     *
     * @var string
     */
    private $filename = null;

    /**
     * Whether or not to append. Need Boolean to record an unset state (null).
     *
     * @var bool
     */
    private $append = null;

    /**
     * Whether to start or stop recording. Need Boolean to record an unset
     * state (null).
     *
     * @var bool
     */
    private $start = null;

    /**
     * The level to log at. A level of -1 means not initialized yet.
     *
     * @var string
     */
    private $loglevel = -1;

    /**
     * Strip task banners if true.
     *
     * @var bool
     */
    private $emacsMode = false;

    private $logLevelChoices = [
        'error' => 0,
        'warn' => 1,
        'info' => 2,
        'verbose' => 3,
        'debug' => 4
    ];

    /**
     * The list of recorder entries.
     *
     * @var RecorderEntry[]
     */
    private static $recorderEntries = [];

    /**
     * Overridden so we can add the task as build listener.
     *
     * @return void
     */
    public function init(): void
    {
        $this->getProject()->addBuildListener($this);
    }

    /**
     * Sets the name of the file to log to, and the name of the recorder
     * entry.
     *
     * @param string $fname File name of logfile.
     *
     * @return void
     */
    public function setName(string $fname): void
    {
        $this->filename = $fname;
    }

    /**
     * Sets the action for the associated recorder entry.
     *
     * @param string $action The action for the entry to take: start or stop.
     *
     * @return void
     */
    public function setAction(string $action): void
    {
        $this->start = strtolower($action) === 'start';
    }

    /**
     * Whether or not the logger should append to a previous file.
     *
     * @param bool $append if true, append to a previous file.
     *
     * @return void
     */
    public function setAppend(bool $append): void
    {
        $this->append = $append;
    }

    /**
     * Set emacs mode.
     *
     * @param bool $emacsMode if true use emacs mode
     *
     * @return void
     */
    public function setEmacsMode(bool $emacsMode): void
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     * Sets the level to which this recorder entry should log to.
     *
     * @param string $level the level to set.
     *
     * @return void
     */
    public function setLoglevel(string $level): void
    {
        $this->loglevel = $level;
    }

    /**
     * The main execution.
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException on error
     * @throws Exception
     */
    public function main(): void
    {
        if ($this->filename == null) {
            throw new BuildException('No filename specified');
        }

        $this->getProject()->log('setting a recorder for name ' . $this->filename, Project::MSG_DEBUG);

        // get the recorder entry
        $recorder = $this->getRecorder($this->filename, $this->getProject());
        // set the values on the recorder
        if ($this->loglevel === -1) {
            $recorder->setMessageOutputLevel($this->loglevel);
        } elseif (isset($this->logLevelChoices[$this->loglevel])) {
            $recorder->setMessageOutputLevel($this->logLevelChoices[$this->loglevel]);
        } else {
            throw new BuildException('Loglevel should be one of (error|warn|info|verbose|debug).');
        }

        $recorder->setEmacsMode(StringHelper::booleanValue($this->emacsMode));
        if ($this->start != null) {
            if (StringHelper::booleanValue($this->start)) {
                $recorder->reopenFile();
                $recorder->setRecordState($this->start);
            } else {
                $recorder->setRecordState($this->start);
                $recorder->closeFile();
            }
        }
    }

    /**
     * Gets the recorder that's associated with the passed in name. If the
     * recorder doesn't exist, then a new one is created.
     *
     * @param string  $name the name of the recorder
     * @param Project $proj the current project
     *
     * @return RecorderEntry a recorder
     *
     * @throws BuildException on error
     * @throws Exception
     */
    protected function getRecorder(string $name, Project $proj): RecorderEntry
    {
        // create a recorder entry
        $entry = self::$recorderEntries[$name] ?? new RecorderEntry($name);

        if ($this->append == null) {
            $entry->openFile(false);
        } else {
            $entry->openFile(StringHelper::booleanValue($this->append));
        }
        $entry->setProject($proj);
        self::$recorderEntries[$name] = $entry;

        return $entry;
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function buildStarted(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function subBuildStarted(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function targetStarted(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function targetFinished(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function taskStarted(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function taskFinished(BuildEvent $event): void
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function messageLogged(BuildEvent $event): void
    {
    }

    /**
     * Cleans recorder registry.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function buildFinished(BuildEvent $event): void
    {
        $this->cleanup();
    }

    /**
     * Cleans recorder registry, if this is the subbuild the task has
     * been created in.
     *
     * @param BuildEvent $event ignored.
     *
     * @return void
     */
    public function subBuildFinished(BuildEvent $event): void
    {
        if ($event->getProject() === $this->getProject()) {
            $this->cleanup();
        }
    }

    /**
     * cleans recorder registry and removes itself from BuildListener list.
     *
     * @return void
     */
    private function cleanup(): void
    {
        $entries = self::$recorderEntries;
        foreach ($entries as $key => $entry) {
            if ($entry->getProject() === $this->getProject()) {
                unset(self::$recorderEntries[$key]);
            }
        }
        $this->getProject()->removeBuildListener($this);
    }
}
