<?php
/*
 *  $Id: DefaultLogger.php,v 1.11 2005/08/25 19:33:43 hlellelid Exp $
 *
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
 
require_once 'phing/BuildListener.php';
include_once 'phing/BuildEvent.php';

/**
 *  Writes a build event to the console.
 *
 *  Currently, it only writes which targets are being executed, and
 *  any messages that get logged.
 *
 *  @author    Andreas Aderhold <andi@binarycloud.com>
 *  @copyright © 2001,2002 THYRELL. All rights reserved
 *  @version   $Revision: 1.11 $ $Date: 2005/08/25 19:33:43 $
 *  @see       BuildEvent
 *  @package   phing.listener
 */
class DefaultLogger implements BuildListener {

    /**
     *  Size of the left column in output. The default char width is 12.
     *  @var int
     */
    const LEFT_COLUMN_SIZE = 12;

    /**
     *  The message output level that should be used. The default is
     *  <code>PROJECT_MSG_VERBOSE</code>.
     *  @var int
     */
    protected $msgOutputLevel = PROJECT_MSG_ERR;

    /**
     *  Time that the build started
     *  @var int
     */
    protected $startTime;

    /**
     *  Char that should be used to seperate lines. Default is the system
     *  property <em>line.seperator</em>.
     *  @var string
     */
    protected $lSep;

    /**
     *  Construct a new default logger.
     */
    public function __construct() {
        $this->lSep = Phing::getProperty("line.separator");
    }

    /**
     *  Set the msgOutputLevel this logger is to respond to.
     *
     *  Only messages with a message level lower than or equal to the given
     *  level are output to the log.
     *
     *  <p> Constants for the message levels are in Project.php. The order of
     *  the levels, from least to most verbose, is:
     *
     *  <ul>
     *    <li>PROJECT_MSG_ERR</li>
     *    <li>PROJECT_MSG_WARN</li>
     *    <li>PROJECT_MSG_INFO</li>
     *    <li>PROJECT_MSG_VERBOSE</li>
     *    <li>PROJECT_MSG_DEBUG</li>
     *  </ul>
     *
     *  The default message level for DefaultLogger is PROJECT_MSG_ERR.
     *
     *  @param  integer  the logging level for the logger.
     *  @access public
     */
    function setMessageOutputLevel($level) {
        $this->msgOutputLevel = (int) $level;
    }

    /**
    *  Sets the start-time when the build started. Used for calculating
    *  the build-time.
    *
    *  @param  object  The BuildEvent
    *  @access public
    */

    function buildStarted(BuildEvent $event) {
        $this->startTime = Phing::currentTimeMillis();
        if ($this->msgOutputLevel >= PROJECT_MSG_INFO) {
            $this->printMessage("Buildfile: ".$event->getProject()->getProperty("phing.file"), PROJECT_MSG_INFO);
        }
    }

    /**
     *  Prints whether the build succeeded or failed, and any errors that
     *  occured during the build. Also outputs the total build-time.
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getException()
     */
    function buildFinished(BuildEvent $event) {
        $error = $event->getException();
        if ($error === null) {
            print($this->lSep . "BUILD FINISHED" . $this->lSep);
        } else {
            print($this->lSep . "BUILD FAILED" . $this->lSep);
            if (PROJECT_MSG_VERBOSE <= $this->msgOutputLevel || !($error instanceof BuildException)) {
                print($error->__toString().$this->lSep);
            } else {
                print($error->getMessage());
            }
        }
        print($this->lSep . "Total time: " .$this->_formatTime(Phing::currentTimeMillis() - $this->startTime) . $this->lSep);
    }

    /**
     *  Prints the current target name
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getTarget()
     */
    function targetStarted(BuildEvent $event) {
        if (PROJECT_MSG_INFO <= $this->msgOutputLevel) {
            print($this->lSep . $event->getProject()->getName() . ' > ' . $event->getTarget()->getName() . ':' . $this->lSep);
        }
    }

    /**
     *  Fired when a target has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getException()
     */
    function targetFinished(BuildEvent $event) {}

    /**
     *  Fired when a task is started. We don't need specific action on this
     *  event. So the methods are empty.
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getTask()
     */
    function taskStarted(BuildEvent $event) {}

    /**
     *  Fired when a task has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getException()
     */
    function taskFinished(BuildEvent $event) {}

    /**
     *  Print a message to the stdout.
     *
     *  @param  object  The BuildEvent
     *  @access public
     *  @see    BuildEvent::getMessage()
     */
    function messageLogged(BuildEvent $event) {
        if ($event->getPriority() <= $this->msgOutputLevel) {
            $msg = "";
            if ($event->getTask() !== null) {
                $name = $event->getTask();
                $name = $name->getTaskName();
                $msg = str_pad("[$name] ", self::LEFT_COLUMN_SIZE, " ", STR_PAD_LEFT);
                #for ($i=0; $i < ($this->LEFT_COLUMN_SIZE - strlen($msg)); ++$i) {
                #    print(" ");
                #}
                #print($msg);
            }
            $msg .= $event->getMessage();
            $this->printMessage($msg, $event->getPriority());
        }
    }

    /**
     *  Formats a time micro integer to human readable format.
     *
     *  @param  integer The time stamp
     *  @access private
     */
    function _formatTime($micros) {
        $seconds = $micros;
        $minutes = $seconds / 60;
        if ($minutes > 1) {
            return sprintf("%1.0f minute%s %0.2f second%s",
                                    $minutes, ($minutes === 1 ? " " : "s "),
                                    $seconds - floor($seconds/60) * 60, ($seconds%60 === 1 ? "" : "s"));
        } else {
            return sprintf("%0.4f second%s", $seconds, ($seconds%60 === 1 ? "" : "s"));
        }
    }
    
    /**
     * Prints a message to console.
     * 
     * @param string $message  The message to print. 
     *                 Should not be <code>null</code>.
     * @param int $priority The priority of the message. 
     *                 (Ignored in this implementation.)
     * @return void
     */
    protected function printMessage($message, $priority) {
        print($message . $this->lSep);
    }    
}
