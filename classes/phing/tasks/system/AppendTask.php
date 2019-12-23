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
 *  Appends text, contents of a file or set of files defined by a filelist to a destination file.
 *
 * <code>
 * <append text="And another thing\n" destfile="badthings.log"/>
 * </code>
 * OR
 * <code>
 * <append file="header.html" destfile="fullpage.html"/>
 * <append file="body.html" destfile="fullpage.html"/>
 * <append file="footer.html" destfile="fullpage.html"/>
 * </code>
 * OR
 * <code>
 * <append destfile="${process.outputfile}">
 *    <filterchain>
 *        <xsltfilter style="${process.stylesheet}">
 *            <param name="mode" expression="${process.xslt.mode}"/>
 *            <param name="file_name" expression="%{task.append.current_file.basename}"/> <!-- Example of using a RegisterSlot variable -->
 *        </xsltfilter>
 *    </filterchain>
 *     <filelist dir="book/" listfile="book/PhingGuide.book"/>
 * </append>
 * </code>
 *
 * @package phing.tasks.system
 */
class AppendTask extends Task
{
    use FileListAware;
    use FileSetAware;
    use FilterChainAware;

    /**
     * Append stuff to this file.
     *
     * @var PhingFile
     */
    private $to;

    /**
     * Explicit file to append.
     *
     * @var PhingFile
     */
    private $file;

    /**
     * Text to append. (cannot be used in conjunction w/ files or filesets)
     *
     * @var string
     */
    private $text;

    /**
     * @var bool
     */
    private $filtering = true;

    /**
     * @var TextElement $header
     */
    private $header;

    /**
     * @var TextElement $footer
     */
    private $footer;

    /**
     * @var bool
     */
    private $append = true;

    /**
     * @var bool
     */
    private $fixLastLine = false;

    /**
     * @var bool
     */
    private $overwrite = true;

    /**
     * @var string
     */
    private $eolString;

    /**
     * @param bool $filtering
     *
     * @return void
     */
    public function setFiltering(bool $filtering): void
    {
        $this->filtering = $filtering;
    }

    /**
     * @param bool $overwrite
     *
     * @return void
     */
    public function setOverwrite(bool $overwrite): void
    {
        $this->overwrite = $overwrite;
    }

    /**
     * The more conventional naming for method to set destination file.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDestFile(PhingFile $f): void
    {
        $this->to = $f;
    }

    /**
     * Sets the behavior when the destination exists. If set to
     * <code>true</code> the task will append the stream data an
     * {@link Appendable} resource; otherwise existing content will be
     * overwritten. Defaults to <code>false</code>.
     *
     * @param bool $append if true append output.
     *
     * @return void
     */
    public function setAppend(bool $append): void
    {
        $this->append = $append;
    }

    /**
     * Specify the end of line to find and to add if
     * not present at end of each input file. This attribute
     * is used in conjunction with fixlastline.
     *
     * @param string $crlf the type of new line to add -
     *              cr, mac, lf, unix, crlf, or dos
     *
     * @return void
     */
    public function setEol(string $crlf): void
    {
        $s = $crlf;
        if ($s === 'cr' || $s === 'mac') {
            $this->eolString = "\r";
        } elseif ($s === 'lf' || $s === 'unix') {
            $this->eolString = "\n";
        } elseif ($s === 'crlf' || $s === 'dos') {
            $this->eolString = "\r\n";
        } else {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    /**
     * Sets specific file to append.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setFile(PhingFile $f): void
    {
        $this->file = $f;
    }

    /**
     * @return Path
     *
     * @throws Exception
     */
    public function createPath(): Path
    {
        $path             = new Path($this->getProject());
        $this->filesets[] = $path;
        return $path;
    }

    /**
     * Sets text to append.  (cannot be used in conjunction w/ files or filesets).
     *
     * @param string $txt
     *
     * @return void
     */
    public function setText(string $txt): void
    {
        $this->text = (string) $txt;
    }

    /**
     * Sets text to append. Supports CDATA.
     *
     * @param string $txt
     *
     * @return void
     */
    public function addText(string $txt): void
    {
        $this->text .= (string) $txt;
    }

    /**
     * @param TextElement $headerToAdd
     *
     * @return void
     */
    public function addHeader(TextElement $headerToAdd): void
    {
        $this->header = $headerToAdd;
    }

    /**
     * @param TextElement $footerToAdd
     *
     * @return void
     */
    public function addFooter(TextElement $footerToAdd): void
    {
        $this->footer = $footerToAdd;
    }

    /**
     * Append line.separator to files that do not end
     * with a line.separator, default false.
     *
     * @param bool $fixLastLine if true make sure each input file has
     *                          new line on the concatenated stream
     *
     * @return void
     */
    public function setFixLastLine(bool $fixLastLine): void
    {
        $this->fixLastLine = $fixLastLine;
    }

    /**
     * Append the file(s).
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws IOException
     */
    public function main(): void
    {
        $this->validate();

        try {
            if ($this->to !== null) {
                // create a file writer to append to "to" file.
                $writer = new FileWriter($this->to, $this->append);
            } else {
                $writer = new LogWriter($this);
            }

            if ($this->text !== null) {
                // simply append the text
                if ($this->to instanceof PhingFile) {
                    $this->log('Appending string to ' . $this->to->getPath());
                }

                $text = $this->text;
                if ($this->filtering) {
                    $fr   = $this->getFilteredReader(new StringReader($text));
                    $text = $fr->read();
                }

                $text = $this->appendHeader($text);
                $text = $this->appendFooter($text);
                $writer->write($text);
            } else {
                // append explicitly-specified file
                if ($this->file !== null) {
                    try {
                        $this->appendFile($writer, $this->file);
                    } catch (Throwable $ioe) {
                        $this->log(
                            'Unable to append contents of file ' . $this->file->getAbsolutePath() . ': ' . $ioe->getMessage(),
                            Project::MSG_WARN
                        );
                    }
                }

                // append any files in filesets
                foreach ($this->filesets as $fs) {
                    try {
                        if ($fs instanceof Path) {
                            $files = $fs->listPaths();
                            $this->appendFiles($writer, $files);
                        } elseif ($fs instanceof FileSet) {
                            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
                            $this->appendFiles($writer, $files, $fs->getDir($this->project));
                        }
                    } catch (BuildException $be) {
                        if (strpos($be->getMessage(), 'is the same as the output file') === false) {
                            $this->log($be->getMessage(), Project::MSG_WARN);
                        } else {
                            throw new BuildException($be->getMessage());
                        }
                    } catch (IOException $ioe) {
                        throw new BuildException($ioe);
                    }
                }

                foreach ($this->filelists as $list) {
                    $dir   = $list->getDir($this->project);
                    $files = $list->getFiles($this->project);
                    foreach ($files as $file) {
                        $this->appendFile($writer, new PhingFile($dir, $file));
                    }
                }
            }
        } catch (Throwable $e) {
            throw new BuildException($e);
        }

        $writer->close();
    }

    /**
     * @param string $string
     *
     * @return string
     *
     * @throws IOException
     * @throws Exception
     */
    private function appendHeader(string $string): string
    {
        $result = $string;
        if ($this->header !== null) {
            $header = $this->header->getValue();
            if ($this->header->filtering) {
                $fr     = $this->getFilteredReader(new StringReader($header));
                $header = $fr->read();
            }

            $result = $header . $string;
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @return string
     *
     * @throws IOException
     * @throws Exception
     */
    private function appendFooter(string $string): string
    {
        $result = $string;
        if ($this->footer !== null) {
            $footer = $this->footer->getValue();
            if ($this->footer->filtering) {
                $fr     = $this->getFilteredReader(new StringReader($footer));
                $footer = $fr->read();
            }

            $result = $string . $footer;
        }
        return $result;
    }

    /**
     * @return void
     */
    private function validate(): void
    {
        $this->sanitizeText();

        if ($this->file === null && $this->text === null && count($this->filesets) === 0 && count($this->filelists) === 0) {
            throw new BuildException('You must specify a file, use a filelist/fileset, or specify a text value.');
        }

        if ($this->text !== null && ($this->file !== null || count($this->filesets) > 0)) {
            throw new BuildException('Cannot use text attribute in conjunction with file or fileset');
        }

        if (!$this->eolString) {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    /**
     * @return void
     */
    private function sanitizeText(): void
    {
        if ($this->text !== null && '' === trim($this->text)) {
            $this->text = null;
        }
    }

    /**
     * @param Reader $r
     *
     * @return Reader
     *
     * @throws Exception
     */
    private function getFilteredReader(Reader $r): Reader
    {
        return FileUtils::getChainedReader($r, $this->filterChains, $this->getProject());
    }

    /**
     * Append an array of files in a directory.
     *
     * @param Writer         $writer The FileWriter that is appending to target file.
     * @param array          $files  array of files to delete; can be of zero length
     * @param PhingFile|null $dir    directory to work from
     *
     * @return void
     *
     * @throws Exception
     */
    private function appendFiles(Writer $writer, array $files, ?PhingFile $dir = null): void
    {
        if (!empty($files)) {
            $this->log(
                'Attempting to append ' . count(
                    $files
                ) . ' files' . ($dir !== null ? ', using basedir ' . $dir->getPath() : '')
            );
            $basenameSlot = Register::getSlot('task.append.current_file');
            $pathSlot     = Register::getSlot('task.append.current_file.path');
            foreach ($files as $file) {
                try {
                    if (!$this->checkFilename($file, $dir)) {
                        continue;
                    }

                    if ($dir !== null) {
                        $file = is_string($file) ? new PhingFile($dir->getPath(), $file) : $file;
                    } else {
                        $file = is_string($file) ? new PhingFile($file) : $file;
                    }
                    $basenameSlot->setValue($file);
                    $pathSlot->setValue($file->getPath());
                    $this->appendFile($writer, $file);
                } catch (IOException $ioe) {
                    $this->log(
                        'Unable to append contents of file ' . $file . ': ' . $ioe->getMessage(),
                        Project::MSG_WARN
                    );
                } catch (NullPointerException $npe) {
                    $this->log(
                        'Unable to append contents of file ' . $file . ': ' . $npe->getMessage(),
                        Project::MSG_WARN
                    );
                }
            }
        }
    }

    /**
     * @param string         $filename
     * @param PhingFile|null $dir
     *
     * @return bool
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    private function checkFilename(string $filename, ?PhingFile $dir = null): bool
    {
        if ($dir !== null) {
            $f = new PhingFile($dir, $filename);
        } else {
            $f = new PhingFile($filename);
        }

        if (!$f->exists()) {
            $this->log('File ' . (string) $f . ' does not exist.', Project::MSG_ERR);
            return false;
        }
        if ($this->to !== null && $f->equals($this->to)) {
            throw new BuildException(
                'Input file "'
                . $f . '" '
                . 'is the same as the output file.'
            );
        }

        if (
            $this->to !== null
            && !$this->overwrite
            && $this->to->exists()
            && $f->lastModified() > $this->to->lastModified()
        ) {
            $this->log((string) $this->to . ' is up-to-date.', Project::MSG_VERBOSE);
            return false;
        }

        return true;
    }

    /**
     * @param Writer    $writer
     * @param PhingFile $f
     *
     * @return void
     *
     * @throws IOException
     * @throws Exception
     */
    private function appendFile(Writer $writer, PhingFile $f): void
    {
        $in = $this->getFilteredReader(new FileReader($f));

        $text = '';
        while (-1 !== ($buffer = $in->read())) { // -1 indicates EOF
            $text .= $buffer;
        }
        if ($this->fixLastLine && ($text[strlen($text) - 1] !== "\n" || $text[strlen($text) - 1] !== "\r")) {
            $text .= $this->eolString;
        }

        $text = $this->appendHeader($text);
        $text = $this->appendFooter($text);
        $writer->write($text);
        if ($f instanceof PhingFile && $this->to instanceof PhingFile) {
            $this->log('Appending contents of ' . $f->getPath() . ' to ' . $this->to->getPath());
        }
    }
}
