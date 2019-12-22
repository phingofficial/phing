<?php
/**
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
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTaskFormatterElement extends DataType
{
    /**
     * Type of output to generate
     *
     * @var string
     */
    protected $type = '';

    /**
     * Output to file?
     *
     * @var bool
     */
    protected $useFile = true;

    /**
     * Output file.
     *
     * @var string
     */
    protected $outfile = '';

    /**
     * Validate config.
     */
    public function parsingComplete()
    {
        if (empty($this->type)) {
            throw new BuildException("Format missing required 'type' attribute.");
        }
        if ($this->useFile && empty($this->outfile)) {
            throw new BuildException("Format requires 'outfile' attribute when 'useFile' is true.");
        }
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * @return bool
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * @param string $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * @return string
     */
    public function getOutfile()
    {
        return $this->outfile;
    }
}
