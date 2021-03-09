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

namespace Phing\Support;

use Phing\Listener\BuildEvent;
use Phing\Listener\BuildListener;

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

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    /**
     *  Fired before any targets are started.
     */
    public function buildStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired after the last target has finished. This event
     *  will still be thrown if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     */
    public function buildFinished(BuildEvent $event)
    {
    }

    /**
     *  Fired when a target is started.
     *
     * @see BuildEvent#getTarget()
     */
    public function targetStarted(BuildEvent $event)
    {
        //System.out.println("targetStarted " + event.getTarget().getName());
    }

    /**
     *  Fired when a target has finished. This event will
     *  still be thrown if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     */
    public function targetFinished(BuildEvent $event)
    {
        //System.out.println("targetFinished " + event.getTarget().getName());
    }

    /**
     *  Fired when a task is started.
     *
     * @see BuildEvent#getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
        //System.out.println("taskStarted " + event.getTask().getTaskName());
    }

    /**
     *  Fired when a task has finished. This event will still
     *  be throw if an error occurred during the build.
     *
     * @see BuildEvent#getException()
     */
    public function taskFinished(BuildEvent $event)
    {
        //System.out.println("taskFinished " + event.getTask().getTaskName());
    }

    /**
     *  Fired whenever a message is logged.
     *
     * @see BuildEvent#getMessage()
     * @see BuildEvent#getPriority()
     */
    public function messageLogged(BuildEvent $event)
    {
        $this->parent->logBuffer[] = [
            'message' => $event->getMessage(),
            'priority' => $event->getPriority(),
        ];
    }
}
