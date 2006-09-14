<?php
/*
 *  $Id$
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
 
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/Reader.php';

/**
 * Convenience class for reading files. The constructor of this
 *  @package   phing.system.io
 */

class FileReader extends Reader {

    protected $file;
    protected $fd;

    protected $currentPosition = 0;
    protected $mark = 0;

    function __construct($file, $exclusive = false) {
    
        if ($file instanceof PhingFile) {
            $this->file = $file;
        } elseif (is_string($file)) {
            $this->file = new PhingFile($file);
        } else {
            throw new Exception("Illegal argument type to " . __METHOD__);
        }
    }

    function skip($n) {
        $this->open();

        $start = $this->currentPosition;

        $ret = @fseek($this->fd, $n, SEEK_CUR);
        if ( $ret === -1 )
            return -1;

        $this->currentPosition = ftell($this->fd);

        if ( $start > $this->currentPosition )
            $skipped = $start - $this->currentPosition;
        else
            $skipped = $this->currentPosition - $start;

        return $skipped;
    }
    
    /**
     * Read data from file.
     * @param int $len Num chars to read.
     * @return string chars read or -1 if eof.
     */
    function read($len = null) {
        $this->open();
        if (feof($this->fd)) {
            return -1;
        }

        // Compute length to read
        // possible that filesize($this->file) will be larger than 
        // available bytes to read, but that's fine -- better to err on high end
        $length = ($len === null) ? filesize($this->file->getAbsolutePath()) : $len;

        // Read data
        $out = fread($this->fd, $length + 1); // adding 1 seems to ensure that next call to read() will return EOF (-1)
        $this->currentPosition = ftell($this->fd);

        return $out;
    }    
    
    function mark($n = null) {
        $this->mark = $this->currentPosition;
    }
    
    function reset() {
        // goes back to last mark, by default this would be 0 (i.e. rewind file).
        fseek($this->fd, SEEK_SET, $this->mark);
        $this->mark = 0;
    }

    function close() {
        if ($this->fd === null) {
            return true;
        }

        if (false === @fclose($this->fd)) {
            // FAILED.
            $msg = "Cannot fclose " . $this->file->__toString() . " $php_errormsg";
            throw new IOException($msg);
        } else {
            $this->fd = null;
            return true;
        }
    }

    function open() {
        global $php_errormsg;
        
        if ($this->fd === null) {
            $this->fd = @fopen($this->file->getAbsolutePath(), "rb");
        }

        if ($this->fd === false) {
            // fopen FAILED.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "Cannot fopen ".$this->file->getAbsolutePath().". $php_errormsg";
            throw new IOException($msg);
        }

        if (false) {
            // Locks don't seem to work on windows??? HELP!!!!!!!!!
            // if (FALSE === @flock($fp, LOCK_EX)) { // FAILED.
            $msg = "Cannot acquire flock on $file. $php_errormsg";
            throw new IOException($msg);
        }

        return true;
    }

    /**
     * Whether eof has been reached with stream.
     * @return boolean
     */
    function eof() {
        return feof($this->fd);
    }
     
    /**
     * Reads a entire file and stores the data in the variable
     * passed by reference.
     *
     * @param    string $file    String. Path and/or name of file to read.
     * @param    object &$rBuffer    Reference. Variable of where to put contents.
     *
     * @return    TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */
    function readInto(&$rBuffer) {

        $this->open();

        $fileSize = $this->file->length();
        if ($fileSize === false) {
            $msg = "Cannot get filesize of " . $this->file->__toString() . " $php_errormsg";
            throw new IOException($msg);
        }
        $rBuffer = fread($this->fd, $fileSize);
        $this->close();
    }
    
    /**
     * Returns path to file we are reading.
     * @return string
     */
    function getResource() {
        return $this->file->toString();
    }
}
?>
