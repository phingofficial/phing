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
include_once 'phing/Task.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/BuildException.php';

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class TruncateTask extends Task
{
    private $create = true;
    private $mkdirs = false;

    private $length;
    private $adjust;
    private $file;

    /**
     * Set a single target File.
     * @param PhingFile|string $f the single File
     * @throws \IOException
     * @throws \NullPointerException
     */
    public function setFile($f)
    {
        if (is_string($f)) {
            $f = new PhingFile($f);
        }
        $this->file = $f;
    }

    /**
     * Set the amount by which files' lengths should be adjusted.
     * It is permissible to append K / M / G / T / P.
     * @param $adjust (positive or negative) adjustment amount.
     */
    public function setAdjust($adjust)
    {
        $this->adjust = $adjust;
    }

    /**
     * Set the length to which files should be set.
     * It is permissible to append K / M / G / T / P.
     * @param $length (positive) adjustment amount.
     *
     * @throws \BuildException
     */
    public function setLength($length)
    {
        $this->length = $length;
        if ($this->length !== null && $this->length < 0) {
            throw new BuildException('Cannot truncate to length ' . $this->length);
        }
    }

    /**
     * Set whether to create nonexistent files.
     * @param boolean $create default <code>true</code>.
     */
    public function setCreate($create)
    {
        $this->create = $create;
    }

    /**
     * Set whether, when creating nonexistent files, nonexistent directories
     * should also be created.
     * @param boolean $mkdirs default <code>false</code>.
     */
    public function setMkdirs($mkdirs)
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * {@inheritDoc}.
     * @throws \BuildException
     */
    public function main()
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
     * @return bool
     * @throws \BuildException
     */
    private function shouldProcess(PhingFile $f)
    {
        if ($f->isFile()) {
            return true;
        }
        if (!$this->create) {
            return false;
        }
        $exception = null;
        try {
            /** @var PhingFile $parent */
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
        $msg = "Unable to create " . $f;
        if ($exception === null) {
            $this->log($msg, Project::MSG_WARN);
            return false;
        }
        throw new BuildException($msg, $exception);
    }

    private function process(PhingFile $f)
    {
        $len = $f->length();
        $newLength = $this->length === null
            ? $len + $this->adjust
            : $this->length;

        if ($len === $newLength) {
            //nothing to do!
            return;
        }

        $splFile = new SplFileObject($f->getPath(), 'a+');

        if (!$splFile->ftruncate((int) $newLength)) {
            throw new BuildException("Exception working with " . (string)$splFile);
        }

        $splFile->rewind();
        clearstatcache();
    }
}
