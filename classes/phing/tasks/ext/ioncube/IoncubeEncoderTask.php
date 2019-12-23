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
 * Invokes the ionCube Encoder (PHP4 or PHP5)
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Andrew Eddie <andrew.eddie@jamboworks.com>
 * @author  Domenico Sgarbossa <sbraaaa@yahoo.it>
 * @package phing.tasks.ext.ioncube
 * @since   2.2.0
 */
class IoncubeEncoderTask extends Task
{
    /**
     * @var array
     */
    private $ionSwitches = [];

    /**
     * @var array
     */
    private $ionOptions = [];

    /**
     * @var array
     */
    private $ionOptionsXS = [];

    /**
     * @var IoncubeComment[]
     */
    private $comments = [];

    /**
     * @var string
     */
    private $encoderName = 'ioncube_encoder';

    /**
     * @var string
     */
    private $fromDir = '';

    /**
     * @var string
     */
    private $ioncubePath = '/usr/local/ioncube';

    /**
     * @var string
     */
    private $phpVersion = '5';

    /**
     * @var string
     */
    private $targetOption = '';

    /**
     * @var string
     */
    private $toDir = '';

    /**
     * @var bool
     */
    private $showCommandLine = false;

    /**
     * Sets whether to show command line before it is executed
     *
     * @param bool $value
     *
     * @return void
     */
    public function setShowCommandLine(bool $value): void
    {
        $this->showCommandLine = $value;
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
     * Sets the allowed server
     *
     * @param string $value
     *
     * @return void
     */
    public function setAllowedServer(string $value): void
    {
        $this->ionOptionsXS['allowed-server'] = $value;
    }

    /**
     * Returns the allowed server setting
     *
     * @return string
     */
    public function getAllowedServer(): string
    {
        return $this->ionOptionsXS['allowed-server'];
    }

    /**
     * Sets the binary option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setBinary(bool $value): void
    {
        $this->ionSwitches['binary'] = $value;
    }

    /**
     * Returns the binary option
     *
     * @return bool
     */
    public function getBinary(): bool
    {
        return $this->ionSwitches['binary'];
    }

    /**
     * Sets files or folders to copy (separated by space)
     *
     * @param string $value
     *
     * @return void
     */
    public function setCopy(string $value): void
    {
        $this->ionOptionsXS['copy'] = $value;
    }

    /**
     * Returns the copy setting
     *
     * @return string
     */
    public function getCopy(): string
    {
        return $this->ionOptionsXS['copy'];
    }

    /**
     * Sets additional file patterns, files or directories to encode,
     * or to reverse the effect of copy (separated by space)
     *
     * @param string $value
     *
     * @return void
     */
    public function setEncode(string $value): void
    {
        $this->ionOptionsXS['encode'] = $value;
    }

    /**
     * Returns the encode setting
     *
     * @return string
     */
    public function getEncode(): string
    {
        return $this->ionOptionsXS['encode'];
    }

    /**
     * Sets regexps of additional files to encrypt (separated by space)
     *
     * @param string $value
     *
     * @return void
     */
    public function setEncrypt(string $value): void
    {
        $this->ionOptionsXS['encrypt'] = $value;
    }

    /**
     * Returns regexps of additional files to encrypt (separated by space)
     *
     * @return string
     */
    public function getEncrypt(): string
    {
        return $this->ionOptionsXS['encrypt'];
    }

    /**
     * Sets a period after which the files expire
     *
     * @param string $value
     *
     * @return void
     */
    public function setExpirein(string $value): void
    {
        $this->ionOptions['expire-in'] = $value;
    }

    /**
     * Returns the expireIn setting
     *
     * @return string
     */
    public function getExpirein(): string
    {
        return $this->ionOptions['expire-in'];
    }

    /**
     * Sets a YYYY-MM-DD date to expire the files
     *
     * @param string $value
     *
     * @return void
     */
    public function setExpireon(string $value): void
    {
        $this->ionOptions['expire-on'] = $value;
    }

    /**
     * Returns the expireOn setting
     *
     * @return string
     */
    public function getExpireon(): string
    {
        return $this->ionOptions['expire-on'];
    }

    /**
     * Sets the source directory
     *
     * @param string $value
     *
     * @return void
     */
    public function setFromDir(string $value): void
    {
        $this->fromDir = $value;
    }

    /**
     * Returns the source directory
     *
     * @return string
     */
    public function getFromDir(): string
    {
        return $this->fromDir;
    }

    /**
     * Set files and directories to ignore entirely and exclude from the target directory
     * (separated by space).
     *
     * @param string $value
     *
     * @return void
     */
    public function setIgnore(string $value): void
    {
        $this->ionOptionsXS['ignore'] = $value;
    }

    /**
     * Returns the ignore setting
     *
     * @return string
     */
    public function getIgnore(): string
    {
        return $this->ionOptionsXS['ignore'];
    }

    /**
     * Sets the path to the ionCube encoder
     *
     * @param string $value
     *
     * @return void
     */
    public function setIoncubePath(string $value): void
    {
        $this->ioncubePath = $value;
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
     * Set files and directories not to be ignored (separated by space).
     *
     * @param string $value
     *
     * @return void
     */
    public function setKeep(string $value): void
    {
        $this->ionOptionsXS['keep'] = $value;
    }

    /**
     * Returns the ignore setting
     *
     * @return string
     */
    public function getKeep(): string
    {
        return $this->ionOptionsXS['keep'];
    }

    /**
     * Sets the path to the license file to use
     *
     * @param string $value
     *
     * @return void
     */
    public function setLicensePath(string $value): void
    {
        $this->ionOptions['with-license'] = $value;
    }

    /**
     * Returns the path to the license file to use
     *
     * @return string
     */
    public function getLicensePath(): string
    {
        return $this->ionOptions['with-license'];
    }

    /**
     * Sets the no-doc-comments option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setNoDocComments(bool $value): void
    {
        $this->ionSwitches['no-doc-comment'] = $value;
    }

    /**
     * Returns the no-doc-comments option
     *
     * @return bool
     */
    public function getNoDocComments(): bool
    {
        return $this->ionSwitches['no-doc-comment'];
    }

    /**
     * Sets the obfuscate option
     *
     * @param string $value
     *
     * @return void
     */
    public function setObfuscate(string $value): void
    {
        $this->ionOptionsXS['obfuscate'] = $value;
    }

    /**
     * Returns the optimize option
     *
     * @return string
     */
    public function getObfuscate(): string
    {
        return $this->ionOptionsXS['obfuscate'];
    }

    /**
     * Sets the obfuscation key (required if using the obfuscate option)
     *
     * @param string $value
     *
     * @return void
     */
    public function setObfuscationKey(string $value): void
    {
        $this->ionOptions['obfuscation-key'] = $value;
    }

    /**
     * Returns the optimize option
     *
     * @return string
     */
    public function getObfuscationKey(): string
    {
        return $this->ionOptions['obfuscation-key'];
    }

    /**
     * Sets the optimize option
     *
     * @param string $value
     *
     * @return void
     */
    public function setOptimize(string $value): void
    {
        $this->ionOptions['optimize'] = $value;
    }

    /**
     * Returns the optimize option
     *
     * @return string
     */
    public function getOptimize(): string
    {
        return $this->ionOptions['optimize'];
    }

    /**
     * Sets the passphrase to use when encoding files
     *
     * @param string $value
     *
     * @return void
     */
    public function setPassPhrase(string $value): void
    {
        $this->ionOptions['passphrase'] = $value;
    }

    /**
     * Returns the passphrase to use when encoding files
     *
     * @return string
     */
    public function getPassPhrase(): string
    {
        return $this->ionOptions['passphrase'];
    }

    /**
     * Sets the version of PHP to use (defaults to 5)
     *
     * @param string $value
     *
     * @return void
     */
    public function setPhpVersion(string $value): void
    {
        $this->phpVersion = $value;
    }

    /**
     * Returns the version of PHP to use (defaults to 5)
     *
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * Sets the target directory
     *
     * @param string $value
     *
     * @return void
     */
    public function setToDir(string $value): void
    {
        $this->toDir = $value;
    }

    /**
     * Returns the target directory
     *
     * @return string
     */
    public function getToDir(): string
    {
        return $this->toDir;
    }

    /**
     * Sets the without-runtime-loader-support option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setWithoutRuntimeLoaderSupport(bool $value): void
    {
        $this->ionSwitches['without-runtime-loader-support'] = $value;
    }

    /**
     * Returns the without-runtime-loader-support option
     *
     * @return bool
     */
    public function getWithoutRuntimeLoaderSupport(): bool
    {
        return $this->ionSwitches['without-runtime-loader-support'];
    }

    /**
     * Sets the no-short-open-tags option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setNoShortOpenTags(bool $value): void
    {
        $this->ionSwitches['no-short-open-tags'] = $value;
    }

    /**
     * Returns the no-short-open-tags option
     *
     * @return bool
     */
    public function getNoShortOpenTags(): bool
    {
        return $this->ionSwitches['no-short-open-tags'];
    }

    /**
     * Sets the ignore-deprecated-warnings option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setIgnoreDeprecatedWarnings(bool $value): void
    {
        $this->ionSwitches['ignore-deprecated-warnings'] = $value;
    }

    /**
     * Returns the ignore-deprecated-warnings option
     *
     * @return bool
     */
    public function getIgnoreDeprecatedWarnings(): bool
    {
        return $this->ionSwitches['ignore-deprecated-warnings'];
    }

    /**
     * Sets the ignore-strict-warnings option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setIgnoreStrictWarnings(bool $value): void
    {
        $this->ionSwitches['ignore-strict-warnings'] = $value;
    }

    /**
     * Returns the ignore-strict-warnings option
     *
     * @return bool
     */
    public function getIgnoreStrictWarnings(): bool
    {
        return $this->ionSwitches['ignore-strict-warnings'];
    }

    /**
     * Sets the allow-encoding-into-source option
     *
     * @param bool $value
     *
     * @return void
     */
    public function setAllowEncodingIntoSource(bool $value): void
    {
        $this->ionSwitches['allow-encoding-into-source'] = $value;
    }

    /**
     * Returns the allow-encoding-into-source option
     *
     * @return bool
     */
    public function getAllowEncodingIntoSource(): bool
    {
        return $this->ionSwitches['allow-encoding-into-source'];
    }

    /**
     * Sets the message-if-no-loader option
     *
     * @param string $value
     *
     * @return void
     */
    public function setMessageIfNoLoader(string $value): void
    {
        $this->ionOptions['message-if-no-loader'] = $value;
    }

    /**
     * Returns the message-if-no-loader option
     *
     * @return string
     */
    public function getMessageIfNoLoader(): string
    {
        return $this->ionOptions['message-if-no-loader'];
    }

    /**
     * Sets the action-if-no-loader option
     *
     * @param string $value
     *
     * @return void
     */
    public function setActionIfNoLoader(string $value): void
    {
        $this->ionOptions['action-if-no-loader'] = $value;
    }

    /**
     * Returns the action-if-no-loader option
     *
     * @return string
     */
    public function getActionIfNoLoader(): string
    {
        return $this->ionOptions['action-if-no-loader'];
    }

    /**
     * Sets the option to use when encoding target directory already exists (defaults to none)
     *
     * @param string $targetOption
     *
     * @return void
     */
    public function setTargetOption(string $targetOption): void
    {
        $this->targetOption = $targetOption;
    }

    /**
     * Returns the option to use when encoding target directory already exists (defaults to none)
     *
     * @return string
     */
    public function getTargetOption(): string
    {
        return $this->targetOption;
    }

    /**
     * Sets the callback-file option
     *
     * @param string $value
     *
     * @return void
     */
    public function setCallbackFile(string $value): void
    {
        $this->ionOptions['callback-file'] = $value;
    }

    /**
     * Returns the callback-file option
     *
     * @return string
     */
    public function getCallbackFile(): string
    {
        return $this->ionOptions['callback-file'];
    }

    /**
     * Sets the obfuscation-exclusions-file option
     *
     * @param string $value
     *
     * @return void
     */
    public function setObfuscationExclusionFile(string $value): void
    {
        $this->ionOptions['obfuscation-exclusion-file'] = $value;
    }

    /**
     * Returns the obfuscation-exclusions-file option
     *
     * @return string
     */
    public function getObfuscationExclusionFile(): string
    {
        return $this->ionOptions['obfuscation-exclusion-file'];
    }

    /**
     * The main entry point
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    public function main(): void
    {
        $arguments = $this->constructArguments();

        $encoder = new PhingFile($this->ioncubePath, $this->encoderName . $this->phpVersion);

        $this->log('Running ionCube Encoder...');

        if ($this->showCommandLine) {
            $this->log('Command line: ' . $encoder->__toString() . ' ' . $arguments);
        }

        exec($encoder->__toString() . ' ' . $arguments . ' 2>&1', $output, $return);

        if ($return != 0) {
            throw new BuildException('Could not execute ionCube Encoder: ' . implode(' ', $output));
        }
    }

    /**
     * Constructs an argument string for the ionCube encoder
     *
     * @return string
     */
    private function constructArguments(): string
    {
        $arguments = '';

        foreach ($this->ionSwitches as $name => $value) {
            if ($value) {
                $arguments .= sprintf('--%s ', $name);
            }
        }

        foreach ($this->ionOptions as $name => $value) {
            /**
             * action-if-no-loader value is a php source snippet so it is
             * better to handle it this way to prevent quote problems!
             */
            if ($name == 'action-if-no-loader') {
                $arguments .= sprintf('--%s "%s" ', $name, $value);
            } else {
                $arguments .= sprintf("--%s '%s' ", $name, $value);
            }
        }

        foreach ($this->ionOptionsXS as $name => $value) {
            foreach (explode(' ', $value) as $arg) {
                $arguments .= sprintf("--%s '%s' ", $name, $arg);
            }
        }

        foreach ($this->comments as $comment) {
            $arguments .= "--add-comment '" . $comment->getValue() . "' ";
        }

        if (!empty($this->targetOption)) {
            switch ($this->targetOption) {
                case 'replace':
                case 'merge':
                case 'update':
                case 'rename':
                    $arguments .= '--' . $this->targetOption . '-target ';
                    break;
                default:
                    throw new BuildException("Unknown target option '" . $this->targetOption . "'");
            }
        }

        if ($this->fromDir != '') {
            $arguments .= $this->fromDir . ' ';
        }

        if ($this->toDir != '') {
            $arguments .= '-o ' . $this->toDir . ' ';
        }

        return $arguments;
    }
}
