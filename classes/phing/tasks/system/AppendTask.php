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

require_once 'phing/Task.php';
include_once 'phing/types/FileList.php';
include_once 'phing/types/FileSet.php';
include_once 'phing/tasks/system/AppendTask/TextElement.php';

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
 * @package phing.tasks.system
 * @version $Id$
 */
class AppendTask extends Task
{
    /** Append stuff to this file. */
    private $to;

    /** Explicit file to append. */
    private $file;

    /** Any filesets of files that should be appended. */
    private $filesets = array();

    /** Any filters to be applied before append happens. */
    private $filterChains = array();

    /** Text to append. (cannot be used in conjunction w/ files or filesets) */
    private $text;

    private $filtering = true;

    /** @var TextElement $header */
    private $header;

    /** @var TextElement $footer */
    private $footer;

    private $append = true;

    private $fixLastLine = false;

    private $overwrite = true;

    private $eolString;

    private $fileList = array();

    /**
     * @param bool $filtering
     */
    public function setFiltering($filtering)
    {
        $this->filtering = (bool)$filtering;
    }

    /**
     * @param bool $overwrite
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * Set target file to append to.
     *
     * @deprecated Will be removed with final release.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setTo(PhingFile $f)
    {
        $this->log(
            "The 'to' attribute is deprecated in favor of 'destFile'; please update your code.",
            Project::MSG_WARN
        );
        $this->to = $f;
    }

    /**
     * The more conventional naming for method to set destination file.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDestFile(PhingFile $f)
    {
        $this->to = $f;
    }

    /**
     * Sets the behavior when the destination exists. If set to
     * <code>true</code> the task will append the stream data an
     * {@link Appendable} resource; otherwise existing content will be
     * overwritten. Defaults to <code>false</code>.
     * @param bool $append if true append output.
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }

    /**
     * Specify the end of line to find and to add if
     * not present at end of each input file. This attribute
     * is used in conjunction with fixlastline.
     * @param string $crlf the type of new line to add -
     *              cr, mac, lf, unix, crlf, or dos
     */
    public function setEol($crlf)
    {
        $s = $crlf;
        if ($s === "cr" || $s === "mac") {
            $this->eolString = "\r";
        } elseif ($s === "lf" || $s === "unix") {
            $this->eolString = "\n";
        } elseif ($s === "crlf" || $s === "dos") {
            $this->eolString = "\r\n";
        } else {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    /**
     * Sets specific file to append.
     * @param PhingFile $f
     */
    public function setFile(PhingFile $f)
    {
        $this->file = $f;
    }

    /**
     * Supports embedded <filelist> element.
     *
     * @return FileList
     */
    public function addFileList(FileList $fileList)
    {
        $this->fileList[] = $fileList;
    }

    public function createPath()
    {
        $path = new Path($this->getProject());
        $this->filesets[] = $path;
        return $path;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Creates a filterchain
     *
     * @return FilterChain The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /**
     * Sets text to append.  (cannot be used in conjunction w/ files or filesets).
     *
     * @param string $txt
     *
     * @return void
     */
    public function setText($txt)
    {
        $this->text = (string)$txt;
    }

    /**
     * Sets text to append. Supports CDATA.
     *
     * @param string $txt
     *
     * @return void
     */
    public function addText($txt)
    {
        $this->text .= (string)$txt;
    }

    public function addHeader(TextElement $headerToAdd)
    {
        $this->header = $headerToAdd;
    }

    public function addFooter(TextElement $footerToAdd)
    {
        $this->footer = $footerToAdd;
    }

    /**
     * Append line.separator to files that do not end
     * with a line.separator, default false.
     * @param bool $fixLastLine if true make sure each input file has
     *                          new line on the concatenated stream
     */
    public function setFixLastLine($fixLastLine)
    {
        $this->fixLastLine = $fixLastLine;
    }

    /**
     * Append the file(s).
     *
     * {@inheritdoc}
     */
    public function main()
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
                    $this->log("Appending string to " . $this->to->getPath());
                }

                $text = $this->text;
                if ($this->filtering) {
                    $fr = $this->getFilteredReader(new StringReader($text));
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
                    } catch (Exception $ioe) {
                        $this->log(
                            "Unable to append contents of file " . $this->file->getAbsolutePath() . ": " . $ioe->getMessage(),
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

                /** @var FileList $list */
                foreach ($this->fileList as $list) {
                    $dir = $list->getDir($this->project);
                    $files = $list->getFiles($this->project);
                    foreach ($files as $file) {
                        $this->appendFile($writer, new PhingFile($dir, $file));
                    }
                }
            }
        } catch (Exception $e) {
            throw new BuildException($e);
        }

        $writer->close();
    }

    private function appendHeader($string)
    {
        $result = $string;
        if ($this->header !== null) {
            $header = $this->header->getValue();
            if ($this->header->filtering) {
                $fr = $this->getFilteredReader(new StringReader($header));
                $header = $fr->read();
            }

            $result = $header . $string;
        }

        return $result;
    }

    private function appendFooter($string)
    {
        $result = $string;
        if ($this->footer !== null) {
            $footer = $this->footer->getValue();
            if ($this->footer->filtering) {
                $fr = $this->getFilteredReader(new StringReader($footer));
                $footer = $fr->read();
            }

            $result = $string . $footer;
        }
        return $result;
    }

    private function validate()
    {
        $this->sanitizeText();

        if ($this->file === null && $this->text === null && count($this->filesets) === 0 && count($this->fileList) === 0) {
            throw new BuildException("You must specify a file, use a filelist/fileset, or specify a text value.");
        }

        if ($this->text !== null && ($this->file !== null || count($this->filesets) > 0)) {
            throw new BuildException("Cannot use text attribute in conjunction with file or fileset");
        }

        if (!$this->eolString) {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    private function sanitizeText()
    {
        if ($this->text !== null && "" === trim($this->text)) {
            $this->text = null;
        }
    }

    private function getFilteredReader(Reader $r)
    {
        $helper = FileUtils::getChainedReader($r, $this->filterChains, $this->getProject());
        return $helper;
    }

    /**
     * Append an array of files in a directory.
     *
     * @param Writer $writer The FileWriter that is appending to target file.
     * @param array $files array of files to delete; can be of zero length
     * @param PhingFile $dir directory to work from
     *
     * @return void
     */
    private function appendFiles(Writer $writer, $files, PhingFile $dir = null)
    {
        if (!empty($files)) {
            $this->log(
                "Attempting to append " . count(
                    $files
                ) . " files" . ($dir !== null ? ", using basedir " . $dir->getPath() : "")
            );
            $basenameSlot = Register::getSlot("task.append.current_file");
            $pathSlot = Register::getSlot("task.append.current_file.path");
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
                        "Unable to append contents of file " . $file . ": " . $ioe->getMessage(),
                        Project::MSG_WARN
                    );
                } catch (NullPointerException $npe) {
                    $this->log(
                        "Unable to append contents of file " . $file . ": " . $npe->getMessage(),
                        Project::MSG_WARN
                    );
                }
            }
        }
    }

    private function checkFilename($filename, $dir = null)
    {
        if ($dir !== null) {
            $f = new PhingFile($dir, $filename);
        } else {
            $f = new PhingFile($filename);
        }

        if (!$f->exists()) {
            $this->log("File " . (string)$f . " does not exist.", Project::MSG_ERR);
            return false;
        }
        if ($this->to !== null && $f->equals($this->to)) {
            throw new BuildException("Input file \""
                . $f . "\" "
                . "is the same as the output file.");
        }

        if ($this->to !== null
            && !$this->overwrite
            && $this->to->exists()
            && $f->lastModified() > $this->to->lastModified()
        ) {
            $this->log((string)$this->to . " is up-to-date.", Project::MSG_VERBOSE);
            return false;
        }

        return true;
    }

    /**
     * @param FileWriter $writer
     * @param PhingFile $f
     *
     * @return void
     */
    private function appendFile(Writer $writer, PhingFile $f)
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
            $this->log("Appending contents of " . $f->getPath() . " to " . $this->to->getPath());
        }
    }
}
