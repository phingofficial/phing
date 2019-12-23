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
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class TruncateTask extends Task
{
    /**
     * @var bool
     */
    private $create = true;

    /**
     * @var bool
     */
    private $mkdirs = false;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $adjust;

    /**
     * @var PhingFile
     */
    private $file;

    /**
     * Set a single target File.
     *
     * @param PhingFile|string $f the single File
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setFile($f): void
    {
        if (is_string($f)) {
            $f = new PhingFile($f);
        }
        $this->file = $f;
    }

    /**
     * Set the amount by which files' lengths should be adjusted.
     * It is permissible to append K / M / G / T / P.
     *
     * @param int $adjust (positive or negative) adjustment amount.
     *
     * @return void
     */
    public function setAdjust(int $adjust): void
    {
        $this->adjust = $adjust;
    }

    /**
     * Set the length to which files should be set.
     * It is permissible to append K / M / G / T / P.
     *
     * @param int $length (positive) adjustment amount.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
        if ($this->length !== null && $this->length < 0) {
            throw new BuildException('Cannot truncate to length ' . $this->length);
        }
    }

    /**
     * Set whether to create nonexistent files.
     *
     * @param bool $create default <code>true</code>.
     *
     * @return void
     */
    public function setCreate(bool $create): void
    {
        $this->create = $create;
    }

    /**
     * Set whether, when creating nonexistent files, nonexistent directories
     * should also be created.
     *
     * @param bool $mkdirs default <code>false</code>.
     *
     * @return void
     */
    public function setMkdirs(bool $mkdirs): void
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * {@inheritDoc}.
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function main(): void
    {
        if ($this->length !== null && $this->adjust !== null) {
            throw new BuildException(
                'length and adjust are mutually exclusive options'
            );
        }
        if ($this->length === null && $this->adjust === null) {
            $this->length = 0;
        }
        if ($this->file === null) {
            throw new BuildException('No files specified.');
        }

        if ($this->shouldProcess($this->file)) {
            $this->process($this->file);
        }
    }

    /**
     * @param PhingFile $f
     *
     * @return bool
     *
     * @throws BuildException
     * @throws NullPointerException
     * @throws Exception
     */
    private function shouldProcess(PhingFile $f): bool
    {
        if ($f->isFile()) {
            return true;
        }
        if (!$this->create) {
            return false;
        }
        $exception = null;
        try {
            /**
             * @var PhingFile $parent
             */
            $parent = $f->getParentFile();
            if ($this->mkdirs && !$parent->exists()) {
                $parent->mkdirs();
            }

            if ($f->createNewFile()) {
                return true;
            }
        } catch (IOException $e) {
            $exception = $e;
        }
        $msg = 'Unable to create ' . $f;
        if ($exception === null) {
            $this->log($msg, Project::MSG_WARN);
            return false;
        }
        throw new BuildException($msg, $exception);
    }

    /**
     * @param PhingFile $f
     *
     * @return void
     *
     * @throws IOException
     */
    private function process(PhingFile $f): void
    {
        $len       = $f->length();
        $newLength = $this->length ?? $len + $this->adjust;

        if ($len === $newLength) {
            //nothing to do!
            return;
        }

        $splFile = new SplFileObject($f->getPath(), 'a+');

        if (!$splFile->ftruncate((int) $newLength)) {
            throw new BuildException('Exception working with ' . (string) $splFile);
        }

        $splFile->rewind();
        clearstatcache();
    }
}
