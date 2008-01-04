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

namespace phing::tasks::ext::svn;
use phing::BuildException;

/**
 * Stores the number of the last revision of a workingcopy in a property
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @author Arno Schneider <arnoschn@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.1.0
 */
class SvnLastRevisionTask extends SvnBaseTask
{
	private $propertyName = "svn.lastrevision";

	/**
	 * Sets the name of the property to use
	 */
	function setPropertyName($propertyName)
	{
		$this->propertyName = $propertyName;
	}

	/**
	 * Returns the name of the property to use
	 */
	function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * The main entry point
	 *
	 * @throws BuildException
	 */
	function main()
	{
		$this->setup('info');
		
		/**
		 * run in xml mode, allows us to retrieve workingcopy info in 
		 * a unified xml format, so we dont have to fight with internationalized
		 * versions of svn info output
		 */
		$output = $this->run(array('--xml'));
		try 
		{
			$xml = new SimpleXMLElement($output);
			/**
			 * walk the xml towards the last commit element
			 */
			$commits = $xml->xpath('/info/entry/commit');
			if (count($commits)>0) {
				$commit = $commits[0];
				/**
				 * get the attributes of the commit element
				 */
				$attributes = $commit->attributes();
				$this->project->setProperty($this->getPropertyName(), $attributes->revision);
			} else {
				throw new BuildException("Failed to parse the output of 'svn info'.");
			}
			
		}
		catch (Exception $e)
		{
			throw new BuildException("Failed to parse the output of 'svn info'.");
		}
	}
}
?>