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
 * A wrapper for the implementations of PHPCPDResultFormatter.
 *
 * @package phing.tasks.ext.phpcpd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 */
class PHPCPDFormatterElement
{
    /**
     * The report result formatter.
     *
     * @var PHPCPDResultFormatter
     */
    protected $formatter = null;

    /**
     * The type of the formatter.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Whether to use file (or write output to phing log).
     *
     * @var bool
     */
    protected $useFile = true;

    /**
     * Output file for formatter.
     *
     * @var PhingFile|null
     */
    protected $outfile = null;

    /**
     * The parent task
     *
     * @var PHPCPDTask
     */
    private $parentTask;

    /**
     * Construct a new PHPCPDFormatterElement with parent task.
     *
     * @param PHPCPDTask $parentTask
     */
    public function __construct(PHPCPDTask $parentTask)
    {
        $this->parentTask = $parentTask;
    }

    /**
     * Sets the formatter type.
     *
     * @param string $type Type of the formatter
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setType(string $type): void
    {
        $this->type = $type;

        switch ($this->type) {
            case 'pmd':
                if ($this->useFile === false) {
                    throw new BuildException('Formatter "' . $this->type . '" can only print the result to an file');
                }

                $this->formatter = new PMDPHPCPDResultFormatter();
                break;

            case 'default':
                $this->formatter = new DefaultPHPCPDResultFormatter();
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set whether to write formatter results to file or not.
     *
     * @param bool $useFile True or false.
     *
     * @return void
     */
    public function setUseFile(bool $useFile): void
    {
        $this->useFile = StringHelper::booleanValue($useFile);
    }

    /**
     * Return whether to write formatter results to file or not.
     *
     * @return bool
     */
    public function getUseFile(): bool
    {
        return $this->useFile;
    }

    /**
     * Sets the output file for the formatter results.
     *
     * @param PhingFile $outfile The output file
     *
     * @return void
     */
    public function setOutfile(PhingFile $outfile): void
    {
        $this->outfile = $outfile;
    }

    /**
     * Get the output file.
     *
     * @return PhingFile|null
     */
    public function getOutfile(): ?PhingFile
    {
        return $this->outfile;
    }

    /**
     * Returns the report formatter.
     *
     * @return PHPCPDResultFormatter
     */
    public function getFormatter(): PHPCPDResultFormatter
    {
        return $this->formatter;
    }
}
