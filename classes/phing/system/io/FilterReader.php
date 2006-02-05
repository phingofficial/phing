<?php
/*
 *  $Id: FilterReader.php,v 1.6 2005/05/26 13:10:52 mrook Exp $
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

include_once 'phing/system/io/Reader.php';

/**
 * Convenience class for reading files. The constructor of this
 *  @package   phing.system.io
 *
 * TODO: All filters should be ProjectComponents, too!
 */
class FilterReader extends Reader {
    
    protected $in;
    
    function __construct(Reader $in = null) {
        $this->in = $in;
        //parent::__construct(new FileReader($file, $exclusive));
    }
    
    public function setReader(Reader $in) {
        $this->in = $in;
    }
    
    public function skip($n) {
        return $this->in->skip($n);
    }
    
    /**
     * Read data from source.
     * FIXME: Clean up this function signature, as it a) params aren't being used
     * and b) it doesn't make much sense.
     */
    public function read($len = null) {
        return $this->in->read($len);
    }

    public function reset() {
        return $this->in->reset();
    }
    
    public function close() {
        return $this->in->close();
    }
    
    public function open() {
        return $this->in->open();
    }

    function getResource() {
        return $this->in->getResource();
    }
}
?>
