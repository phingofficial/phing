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
 * Invokes the ionCube "make_license" program
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.ioncube
 * @since   2.2.0
 */
class IoncubeLicenseTask extends Task
{
    /**
     * @var string
     */
    private $ioncubePath = '/usr/local/ioncube';

    /**
     * @var string
     */
    private $licensePath = '';

    /**
     * @var string
     */
    private $passPhrase = '';

    /**
     * @var string
     */
    private $allowedServer = '';

    /**
     * @var string
     */
    private $expireOn = '';

    /**
     * @var string
     */
    private $expireIn = '';

    /**
     * @var IoncubeComment[]
     */
    private $comments = [];

    /**
     * Sets the path to the ionCube encoder
     *
     * @param string $ioncubePath
     *
     * @return void
     */
    public function setIoncubePath(string $ioncubePath): void
    {
        $this->ioncubePath = $ioncubePath;
    }

    /**
     * Returns the path to the ionCube encoder
     *
     * @return string
     */
    public function getIoncubePath(): string
    {
        return $this->ioncubePath;
    }

    /**
     * Sets the path to the license file to use
     *
     * @param string $licensePath
     *
     * @return void
     */
    public function setLicensePath(string $licensePath): void
    {
        $this->licensePath = $licensePath;
    }

    /**
     * Returns the path to the license file to use
     *
     * @return string
     */
    public function getLicensePath(): string
    {
        return $this->licensePath;
    }

    /**
     * Sets the passphrase to use when encoding files
     *
     * @param string $passPhrase
     *
     * @return void
     */
    public function setPassPhrase(string $passPhrase): void
    {
        $this->passPhrase = $passPhrase;
    }

    /**
     * Returns the passphrase to use when encoding files
     *
     * @return string
     */
    public function getPassPhrase(): string
    {
        return $this->passPhrase;
    }

    /**
     * Adds a comment to be used in encoded files
     *
     * @param IoncubeComment $comment
     *
     * @return void
     */
    public function addComment(IoncubeComment $comment): void
    {
        $this->comments[] = $comment;
    }

    /**
     * Sets the --allowed-server option to use when generating the license
     *
     * @param string $allowedServer
     *
     * @return void
     */
    public function setAllowedServer(string $allowedServer): void
    {
        $this->allowedServer = $allowedServer;
    }

    /**
     * Returns the --allowed-server option
     *
     * @return string
     */
    public function getAllowedServer(): string
    {
        return $this->allowedServer;
    }

    /**
     * Sets the --expire-on option to use when generating the license
     *
     * @param string $expireOn
     *
     * @return void
     */
    public function setExpireOn(string $expireOn): void
    {
        $this->expireOn = $expireOn;
    }

    /**
     * Returns the --expire-on option
     *
     * @return string
     */
    public function getExpireOn(): string
    {
        return $this->expireOn;
    }

    /**
     * Sets the --expire-in option to use when generating the license
     *
     * @param string $expireIn
     *
     * @return void
     */
    public function setExpireIn(string $expireIn): void
    {
        $this->expireIn = $expireIn;
    }

    /**
     * Returns the --expire-in option
     *
     * @return string
     */
    public function getExpireIn(): string
    {
        return $this->expireIn;
    }

    /**
     * The main entry point
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function main(): void
    {
        $arguments = $this->constructArguments();

        $makelicense = new PhingFile($this->ioncubePath, 'make_license');

        $this->log('Running ionCube make_license...');

        exec($makelicense->__toString() . ' ' . $arguments . ' 2>&1', $output, $return);

        if ($return != 0) {
            throw new BuildException('Could not execute ionCube make_license: ' . implode(' ', $output));
        }
    }

    /**
     * Constructs an argument string for the ionCube make_license
     *
     * @return string
     */
    private function constructArguments(): string
    {
        $arguments = '';

        if (!empty($this->passPhrase)) {
            $arguments .= "--passphrase '" . $this->passPhrase . "' ";
        }

        foreach ($this->comments as $comment) {
            $arguments .= "--header-line '" . $comment->getValue() . "' ";
        }

        if (!empty($this->licensePath)) {
            $arguments .= "--o '" . $this->licensePath . "' ";
        }

        if (!empty($this->allowedServer)) {
            $arguments .= '--allowed-server {' . $this->allowedServer . '} ';
        }

        if (!empty($this->expireOn)) {
            $arguments .= '--expire-on ' . $this->expireOn . ' ';
        }

        if (!empty($this->expireIn)) {
            $arguments .= '--expire-in ' . $this->expireIn . ' ';
        }

        return $arguments;
    }
}
