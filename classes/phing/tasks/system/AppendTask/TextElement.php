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
 * Text element points to a file or contains text.
 * @package phing.tasks.system.AppendTask
 */
class TextElement extends ProjectComponent
{
    public $value = "";
    public $trimLeading = false;
    public $trim = false;
    public $filtering = true;
    public $encoding = null;

    /**
     * whether to filter the text in this element
     * or not.
     *
     * @param filtering true if the text should be filtered.
     *                  the default value is true.
     */
    public function setFiltering($filtering)
    {
        $this->filtering = $filtering;
    }

    /** return the filtering attribute */
    private function getFiltering()
    {
        return $this->filtering;
    }

    /**
     * The encoding of the text element
     *
     * @param encoding the name of the charset used to encode
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * set the text using a file
     * @param file the file to use
     * @throws BuildException if the file does not exist, or cannot be
     *                        read
     */
    public function setFile(PhingFile $file)
    {
        // non-existing files are not allowed
        if (!$file->exists()) {
            throw new BuildException("File " . $file . " does not exist.");
        }

        $reader = null;
        try {
            if ($this->encoding == null) {
                $reader = new BufferedReader(new FileReader($file));
            } else {
                $reader = new BufferedReader(
                    new InputStreamReader(new FileInputStream($file))
                );
            }
            $this->value = $reader->read();
        } catch (IOException $ex) {
            $reader->close();
            throw new BuildException($ex);
        }
        $reader->close();
    }

    /**
     * set the text using inline
     * @param value the text to place inline
     */
    public function addText($value)
    {
        $this->value .= $this->getProject()->replaceProperties($value);
    }

    /**
     * s:^\s*:: on each line of input
     * @param strip if true do the trim
     */
    public function setTrimLeading($strip)
    {
        $this->trimLeading = $strip;
    }

    /**
     * whether to call text.trim()
     * @param trim if true trim the text
     */
    public function setTrim($trim)
    {
        $this->trim = $trim;
    }

    /**
     * @return the text, after possible trimming
     */
    public function getValue()
    {
        if ($this->value == null) {
            $this->value = "";
        }
        if (trim($this->value) === '') {
            $this->value = "";
        }
        if ($this->trimLeading) {
            $current = str_split($this->value);
                $b = '';
                $startOfLine = true;
                $pos = 0;
                while ($pos < count($current)) {
                    $ch = $current[$pos++];
                    if ($startOfLine) {
                        if ($ch == ' ' || $ch == "\t") {
                            continue;
                        }
                        $startOfLine = false;
                    }
                    $b .= $ch;
                    if ($ch == "\n" || $ch == "\r") {
                        $startOfLine = true;
                    }
                }
                $this->value = $b;
            }
        if ($this->trim) {
            $this->value = trim($this->value);
        }
        return $this->value;
    }
}
