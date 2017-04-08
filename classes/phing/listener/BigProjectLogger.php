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

/**
 * This is a special logger that is designed to make it easier to work
 * with big projects.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.listener
 */
class BigProjectLogger extends SimpleBigProjectLogger implements SubBuildListener
{
    /** @var bool $subBuildStartedRaised */
    private $subBuildStartedRaised = false;

    /**
     * Header string for the log.
     * {@value}
     */
    const HEADER = '======================================================================';
    /**
     * Footer string for the log.
     * {@value}
     */
    const FOOTER = self::HEADER;

    /**
     * This is an override point: the message that indicates whether
     * a build failed. Subclasses can change/enhance the
     * message.
     *
     * @return string The classic "BUILD FAILED" plus a timestamp
     */
    protected function getBuildFailedMessage()
    {
        return parent::getBuildFailedMessage() . TimestampedLogger::$SPACER . $this->getTimestamp();
    }

    /**
     * This is an override point: the message that indicates that
     * a build succeeded. Subclasses can change/enhance the
     * message.
     *
     * @return string The classic "BUILD SUCCESSFUL" plus a timestamp
     */
    protected function getBuildSuccessfulMessage()
    {
        return parent::getBuildSuccessfulMessage() . TimestampedLogger::$SPACER . $this->getTimestamp();
    }

    /**
     * @param BuildEvent $event
     */
    public function targetStarted(BuildEvent $event)
    {
        $this->maybeRaiseSubBuildStarted($event);
        parent::targetStarted($event);
    }

    /**
     *  Fired when a task is started. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param BuildEvent $event
     * @see    BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
        $this->maybeRaiseSubBuildStarted($event);
        parent::taskStarted($event);
    }

    /**
     *  Prints whether the build succeeded or failed, and any errors that
     *  occurred during the build. Also outputs the total build-time.
     *
     * @param BuildEvent $event
     * @see    BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event)
    {
        $this->maybeRaiseSubBuildStarted($event);
        $this->subBuildFinished($event);
        parent::buildFinished($event);
    }

    /**
     * @param BuildEvent $event
     */
    public function messageLogged(BuildEvent $event)
    {
        $this->maybeRaiseSubBuildStarted($event);
        parent::messageLogged($event);
    }

    /**
     * Signals that a subbuild has started. This event
     * is fired before any targets have started.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Must not be <code>null</code>.
     */
    public function subBuildStarted(BuildEvent $event)
    {
        $name = $this->extractNameOrDefault($event);
        $project = $event->getProject();

        $base = $project === null ? null : $project->getBasedir();
        $path = $base === null
            ? 'With no base directory'
            : 'In ' . $base->getAbsolutePath();
        $this->printMessage(PHP_EOL . $this->getHeader()
            . PHP_EOL . 'Entering project ' . $name
            . PHP_EOL . $path
            . PHP_EOL . $this->getFooter(),
            $this->out,
            $event->getPriority());
    }

    /**
     * Get the name of an event
     *
     * @param BuildEvent $event the event name
     * @return string the name or a default string
     */
    protected function extractNameOrDefault(BuildEvent $event)
    {
        $name = $this->extractProjectName($event);
        if ($name == null) {
            $name = '';
        } else {
            $name = '"' . $name . '"';
        }
        return $name;
    }

    /**
     * Signals that the last target has finished. This event
     * will still be fired if an error occurred during the build.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Must not be <code>null</code>.
     *
     * @see BuildEvent::getException()
     */
    public function subBuildFinished(BuildEvent $event)
    {
        $name = $this->extractNameOrDefault($event);
        $failed = $event->getException() !== null ? 'failing ' : '';
        $this->printMessage(PHP_EOL . $this->getHeader()
            . PHP_EOL . 'Exiting ' . $failed . 'project '
            . $name
            . PHP_EOL . $this->getFooter(),
            $this->out,
            $event->getPriority());
    }

    /**
     * Override point: return the header string for the entry/exit message
     * @return string the header string
     */
    protected function getHeader()
    {
        return self::HEADER;
    }

    /**
     * Override point: return the footer string for the entry/exit message
     * @return string the footer string
     */
    protected function getFooter()
    {
        return self::FOOTER;
    }

    private function maybeRaiseSubBuildStarted(BuildEvent $event)
    {
        // double checked locking should be OK since the flag is write-once
        if (!$this->subBuildStartedRaised) {
            if (!$this->subBuildStartedRaised) {
                $this->subBuildStartedRaised = true;
                $this->subBuildStarted($event);
            }
        }
    }
}
