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
 * Plain text formatter for PDO results.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.ext.pdo
 * @since   2.3.0
 */
class PlainPDOResultFormatter extends PDOResultFormatter
{
    /**
     * Have column headers been printed?
     *
     * @var bool
     */
    private $colsprinted = false;

    /**
     * Whether to show headers.
     *
     * @var bool
     */
    private $showheaders = true;

    /**
     * Column delimiter.
     * Defaults to ','
     *
     * @var string
     */
    private $coldelimiter = ',';

    /**
     * Row delimiter.
     * Defaults to PHP_EOL.
     *
     * @var string
     */
    private $rowdelimiter = PHP_EOL;

    /**
     * Set the showheaders attribute.
     *
     * @param bool $v
     *
     * @return void
     */
    public function setShowheaders(bool $v): void
    {
        $this->showheaders = StringHelper::booleanValue($v);
    }

    /**
     * Sets the column delimiter.
     *
     * @param string $v
     *
     * @return void
     */
    public function setColdelim(string $v): void
    {
        $this->coldelimiter = $v;
    }

    /**
     * Sets the row delimiter.
     *
     * @param string $v
     *
     * @return void
     */
    public function setRowdelim(string $v): void
    {
        $this->rowdelimiter = $v;
    }

    /**
     * Processes a specific row from PDO result set.
     *
     * @param array $row Row of PDO result set.
     *
     * @return void
     */
    public function processRow(array $row): void
    {
        if (!$this->colsprinted && $this->showheaders) {
            $first = true;
            $line  = '';
            foreach ($row as $fieldName => $ignore) {
                if ($first) {
                    $first = false;
                } else {
                    $line .= ',';
                }
                $line .= $fieldName;
            }

            $this->out->write($line);
            $this->out->write(PHP_EOL);

            $line              = '';
            $this->colsprinted = true;
        } // if show headers

        $first = true;
        foreach ($row as $columnValue) {
            if ($columnValue != null) {
                $columnValue = trim((string) $columnValue);
            }

            if ($first) {
                $first = false;
            } else {
                $line .= $this->coldelimiter;
            }
            $line .= $columnValue;
        }

        $this->out->write($line);
        $this->out->write($this->rowdelimiter);
    }

    /**
     * @return PhingFile
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function getPreferredOutfile(): PhingFile
    {
        return new PhingFile('results.txt');
    }
}
