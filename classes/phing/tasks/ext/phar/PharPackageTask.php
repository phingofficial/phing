<?php
/*
 * $Id$
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

require_once 'phing/tasks/system/MatchingTask.php';
require_once 'phing/tasks/ext/phar/IterableFileSet.php';
require_once 'phing/tasks/ext/phar/PharMetadata.php';

/**
 * Package task for {@link http://ru.php.net/manual/en/book.phar.php Phar technology}.
 *
 * @package phing.tasks.ext
 * @author Alexey Shockov <alexey@shockov.com>
 * @since 2.4.0
 */
class PharPackageTask
    extends MatchingTask
{
    /**
     * @var PhingFile
     */
    private $destinationFile;
    /**
     * @var int
     */
    private $compression = Phar::NONE;
    /**
     * Base directory, from where local package paths will be calculated.
     *
     * @var PhingFile
     */
    private $baseDirectory;
    /**
     * @var PhingFile
     */
    private $cliStubFile;
    /**
     * @var PhingFile
     */
    private $webStubFile;
    /**
     * @var int
     */
    private $signatureAlgorithm = Phar::SHA1;
    /**
     * @var array
     */
    private $filesets = array();
    /**
     * @var PharMetadata
     */
    private $metadata;
    /**
     * @return PharMetadata
     */
    public function createMetadata()
    {
        return ($this->metadata = new PharMetadata());
    }
    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $this->fileset      = new IterableFileSet();
        $this->filesets[]   = $this->fileset;
        return $this->fileset;
    }
    /**
     * @param string $algorithm
     */
    public function setSignature($algorithm)
    {
        /*
         * If we don't support passed algprithm, leave old one.
         */
        switch ($algorithm) {
            case 'md5':
                $this->signatureAlgorithm = Phar::MD5;
                break;
            case 'sha1':
                $this->signatureAlgorithm = Phar::SHA1;
                break;
            case 'sha256':
                $this->signatureAlgorithm = Phar::SHA256;
                break;
            case 'sha512':
                $this->signatureAlgorithm = Phar::SHA512;
                break;
            default:
                break;
        }
    }
    /**
     * @param string $compression
     */
    public function setCompression($compression)
    {
        /*
         * If we don't support passed compression, leave old one.
         */
        switch ($compression) {
            case 'gzip':
                $this->compression = Phar::GZ;
                break;
            case 'bzip2':
                $this->compression = Phar::BZ2;
                break;
            default:
                break;
        }
    }
    /**
     * @param PhingFile $destinationFile
     */
    public function setDestFile(PhingFile $destinationFile)
    {
        $this->destinationFile = $destinationFile;
    }
    /**
     * @param PhingFile $baseDirectory
     */
    public function setBaseDir(PhingFile $baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }
    /**
     * @param PhingFile $stubFile
     */
    public function setCliStub(PhingFile $stubFile)
    {
        $this->cliStubFile = $stubFile;
    }
    /**
     * @param PhingFile $stubFile
     */
    public function setWebStub(PhingFile $stubFile)
    {
        $this->webStubFile = $stubFile;
    }
    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->checkPreconditions();

        try {
            $this->log(
                'Building package: '.$this->destinationFile->__toString(),
                Project::MSG_INFO
            );

            /*
             * Delete old package, if exists.
             */
            if ($this->destinationFile->exists()) {
                /*
                 * TODO Check operation for errors...
                 */
                $this->destinationFile->delete();
            }

            $phar = $this->buildPhar();
            $phar->startBuffering();

            $baseDirectory = realpath($this->baseDirectory->getPath());

            foreach ($this->filesets as $fileset) {
                foreach ($fileset as $realFileName) {
                    /*
                     * Calculate local file name.
                     */
                    $localFileName = $realFileName;
                    if (0 === strpos($realFileName, $baseDirectory)) {
                        $localFileName = substr(
                            $realFileName,
                            strlen($baseDirectory)
                        );
                    }

                    $this->log(
                        'Adding '.$realFileName.' as '.$localFileName.' to package',
                        Project::MSG_VERBOSE
                    );

                    $phar->addFile($realFileName, $localFileName);
                }
            }

            $phar->stopBuffering();
        } catch (Exception $e) {
            throw new BuildException(
                'Problem creating package: '.$e->getMessage(),
                $e,
                $this->getLocation()
            );
        }
    }
    /**
     * @throws BuildException
     */
    private function checkPreconditions()
    {
        if (is_null($this->destinationFile)) {
            throw new BuildException("destfile attribute must be set!", $this->getLocation());
        }

        if ($this->destinationFile->exists() && $this->destinationFile->isDirectory()) {
            throw new BuildException("destfile is a directory!", $this->getLocation());
        }

        if (!$this->destinationFile->canWrite()) {
            throw new BuildException("Can not write to the specified destfile!", $this->getLocation());
        }
        if (!is_null($this->baseDirectory)) {
            if (!$this->baseDirectory->exists()) {
                throw new BuildException("basedir does not exist!", $this->getLocation());
            }
        }
    }
    /**
     * Build and configure Phar object.
     *
     * @return Phar
     */
    private function buildPhar()
    {
        $phar = new Phar($this->destinationFile);

        $phar->setSignatureAlgorithm($this->signatureAlgorithm);

        /*
         * File compression, if needed.
         */
        if (Phar::NONE != $this->compression) {
            $phar->compressFiles($this->compression);
        }

        $phar->setDefaultStub(
            $this->cliStubFile,
            $this->webStubFile
        );

        if ($metadata = $this->metadata->toArray()) {
            $phar->setMetadata($metadata);
        }

        return $phar;
    }
}
