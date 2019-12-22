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
 * fileHash
 *
 * Calculate either MD5 or SHA hash value of a specified file and retun the
 * value in a property
 *
 * @author  Johan Persson <johan162@gmail.com>
 * @package phing.tasks.ext
 */
class FileHashTask extends Task
{
    /**
     * Property for File
     *
     * @var PhingFile file
     */
    private $file;

    /**
     * Property to be set
     *
     * @var string $property
     */
    private $propertyName = 'filehashvalue';

    /**
     * Specify which hash algorithm to use.
     *   0 = MD5
     *   1 = SHA1
     *
     * @var int $hashtype
     */
    private $hashtype = 0;

    /** @var string $algorithm */
    private $algorithm = '';

    /**
     * Specify if MD5 or SHA1 hash should be used
     *
     * @param int $type 0=MD5, 1=SHA1
     */
    public function setHashtype($type): void
    {
        $this->hashtype = $type;
    }

    public function setAlgorithm($type): void
    {
        $this->algorithm = strtolower($type);
    }

    /**
     * Which file to calculate the hash value of
     *
     * @param PhingFile $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * Set the name of the property to store the hash value in
     *
     * @param string $property
     *
     * @return void
     */
    public function setPropertyName($property): void
    {
        $this->propertyName = $property;
    }

    /**
     * Main-Method for the Task
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->checkFile();
        $this->checkPropertyName();

        // read file
        if ($this->algorithm !== '' && in_array($this->algorithm, hash_algos())) {
            $this->log('Calculating ' . $this->algorithm . ' hash from: ' . $this->file);
            $hashValue = hash_file($this->algorithm, $this->file);
        } elseif ((int) $this->hashtype === 0) {
            $this->log('Calculating MD5 hash from: ' . $this->file);
            $hashValue       = md5_file($this->file, false);
            $this->algorithm = 'md5';
        } elseif ((int) $this->hashtype === 1) {
            $this->log('Calculating SHA1 hash from: ' . $this->file);
            $hashValue       = sha1_file($this->file, false);
            $this->algorithm = 'sha1';
        } else {
            if ($this->algorithm !== '') {
                throw new BuildException(
                    sprintf(
                        '[FileHash] Unknown algorithm specified %d. Must be one of %s',
                        $this->algorithm,
                        implode(', ', hash_algos())
                    )
                );
            }

            throw new BuildException(
                sprintf(
                    '[FileHash] Unknown hashtype specified %d. Must be either 0 (=MD5) or 1 (=SHA1)',
                    $this->hashtype
                )
            );
        }

        // publish hash value
        $this->project->setProperty($this->propertyName, $hashValue);
        $fos = new FileOutputStream($this->file . '.' . $this->algorithm);
        $fos->write(sprintf('%s  %s', $hashValue, basename($this->file)));
    }

    /**
     * checks file attribute
     *
     * @throws BuildException
     */
    private function checkFile(): void
    {
        // check File
        if ($this->file === null || $this->file === '') {
            throw new BuildException('[FileHash] You must specify an input file.', $this->file);
        }

        if (!is_readable($this->file)) {
            throw new BuildException(
                sprintf(
                    '[FileHash] Input file does not exist or is not readable: %s',
                    $this->file
                )
            );
        }
    }

    /**
     * checks property attribute
     *
     * @throws BuildException
     */
    private function checkPropertyName(): void
    {
        if (null === $this->propertyName || $this->propertyName === '') {
            throw new BuildException('Property name for publishing hashvalue is not set');
        }
    }
}
