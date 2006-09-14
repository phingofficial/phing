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
	
	require_once 'phing/listener/BuildLogger.php';
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
	class XmlLogger implements BuildLogger
	{
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
		
		private $doc = NULL;
		
		private $buildStartTime = 0;
		private $targetStartTime = 0;
		private $taskStartTime = 0;
		
		private $buildElement = NULL;
		
		private $msgOutputLevel = PROJECT_MSG_DEBUG;
		
		/**
		 *  Constructs a new BuildListener that logs build events to an XML file.
		 */
		function __construct()
		{
			$this->doc = new DOMDocument();
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
		function buildStarted(BuildEvent $event)
		{
			$this->buildTimerStart = Phing::currentTimeMillis();
			$this->buildElement = $this->doc->createElement(XmlLogger::BUILD_TAG);
		}
		
		/**
		 * Fired when the build finishes, this adds the time taken and any
		 * error stacktrace to the build element and writes the document to disk.
		 *
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */
		function buildFinished(BuildEvent $event)
		{
			$this->buildTimer->stop();
			
			$elapsedTime = Phing::currentTimeMillis() - $this->buildTimerStart;
			
			$this->buildElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::_formatTime($elapsedTime));
			
			if ($event->getException() != null)
			{
				$this->buildElement->setAttribute(XmlLogger::ERROR_ATTR, $event->getException()->toString());
				
				$errText = $this->doc->createCDATASection($event->getException()->getTraceAsString());
				$stacktrace = $this->doc->createElement(XmlLogger::STACKTRACE_TAG);
				$stacktrace->appendChild($errText);
				$this->buildElement->appendChild($stacktrace);
			}
			
			$outFilename = $event->getProject()->getProperty("XmlLogger.file");
			
			if ($outFilename == "")
			{
				$outFilename = "log.xml";
			}
			$writer = new FileWriter($outFilename);
			
			$writer->write("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
			$writer->write($this->doc->saveXML($this->buildElement));
			$writer->close();
		}
		/**
		 * Fired when a target starts building, remembers the current time and the name of the target.
		 *
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */
		function targetStarted(BuildEvent $event)
		{
			$target = $event->getTarget();
			
			$this->targetTimerStart = Phing::currentTimeMillis();
			
			$this->targetElement = $this->doc->createElement(XmlLogger::TARGET_TAG);
			$this->targetElement->setAttribute(XmlLogger::NAME_ATTR, $target->getName());
		}
		
		/**
		 * Fired when a target finishes building, this adds the time taken
		 * to the appropriate target element in the log.
		 *
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */
		function targetFinished(BuildEvent $event)
		{
			$target = $event->getTarget();
			
			$elapsedTime = Phing::currentTimeMillis() - $this->targetTimerStart;
			
			$this->targetElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::_formatTime($elapsedTime));
			
			$this->buildElement->appendChild($this->targetElement);
		}
		
		/**
		 * Fired when a task starts building, remembers the current time and the name of the task.
		 *
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */
		function taskStarted(BuildEvent $event)
		{
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
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */		
		function taskFinished(BuildEvent $event)
		{
			$task = $event->getTask();
			
			$elapsedTime = Phing::currentTimeMillis() - $this->taskTimerStart;
			$this->taskElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::_formatTime($elapsedTime));
			
			$this->targetElement->appendChild($this->taskElement);
		}
		
		/**
		 * Fired when a message is logged, this adds a message element to the
		 * most appropriate parent element (task, target or build) and records
		 * the priority and text of the message.
		 *
		 * @param BuildEvent An event with any relevant extra information.
		 *              Will not be <code>null</code>.
		 */
		function messageLogged(BuildEvent $event)
		{
			$priority = $event->getPriority();
			
			if ($priority > $this->msgOutputLevel)
			{
				return;
			}
			
			$messageElement = $this->doc->createElement(XmlLogger::MESSAGE_TAG);
			
			switch ($priority)
			{
				case PROJECT_MSG_ERR: 
					$name = "error"; 
					break;
					
				case PROJECT_MSG_WARN:
					$name = "warn";
					break;
				
				case PROJECT_MSG_INFO:
					$name = "info";
					break;
					
				default:
					$name = "debug";
					break;
			}
			
			$messageElement->setAttribute(XmlLogger::PRIORITY_ATTR, $name);
			
			$messageText = $this->doc->createCDATASection($event->getMessage());
			
			$messageElement->appendChild($messageText);
			
			if ($event->getTask() != null)
			{
				$this->taskElement->appendChild($messageElement);
			}
			else
			if ($event->getTarget() != null)
			{
				$this->targetElement->appendChild($messageElement);
			}
			else
			if ($this->buildElement != null)
			{			
				$this->buildElement->appendChild($messageElement);
			}
		}
		
		/**
		 * Set the logging level when using this as a Logger
		 */
		function setMessageOutputLevel($level)
		{
			$this->msgOutputLevel = $level;
		}
	};
?>