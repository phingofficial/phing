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

require_once 'phing/system/io/PhingFile.php';

/**
 * A wrapper for the implementations of PHPMDResultFormatter.
 *
 * @package phing.tasks.ext.phpmd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id$
 * @since   2.4.1
 */
class PHPMDFormatterElement
{
    /**
     * @var PHPMDResultFormatter
     */
    protected $formatter = null;

    /**
     * The type of the formatter.
     *
     * @var string
     */
    protected $type = "";

    /**
     * Whether to use file (or write output to phing log).
     *
     * @var boolean
     */
    protected $useFile = true;

    /**
     * Output file for formatter.
     *
     * @var PhingFile
     */
    protected $outfile = null;

    /**
     * Sets the formatter type.
     *
     * @param string $type Type of the formatter
     *
     * @throws BuildException
     */
    public function setType($type)
    {
        $this->type = $type;
        $root = false === stream_resolve_include_path("PHP/PMD.php") ? "PHPMD/" : "PHP/PMD/";
        switch ($this->type) {
            case 'xml':
                include_once $root .'Renderer/XMLRenderer.php';
                break;

            case 'html':
                include_once $root . 'Renderer/HTMLRenderer.php';
                break;

            case 'text':
                include_once $root . 'Renderer/TextRenderer.php';
                break;

            default:
                throw new BuildException('Formatter "' . $this->type . '" not implemented');
        }
    }

    /**
     * Get the formatter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set whether to write formatter results to file or not.
     *
     * @param boolean $useFile True or false.
     */
    public function setUseFile($useFile)
    {
        $this->useFile = StringHelper::booleanValue($useFile);
    }

    /**
     * Return whether to write formatter results to file or not.
     *
     * @return boolean
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets the output file for the formatter results.
     *
     * @param PhingFile $outfile The output file
     */
    public function setOutfile(PhingFile $outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Get the output file.
     *
     * @return PhingFile
     */
    public function getOutfile()
    {
        return $this->outfile;
    }

    /**
     * Creates a report renderer instance based on the formatter type.
     *
     * @return PHP_PMD_AbstractRenderer
     * @throws BuildException When the specified renderer does not exist.
     */
    public function getRenderer()
    {
        if(false === stream_resolve_include_path("PHP/PMD.php")){
            $render_root = 'PHPMD\Renderer\\';
            $writer_class = '\PHPMD\Writer\StreamWriter'; 
            $writer_file = 'PHPMD/Writer/StreamWriter.php';
        } else {
            $render_root = 'PHP_PMD_RENDERER_';
            $writer_class = 'PHP_PMD_Writer_Stream';
            $writer_file = 'PHP/PMD/Writer/Stream.php';
        }

        switch ($this->type) {
            case 'xml':
                $class = $render_root.'XMLRenderer';
                break;

            case 'html':
                $class = $render_root.'HTMLRenderer';
                break;

            case 'text':
                $class = $render_root.'TextRenderer';
                break;

            default:
                throw new BuildException('PHP_MD renderer "' . $this->type . '" not implemented');
        }
        $renderer = new $class();
        
				// Create a report stream
        if ($this->getUseFile() === false || $this->getOutfile() === null) {
            $stream = STDOUT;
        } else {
            $stream = fopen($this->getOutfile()->getAbsoluteFile(), 'wb');
        }

				
        require_once $writer_file;

        $renderer->setWriter(new $writer_class($stream));

        return $renderer;
    }
}
