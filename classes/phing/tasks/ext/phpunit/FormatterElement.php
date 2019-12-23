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

declare(strict_types=1);

/**
 * A wrapper for the implementations of PHPUnit2ResultFormatter.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @since   2.1.0
 */
class FormatterElement
{
    /**
     * @var PHPUnitResultFormatter7 $fomatter
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var bool
     */
    protected $useFile = true;

    /**
     * @var PhingFile|string
     */
    protected $toDir = '.';

    /**
     * @var string
     */
    protected $outfile = '';

    /**
     * @var PHPUnitTask
     */
    protected $parent;

    /**
     * Sets parent task
     *
     * @param PHPUnitTask $parent Calling Task
     *
     * @return void
     */
    public function setParent(PHPUnitTask $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Loads a specific formatter type
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Loads a specific formatter class
     *
     * @param string $className
     *
     * @return void
     *
     * @throws ConfigurationException
     */
    public function setClassName(string $className): void
    {
        $classNameNoDot = Phing::import($className);

        $this->formatter = new $classNameNoDot();
    }

    /**
     * Sets whether to store formatting results in a file
     *
     * @param bool $useFile
     *
     * @return void
     */
    public function setUseFile(bool $useFile): void
    {
        $this->useFile = $useFile;
    }

    /**
     * Returns whether to store formatting results in a file
     *
     * @return bool
     */
    public function getUseFile(): bool
    {
        return $this->useFile;
    }

    /**
     * Sets output directory
     *
     * @param string $toDir
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setToDir(string $toDir): void
    {
        if (!is_dir($toDir)) {
            $toDir = new PhingFile($toDir);
            $toDir->mkdirs();
        }

        $this->toDir = $toDir;
    }

    /**
     * Returns output directory
     *
     * @return string|PhingFile
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    /**
     * Sets output filename
     *
     * @param string $outfile
     *
     * @return void
     */
    public function setOutfile(string $outfile): void
    {
        $this->outfile = $outfile;
    }

    /**
     * Returns output filename
     *
     * @return string
     */
    public function getOutfile(): string
    {
        if ($this->outfile) {
            return $this->outfile;
        }

        return $this->formatter->getPreferredOutfile() . $this->getExtension();
    }

    /**
     * Returns extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->formatter->getExtension();
    }

    /**
     * Returns formatter object
     *
     * @return PHPUnitResultFormatter7
     *
     * @throws BuildException
     */
    public function getFormatter(): PHPUnitResultFormatter7
    {
        if ($this->formatter !== null) {
            return $this->formatter;
        }

        if ($this->type === 'summary') {
            $this->formatter = new SummaryPHPUnitResultFormatter7($this->parent);
        } elseif ($this->type === 'clover') {
            $this->formatter = new CloverPHPUnitResultFormatter7($this->parent);
        } elseif ($this->type === 'xml') {
            $this->formatter = new XMLPHPUnitResultFormatter7($this->parent);
        } elseif ($this->type === 'plain') {
            $this->formatter = new PlainPHPUnitResultFormatter7($this->parent);
        } elseif ($this->type === 'crap4j') {
            $this->formatter = new Crap4JPHPUnitResultFormatter7($this->parent);
        } else {
            throw new BuildException("Formatter '" . $this->type . "' not implemented");
        }

        return $this->formatter;
    }
}
