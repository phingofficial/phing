<?php
/**
 * $Id$
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

require_once 'phing/BuildLogger.php';
require_once 'phing/listener/DefaultLogger.php';
require_once 'phing/system/util/Timer.php';

/**
 * Generates a file in the current directory with
 * an XML description of what happened during a build.
 * The default filename is "log.xml", but this can be overridden
 * with the property <code>XmlLogger.file</code>.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.listener
 */	
class XmlLogger implements BuildLogger {
	
	/** XML element name for a build. */
	const BUILD_TAG = "build";
	/** XML element name for a target. */
	const TARGET_TAG = "target";
	/** XML element name for a task. */
	const TASK_TAG = "task";
	/** XML element name for a message. */
	const MESSAGE_TAG = "message";
	/** XML attribute name for a name. */
	const NAME_ATTR = "name";
	/** XML attribute name for a time. */
	const TIME_ATTR = "time";
	/** XML attribute name for a message priority. */
	const PRIORITY_ATTR = "priority";
	/** XML attribute name for a file location. */
	const LOCATION_ATTR = "location";
	/** XML attribute name for an error description. */
	const ERROR_ATTR = "error";
	/** XML element name for a stack trace. */
	const STACKTRACE_TAG = "stacktrace";
	
	/**
	 * @var DOMDocument The XML document created by this logger. 
	 */
	private $doc;
	
	private $buildStartTime = 0;
	private $targetStartTime = 0;
	private $taskStartTime = 0;
	
	private $buildElement;
	
	/**
	 * @var int
	 */
	private $msgOutputLevel = Project::MSG_DEBUG;
	
	/**
     * @var OutputStream Stream to use for standard output.
     */
	private $out;
   
	/**
	 * @var OutputStream Stream to use for error output.
	 */
	private $err;
	
	/**
	 * @var string Name of filename to create.
	 */
	private $outFilename;
	
	/**
	 *  Constructs a new BuildListener that logs build events to an XML file.
	 */
	public function __construct() {
		$this->doc = new DOMDocument("1.0", "UTF-8");
		$this->doc->formatOutput = true;
		
		$this->buildTimer = new Timer();
		$this->targetTimer = new Timer();
		$this->taskTimer = new Timer();
	}
	
	/**
	 * Fired when the build starts, this builds the top-level element for the
	 * document and remembers the time of the start of the build.
	 *
	 * @param BuildEvent Ignored.
	 */
	function buildStarted(BuildEvent $event) {
		$this->buildTimerStart = Phing::currentTimeMillis();
		$this->buildElement = $this->doc->createElement(XmlLogger::BUILD_TAG);
	}
	
	/**
	 * Fired when the build finishes, this adds the time taken and any
	 * error stacktrace to the build element and writes the document to disk.
	 *
	 * @param BuildEvent $event An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */
	public function buildFinished(BuildEvent $event) {
		
		$this->buildTimer->stop();
		
		$elapsedTime = Phing::currentTimeMillis() - $this->buildTimerStart;
		
		$this->buildElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));
		
		if ($event->getException() != null)
		{
			$this->buildElement->setAttribute(XmlLogger::ERROR_ATTR, $event->getException()->toString());
			
			$errText = $this->doc->createCDATASection($event->getException()->getTraceAsString());
			$stacktrace = $this->doc->createElement(XmlLogger::STACKTRACE_TAG);
			$stacktrace->appendChild($errText);
			$this->buildElement->appendChild($stacktrace);
		}
		
		$this->doc->appendChild($this->buildElement);
		
		$outFilename = $event->getProject()->getProperty("XmlLogger.file");
        if ($outFilename == null) {
            $outFilename = "log.xml";
        }
        
        try {
	        $stream = $this->out;
	        if ($stream === null) {
	        	$stream = new FileOutputStream($outFilename); 
	        }
	        
	        // Yes, we could just stream->write() but this will eventually be the better
			// way to do this (when we need to worry about charset conversions. 
	        $writer = new OutputStreamWriter($stream);
	        $writer->write($this->doc->saveXML());
	        $writer->close();
        } catch (IOException $exc) {
        	try {
        		$stream->close(); // in case there is a stream open still ...
        	} catch (Exception $x) {}
        	throw new BuildException("Unable to write log file.", $exc);
        }
	}
        
	
	/**
	 * Fired when a target starts building, remembers the current time and the name of the target.
	 *
	 * @param BuildEvent $event An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */
	public function targetStarted(BuildEvent $event) {
		$target = $event->getTarget();
		
		$this->targetTimerStart = Phing::currentTimeMillis();
		
		$this->targetElement = $this->doc->createElement(XmlLogger::TARGET_TAG);
		$this->targetElement->setAttribute(XmlLogger::NAME_ATTR, $target->getName());
	}
	
	/**
	 * Fired when a target finishes building, this adds the time taken
	 * to the appropriate target element in the log.
	 *
	 * @param BuildEvent $event An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */
	public function targetFinished(BuildEvent $event) {
		$target = $event->getTarget();
		$elapsedTime = Phing::currentTimeMillis() - $this->targetTimerStart;
		$this->targetElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));
		$this->buildElement->appendChild($this->targetElement);
		$this->targetElement = null;
	}
	
	/**
	 * Fired when a task starts building, remembers the current time and the name of the task.
	 *
	 * @param BuildEvent $event An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */
	public function taskStarted(BuildEvent $event) {
		$task = $event->getTask();
		$this->taskTimerStart = Phing::currentTimeMillis();
		$this->taskElement = $this->doc->createElement(XmlLogger::TASK_TAG);
		$this->taskElement->setAttribute(XmlLogger::NAME_ATTR, $task->getTaskName());
		$this->taskElement->setAttribute(XmlLogger::LOCATION_ATTR, $task->getLocation()->toString());
	}
	
	/**
	 * Fired when a task finishes building, this adds the time taken
	 * to the appropriate task element in the log.
	 *
	 * @param BuildEvent $event An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */		
	public function taskFinished(BuildEvent $event) {
		$task = $event->getTask();
		$elapsedTime = Phing::currentTimeMillis() - $this->taskTimerStart;
		$this->taskElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));
		if ($this->targetElement) { // not all tasks are in targets
			$this->targetElement->appendChild($this->taskElement);
		} else {
			$this->buildElement->appendChild($this->taskElement);
		}
		$this->taskElement = null;
	}
	
	/**
	 * Fired when a message is logged, this adds a message element to the
	 * most appropriate parent element (task, target or build) and records
	 * the priority and text of the message.
	 *
	 * @param BuildEvent An event with any relevant extra information.
	 *              Will not be <code>null</code>.
	 */
	public function messageLogged(BuildEvent $event)
	{
		$priority = $event->getPriority();
		
		if ($priority > $this->msgOutputLevel) {
			return;
		}
		
		$messageElement = $this->doc->createElement(XmlLogger::MESSAGE_TAG);
		
		switch ($priority) {
			case Project::MSG_ERR: 
				$name = "error"; 
				break;
			case Project::MSG_WARN:
				$name = "warn";
				break;
			case Project::MSG_INFO:
				$name = "info";
				break;
			default:
				$name = "debug";
				break;
		}
		
		$messageElement->setAttribute(XmlLogger::PRIORITY_ATTR, $name);
		
		$messageText = $this->doc->createCDATASection($event->getMessage());
		
		$messageElement->appendChild($messageText);
		
		if ($event->getTask() !== null) {
			$this->taskElement->appendChild($messageElement);
		} elseif ($event->getTarget() !== null) {
			$this->targetElement->appendChild($messageElement);
		} elseif ($this->buildElement !== null) {			
			$this->buildElement->appendChild($messageElement);
		}
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
     *    <li>Project::MSG_ERR</li>
     *    <li>Project::MSG_WARN</li>
     *    <li>Project::MSG_INFO</li>
     *    <li>Project::MSG_VERBOSE</li>
     *    <li>Project::MSG_DEBUG</li>
     *  </ul>
     *
     *  The default message level for DefaultLogger is Project::MSG_ERR.
     *
     * @param int $level The logging level for the logger.
     * @see BuildLogger#setMessageOutputLevel()
     */
    public function setMessageOutputLevel($level) {
        $this->msgOutputLevel = (int) $level;
    }
    
    /**
     * Sets the output stream.
     * @param OutputStream $output
     * @see BuildLogger#setOutputStream()
     */
    public function setOutputStream(OutputStream $output) {
    	$this->out = $output;
    }
	
    /**
     * Sets the error stream.
     * @param OutputStream $err
     * @see BuildLogger#setErrorStream()
     */
    public function setErrorStream(OutputStream $err) {
    	$this->err = $err;
    }
	
}
