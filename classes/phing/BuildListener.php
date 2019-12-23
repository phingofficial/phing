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
 * Interface for build listeners.
 *
 * Classes that implement a listener must extend this class and (faux)implement
 * all methods that are decleard as dummies below.
 *
 * @see     BuildEvent
 * @see     Project::addBuildListener()
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing
 */
interface BuildListener
{
    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function buildStarted(BuildEvent $event): void;

    /**
     * Fired after the last target has finished.
     *
     * @see   BuildEvent::getException()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function buildFinished(BuildEvent $event): void;

    /**
     * Fired when a target is started.
     *
     * @see   BuildEvent::getTarget()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function targetStarted(BuildEvent $event): void;

    /**
     * Fired when a target has finished.
     *
     * @see   BuildEvent#getException()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function targetFinished(BuildEvent $event): void;

    /**
     * Fired when a task is started.
     *
     * @see   BuildEvent::getTask()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function taskStarted(BuildEvent $event): void;

    /**
     * Fired when a task has finished.
     *
     * @see   BuildEvent::getException()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function taskFinished(BuildEvent $event): void;

    /**
     * Fired whenever a message is logged.
     *
     * @see   BuildEvent::getMessage()
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @return void
     */
    public function messageLogged(BuildEvent $event): void;
}
