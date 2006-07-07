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
require_once 'phing/tasks/ext/ioncube/IoncubeComment.php';

/**
 * Invokes the ionCube Encoder (PHP4 or PHP5)
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.ioncube
 * @since 2.2.0
 */
class IoncubeEncoderTask extends Task
{
	private $phpVersion = "5";
	private $ioncubePath = "/usr/local/ioncube";
	private $encoderName = "ioncube_encoder";
	
	private $fromDir = "";
	private $toDir = "";
	
	private $encrypt = "";
	
	private $targetOption = "";
	private $binary = false;
	private $optimize = "";
	private $withoutRuntimeLoaderSupport = false;
	
	private $licensePath = "";
	private $passPhrase = "";
	
	private $comments = array();

	/**
	 * Sets the path to the ionCube encoder
	 */
	function setIoncubePath($ioncubePath)
	{
		$this->ioncubePath = $ioncubePath;
	}

	/**
	 * Returns the path to the ionCube encoder
	 */
	function getIoncubePath()
	{
		return $this->ioncubePath;
	}

	/**
	 * Sets the version of PHP to use (defaults to 5)
	 */
	function setPhpVersion($phpVersion)
	{
		$this->phpVersion = $phpVersion;
	}

	/**
	 * Returns the version of PHP to use (defaults to 5)
	 */
	function getPhpVersion()
	{
		return $this->phpVersion;
	}
	
	/**
	 * Sets the source directory
	 */
	function setFromDir($fromDir)
	{
		$this->fromDir = $fromDir;
	}

	/**
	 * Returns the source directory
	 */
	function getFromDir($fromDir)
	{
		return $this->fromDir;
	}
	
	/**
	 * Sets the target directory
	 */
	function setToDir($toDir)
	{
		$this->toDir = $toDir;
	}

	/**
	 * Returns the target directory
	 */
	function getToDir($toDir)
	{
		return $this->toDir;
	}

	/**
	 * Sets regexps of additional files to encrypt (separated by space)
	 */
	function setEncrypt($encrypt)
	{
		$this->encrypt = $encrypt;
	}
	
	/**
	 * Returns regexps of additional files to encrypt (separated by space)
	 */
	function getEncrypt()
	{
		return $this->encrypt;
	}
	
	/**
	 * Sets the binary option
	 */
	function setBinary($binary)
	{
		$this->binary = $binary;
	}
	
	/**
	 * Returns the binary option
	 */
	function getBinary()
	{
		return $this->binary;
	}

	/**
	 * Sets the optimize option
	 */
	function setOptimize($optimize)
	{
		$this->optimize = $optimize;
	}
	
	/**
	 * Returns the optimize option
	 */
	function getOptimize()
	{
		return $this->optimize;
	}

	/**
	 * Sets the without-runtime-loader-support option
	 */
	function setWithoutRuntimeLoaderSupport($withoutRuntimeLoaderSupport)
	{
		$this->withoutRuntimeLoaderSupport = $withoutRuntimeLoaderSupport;
	}
	
	/**
	 * Returns the without-runtime-loader-support option
	 */
	function getWithoutRuntimeLoaderSupport()
	{
		return $this->withoutRuntimeLoaderSupport;
	}
	
	/**
	 * Sets the option to use when encoding target directory already exists (defaults to none)
	 */
	function setTargetOption($targetOption)
	{
		$this->targetOption = $targetOption;
	}

	/**
	 * Returns he option to use when encoding target directory already exists (defaults to none)
	 */
	function getTargetOption()
	{
		return $this->targetOption;
	}
	
	/**
	 * Sets the path to the license file to use
	 */
	function setLicensePath($licensePath)
	{
		$this->licensePath = $licensePath;
	}

	/**
	 * Returns the path to the license file to use
	 */
	function getLicensePath()
	{
		return $this->licensePath;
	}

	/**
	 * Sets the passphrase to use when encoding files
	 */
	function setPassPhrase($passPhrase)
	{
		$this->passPhrase = $passPhrase;
	}

	/**
	 * Returns the passphrase to use when encoding files
	 */
	function getPassPhrase()
	{
		return $this->passPhrase;
	}

	/**
	 * Adds a comment to be used in encoded files
	 */
	function addComment(IoncubeComment $comment)
	{
		$this->comments[] = $comment;
	}

	/**
	 * The main entry point
	 *
	 * @throws BuildException
	 */
	function main()
	{
		$arguments = $this->constructArguments();
		
		$encoder = new PhingFile($this->ioncubePath, $this->encoderName . ($this->phpVersion == 5 ? '5' : ''));
		
		$this->log("Running ionCube Encoder...");
		
		exec($encoder->__toString() . " " . $arguments . " 2>&1", $output, $return);
		
        if ($return != 0)
        {
			throw new BuildException("Could not execute ionCube Encoder: " . implode(' ', $output));
        }       
	}

	/**
	 * Constructs an argument string for the ionCube encoder
	 */
	private function constructArguments()
	{
		$arguments = "";
		
		if ($this->binary)
		{
			$arguments.= "--binary ";
		}
		
		if (!empty($this->optimize))
		{
			$arguments.= "--optimize " . $this->optimize . " ";
		}
		
		if ($this->withoutRuntimeLoaderSupport)
		{
			$arguments.= "--without-runtime-loader-support ";
		}
		
		if (!empty($this->targetOption))
		{
			switch ($this->targetOption)
			{
				case "replace":
				case "merge":
				case "update":
				case "rename":
				{
					$arguments.= "--" . $this->targetOption . "-target ";
				} break;
				
				default:
				{
					throw new BuildException("Unknown target option '" . $this->targetOption . "'");
				} break;
			}
		}
		
		if (!empty($this->encrypt))
		{
			foreach (explode(" ", $this->encrypt) as $encrypt)
			{
				$arguments.= "--encrypt '$encrypt' ";
			}
		}
		
		if (!empty($this->licensePath))
		{
			$arguments.= "--with-license '" . $this->licensePath . "' ";
		}

		if (!empty($this->passPhrase))
		{
			$arguments.= "--passphrase '" . $this->passPhrase . "' ";
		}
		
		foreach ($this->comments as $comment)
		{
			$arguments.= "--add-comment '" . $comment->getValue() . "' ";
		}
		
		if ($this->fromDir != "")
		{
			$arguments .= $this->fromDir . " ";
		}

		if ($this->toDir != "")
		{
			$arguments .= "-o " . $this->toDir . " ";
		}

		return $arguments;
	}
}
?>