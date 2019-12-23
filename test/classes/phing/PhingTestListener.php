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
 * our own personal build listener
 *
 * @author Nico Seessle <nico@seessle.de>
 * @author Conor MacNeill
 * @author Victor Farazdagi <simple.square@gmail.com>
 */
class PhingTestListener implements BuildListener
{
    private $parent;

    /**
     * @param mixed $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function buildStarted(BuildEvent $event): void
    {
    }

    /**
     *  Fired after the last target has finished. This event
     *  will still be thrown if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function buildFinished(BuildEvent $event): void
    {
    }

    /**
     *  Fired when a target is started.
     *
     * @see BuildEvent#getTarget()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function targetStarted(BuildEvent $event): void
    {
        //System.out.println("targetStarted " + event.getTarget().getName());
    }

    /**
     *  Fired when a target has finished. This event will
     *  still be thrown if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function targetFinished(BuildEvent $event): void
    {
        //System.out.println("targetFinished " + event.getTarget().getName());
    }

    /**
     *  Fired when a task is started.
     *
     * @see BuildEvent#getTask()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function taskStarted(BuildEvent $event): void
    {
        //System.out.println("taskStarted " + event.getTask().getTaskName());
    }

    /**
     *  Fired when a task has finished. This event will still
     *  be throw if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function taskFinished(BuildEvent $event): void
    {
        //System.out.println("taskFinished " + event.getTask().getTaskName());
    }

    /**
     *  Fired whenever a message is logged.
     *
     * @see BuildEvent#getMessage()
     * @see BuildEvent#getPriority()
     *
     * @param BuildEvent $event
     *
     * @return void
     */
    public function messageLogged(BuildEvent $event): void
    {
        $this->parent->logBuffer[] = [
            'message' => $event->getMessage(),
            'priority' => $event->getPriority(),
        ];
    }
}
