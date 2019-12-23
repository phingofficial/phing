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
 * Text element points to a file or contains text.
 *
 * @package phing.tasks.system.AppendTask
 */
class TextElement extends ProjectComponent
{
    /**
     * @var string
     */
    public $value = '';

    /**
     * @var bool
     */
    public $trimLeading = false;

    /**
     * @var bool
     */
    public $trim = false;

    /**
     * @var bool
     */
    public $filtering = true;

    /**
     * @var string|null
     */
    public $encoding = null;

    /**
     * whether to filter the text in this element
     * or not.
     *
     * @param bool $filtering True if the text should be filtered.
     *                        the default value is true.
     *
     * @return void
     */
    public function setFiltering(bool $filtering): void
    {
        $this->filtering = $filtering;
    }

    /**
     * The encoding of the text element
     *
     * @param string $encoding the name of the charset used to encode
     *
     * @return void
     */
    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    /**
     * set the text using a file
     *
     * @param PhingFile $file the file to use
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException if the file does not exist, or cannot be read
     * @throws Exception
     */
    public function setFile(PhingFile $file): void
    {
        // non-existing files are not allowed
        if (!$file->exists()) {
            throw new BuildException('File ' . $file . ' does not exist.');
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
            throw new BuildException($ex);
        } finally {
            $reader->close();
        }
    }

    /**
     * set the text using inline
     *
     * @param string $value the text to place inline
     *
     * @return void
     */
    public function addText(string $value): void
    {
        $this->value .= $this->getProject()->replaceProperties($value);
    }

    /**
     * s:^\s*:: on each line of input
     *
     * @param bool $trimLeading if true do the trim
     *
     * @return void
     */
    public function setTrimLeading(bool $trimLeading): void
    {
        $this->trimLeading = $trimLeading;
    }

    /**
     * whether to call text.trim()
     *
     * @param bool $trim if true trim the text
     *
     * @return void
     */
    public function setTrim(bool $trim): void
    {
        $this->trim = $trim;
    }

    /**
     * @return string the text, after possible trimming
     */
    public function getValue(): string
    {
        if ($this->value == null) {
            $this->value = '';
        }
        if (trim($this->value) === '') {
            $this->value = '';
        }
        if ($this->trimLeading) {
            $current     = str_split($this->value);
            $b           = '';
            $startOfLine = true;
            $pos         = 0;
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
