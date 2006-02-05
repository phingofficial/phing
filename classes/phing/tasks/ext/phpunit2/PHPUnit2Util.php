<?php
/**
 * $Id: PHPUnit2Util.php,v 1.7 2004/12/02 10:52:08 mrook Exp $
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

/**
 * Various utility functions
 *
 * @author Michiel Rook <michiel@trendserver.nl>
 * @version $Id: PHPUnit2Util.php,v 1.7 2004/12/02 10:52:08 mrook Exp $
 * @package phing.tasks.ext.phpunit2
 * @since 2.1.0
 */
class PHPUnit2Util
{
	/**
	 * Returns the package of a class as defined in the docblock of the class using @package
	 *
	 * @param string the name of the  class
	 * @return string the name of the package
	 */
	static function getPackageName($classname)
	{
		$reflect = new ReflectionClass($classname);

		if (preg_match('/@package[\s]+([\.\w]+)/', $reflect->getDocComment(), $matches))
		{
			return $matches[1];
		}
		else
		{
			return "default";
		}
	}
	
	static function getClassFromFileName($filename)
	{
		$filename = basename($filename);
		
		$rpos = strrpos($filename, '.');
		
		if ($rpos != -1)
		{
			$filename = substr($filename, 0, $rpos);
		}
		
		return $filename;
	}
}
?>