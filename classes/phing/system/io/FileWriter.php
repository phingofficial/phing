<?php
/*
 *  $Id: FileWriter.php,v 1.7 2005/05/26 13:10:52 mrook Exp $  
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
include_once 'phing/system/io/Writer.php';

/**
 * Convenience class for reading files. The constructor of this
 *
 * @package   phing.system.io
 */
class FileWriter extends Writer {

    protected $file;
    protected $fd;
    
    /** Whether to append contents to file. */
    protected $append;
    
    /** Whether we should attempt to lock the file (currently disabled). */
    protected $exclusive;
    
    /**
     * Construct a new FileWriter.
     * @param mixed $file PhingFile or string pathname.
     * @param boolean $append Append to existing file?
     * @param boolean $exclusive Lock file? (currently disabled due to windows incompatibility)
     */
    function __construct($file, $append = false, $exclusive = false) {
        if ($file instanceof PhingFile) {
            $this->file = $file;
        } elseif (is_string($file)) {
            $this->file = new PhingFile($file);
        } else {
            throw new Exception("Invalid argument type for \$file.");
        }
        $this->append = $append;
        $this->exclusive = $exclusive;
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
        if ($this->fd === null) {
            if ($this->append) { $flags = "ab"; } else { $flags = "wb"; }
            $this->fd = @fopen($this->file->getPath(), $flags);
        }

        if ($this->fd === false) {
            // fopen FAILED.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "Cannot fopen ".$this->file->getPath()." $php_errormsg";
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
    
    function reset() {
        // FIXME -- what exactly should this do, if anything?
        // reset to beginning of file (i.e. re-open)?
    }
    
    function writeBuffer($buffer) {

        if (!$this->file->canWrite()) {
            throw new IOException("No permission to write to file: " . $this->file->__toString());
        }

        $this->open();
        $result = @fwrite($this->fd, $buffer);
        $this->close();

        if ($result === false) {
            throw new IOException("Error writing file: ". $this->file->toString());
        } else {
            return true;
        }
    }

    function write($buf, $off = null, $len = null) {
        if ( $off === null && $len === null )
            $to_write = $buf;
        else
            $to_write = substr($buf, $off, $len);

        $this->open();
        $result = @fwrite($this->fd, $to_write);

        if ( $result === false ) {
            throw new IOException("Error writing file.");
        } else {
            return true;
        }
    }
    
    function getResource() {
        return $this->file->toString();
    }
}
?>
