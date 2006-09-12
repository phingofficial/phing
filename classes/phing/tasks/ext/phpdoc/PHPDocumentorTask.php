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

require_once 'phing/Task.php';

/**
 * Task to run phpDocumentor.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.phpdoc
 */	
class PHPDocumentorTask extends Task
{
	/**
	 * The path to the executable for phpDocumentor
	 */
	private $programPath = 'phpdoc';

	private $title = "Default Title";

	private $destdir = ".";

	private $sourcepath = NULL;

	private $output = "";

	private $linksource = false;

	private $parseprivate = false;

	/**
	 * Sets the path to the phpDocumentor executable
	 */
	function setProgramPath($programPath)
	{
		$this->programPath = $programPath;
	}

	/**
	 * Returns the path to the phpDocumentor executable
	 */
	function getProgramPath()
	{
		return $this->programPath;
	}

	/**
	 * Set the title for the generated documentation
	 */
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Set the destination directory for the generated documentation
	 */
	function setDestdir($destdir)
	{
		$this->destdir = $destdir;
	}

	/**
	 * Set the source path
	 */
	function setSourcepath(Path $sourcepath)
	{
		if ($this->sourcepath === NULL)
		{
			$this->sourcepath = $sourcepath;
		}
		else
		{
			$this->sourcepath->append($sourcepath);
		}
	}

	/**
	 * Set the output type
	 */		
	function setOutput($output)
	{
		$this->output = $output;
	}

	/**
	 * Should sources be linked in the generated documentation
	 */
	function setLinksource($linksource)
	{
		$this->linksource = $linksource;
	}

	/**
	 * Should private members/classes be documented
	 */
	function setParseprivate($parseprivate)
	{
		$this->parseprivate = $parseprivate;
	}

	/**
	 * Main entrypoint of the task
	 */
	function main()
	{
		$arguments = $this->constructArguments();

		$this->log("Running phpDocumentor...");

		exec($this->programPath . " " . $arguments, $output, $return);

		if ($return != 0)
		{
			throw new BuildException("Could not execute phpDocumentor: " . implode(' ', $output));
		}
		
		foreach($output as $line)
		{
			if(strpos($line, 'ERROR') !== false)
			{
				$this->log($line, PROJECT_MSG_ERR);
				continue;
			}
			
			$this->log($line, PROJECT_MSG_VERBOSE);
		}
	}

	/**
	 * Constructs an argument string for phpDocumentor
	 */
	private function constructArguments()
	{
		$arguments = "-q on ";

		if ($this->title)
		{
			$arguments.= "-ti \"" . $this->title . "\" ";
		}

		if ($this->destdir)
		{
			$arguments.= "-t \"" . $this->destdir . "\" ";
		}

		if ($this->sourcepath !== NULL)
		{
			$arguments.= "-d \"" . $this->sourcepath->__toString() . "\" ";
		}

		if ($this->output)
		{
			$arguments.= "-o " . $this->output . " ";
		}

		if ($this->linksource)
		{
			$arguments.= "-s on ";
		}

		if ($this->parseprivate)
		{
			$arguments.= "-pp on ";
		}

		return $arguments;
	}
};

?>