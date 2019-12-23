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
 * @author Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phploc
 */
abstract class AbstractPHPLocFormatter
{
    /**
     * @param array $count
     * @param bool  $countTests
     *
     * @return void
     */
    abstract public function printResult(array $count, bool $countTests = false): void;

    /**
     * @var bool
     */
    protected $useFile = true;

    /**
     * @var string|PhingFile
     */
    protected $toDir = '.';

    /**
     * @var string
     */
    protected $outfile = '';

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
        if (!is_dir($toDir) && null !== $toDir) {
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
        return $this->outfile;
    }
}
