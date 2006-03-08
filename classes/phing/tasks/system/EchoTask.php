<?php
/*
 *  $Id: EchoTask.php,v 1.5 2003/12/24 13:02:09 hlellelid Exp $
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
 
include_once 'phing/Task.php';

/**
 *  Echos a message to the logging system or to a file
 *
 *  @author   Michiel Rook <michiel@trendserver.nl>
 *  @author   Andreas Aderhold, andi@binarycloud.com
 *  @version  $Revision: 1.5 $ $Date: 2003/12/24 13:02:09 $
 *  @package  phing.tasks.system
 */

class EchoTask extends Task {
	
    protected $msg = "";
    
    protected $file = "";
    
    protected $append = false;
    
    protected $level = "info";

    function main() {		
		switch ($this->level)
		{
			case "error": $loglevel = PROJECT_MSG_ERR; break;
			case "warning": $loglevel = PROJECT_MSG_WARN; break;
			case "info": $loglevel = PROJECT_MSG_INFO; break;
			case "verbose": $loglevel = PROJECT_MSG_VERBOSE; break;
			case "debug": $loglevel = PROJECT_MSG_DEBUG; break;
		}
		
		if (empty($this->file))
		{
        	$this->log($this->msg, $loglevel);
		}
		else
		{
			if ($this->append)
			{
				$handle = fopen($this->file, "a");
			}
			else
			{
				$handle = fopen($this->file, "w");
			}
			
			fwrite($handle, $this->msg);
			
			fclose($handle);
		}
    }
    
    /** setter for file */
    function setFile($file)
    {
		$this->file = (string) $file;
	}

    /** setter for level */
    function setLevel($level)
    {
		$this->level = (string) $level;
	}

    /** setter for append */
    function setAppend($append)
    {
		$this->append = $append;
	}

    /** setter for message */
    function setMsg($msg) {
        $this->setMessage($msg);
    }

    /** alias setter */
    function setMessage($msg) {
        $this->msg = (string) $msg;
    }
    
    /** Supporting the <echo>Message</echo> syntax. */
    function addText($msg)
    {
        $this->msg = (string) $msg;
    }
}
