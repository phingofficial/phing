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
 * Transform a Phing/Xdebug code coverage xml report.
 * The default transformation generates an html report in framed style.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.coverage
 * @since   2.1.0
 */
class CoverageReportTransformer
{
    /**
     * @var Task|null
     */
    private $task = null;

    /**
     * @var string
     */
    private $styleDir = '';

    /**
     * @var PhingFile
     */
    private $toDir;

    /**
     * @var DOMDocument|SimpleXMLElement|null
     */
    private $document = null;

    /**
     * title of the project, used in the coverage report
     *
     * @var string
     */
    private $title = '';

    /**
     * Whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @var bool
     */
    private $useSortTable = false;

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @param string $styleDir
     *
     * @return void
     */
    public function setStyleDir(string $styleDir): void
    {
        $this->styleDir = $styleDir;
    }

    /**
     * @param PhingFile $toDir
     *
     * @return void
     */
    public function setToDir(PhingFile $toDir): void
    {
        $this->toDir = $toDir;
    }

    /**
     * @param DOMDocument|SimpleXMLElement $document
     *
     * @return void
     */
    public function setXmlDocument($document): void
    {
        $this->document = $document;
    }

    /**
     * Setter for title parameter
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Sets whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @param bool $useSortTable
     *
     * @return void
     */
    public function setUseSortTable(bool $useSortTable): void
    {
        $this->useSortTable = (bool) $useSortTable;
    }

    /**
     * @return void
     *
     * @throws NullPointerException
     * @throws IOException
     */
    public function transform(): void
    {
        if (!$this->toDir->exists()) {
            throw new BuildException("Directory '" . $this->toDir . "' does not exist");
        }

        $xslfile = $this->getStyleSheet();

        $xsl = new DOMDocument();
        $xsl->load($xslfile->getAbsolutePath());

        $proc = new XSLTProcessor();
        if (defined('XSL_SECPREF_WRITE_FILE')) {
            $proc->setSecurityPrefs(XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
        }

        $proc->registerPHPFunctions('nl2br');
        $proc->importStylesheet($xsl);

        ExtendedFileStream::registerStream();

        $toDir = (string) $this->toDir;

        // urlencode() the path if we're on Windows
        if (FileSystem::getFileSystem()->getSeparator() == '\\') {
            $toDir = urlencode($toDir);
        }

        // no output for the framed report
        // it's all done by extension...
        $proc->setParameter('', 'output.dir', $toDir);

        $proc->setParameter('', 'output.sorttable', $this->useSortTable);
        $proc->setParameter('', 'document.title', $this->title);
        $proc->transformToXML($this->document);

        ExtendedFileStream::unregisterStream();
    }

    /**
     * @return PhingFile
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws BuildException
     */
    private function getStyleSheet(): PhingFile
    {
        $xslname = 'coverage-frames.xsl';

        if ($this->styleDir) {
            $file = new PhingFile($this->styleDir, $xslname);
        } else {
            $path = Phing::getResourcePath('phing/etc/' . $xslname);

            if ($path === null) {
                $path = Phing::getResourcePath('etc/' . $xslname);

                if ($path === null) {
                    throw new BuildException('Could not find ' . $xslname . ' in resource path');
                }
            }

            $file = new PhingFile($path);
        }

        if (!$file->exists()) {
            throw new BuildException('Could not find file ' . $file->getPath());
        }

        return $file;
    }
}
