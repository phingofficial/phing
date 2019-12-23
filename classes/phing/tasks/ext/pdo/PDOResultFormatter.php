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
 * Abstract
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.ext.pdo
 * @since   2.3.0
 */
abstract class PDOResultFormatter
{
    /**
     * Output writer.
     *
     * @var Writer
     */
    protected $out;

    /**
     * Sets the output writer.
     *
     * @param Writer $out
     *
     * @return void
     */
    public function setOutput(Writer $out): void
    {
        $this->out = $out;
    }

    /**
     * Gets the output writer.
     *
     * @return Writer
     */
    public function getOutput(): Writer
    {
        return $this->out;
    }

    /**
     * Gets the preferred output filename for this formatter.
     *
     * @return PhingFile
     */
    abstract public function getPreferredOutfile(): PhingFile;

    /**
     * Perform any initialization.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Processes a specific row from PDO result set.
     *
     * @param array $row Row of PDO result set.
     *
     * @return void
     */
    abstract public function processRow(array $row): void;

    /**
     * Perform any final tasks and Close the writer.
     *
     * @return void
     *
     * @throws IOException
     */
    public function close(): void
    {
        $this->out->close();
    }
}
