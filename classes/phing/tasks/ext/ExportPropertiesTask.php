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
 * Saves currently defined properties into a specified file
 *
 * @author  Andrei Serdeliuc
 * @extends Task
 * @package phing.tasks.ext
 */
class ExportPropertiesTask extends Task
{
    /**
     * Array of project properties
     *
     * (default value: null)
     *
     * @var array|null
     */
    private $properties = null;

    /**
     * Target file for saved properties
     *
     * (default value: null)
     *
     * @var string
     */
    private $targetFile = null;

    /**
     * Exclude properties starting with these prefixes
     *
     * @var array
     */
    private $disallowedPropertyPrefixes = [
        'host.',
        'phing.',
        'os.',
        'php.',
        'line.',
        'env.',
        'user.',
    ];

    /**
     * setter for _targetFile
     *
     * @param string $file
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setTargetFile(string $file): void
    {
        if (!is_dir(dirname($file))) {
            throw new BuildException("Parent directory of target file doesn't exist");
        }

        if (!is_writable(dirname($file)) && (file_exists($file) && !is_writable($file))) {
            throw new BuildException("Target file isn't writable");
        }

        $this->targetFile = $file;
    }

    /**
     * setter for _disallowedPropertyPrefixes
     *
     * @param string $prefixes
     *
     * @return void
     */
    public function setDisallowedPropertyPrefixes(string $prefixes): void
    {
        $this->disallowedPropertyPrefixes = explode(',', $prefixes);
    }

    /**
     * @return void
     */
    public function main(): void
    {
        // Sets the currently declared properties
        $this->properties = $this->getProject()->getProperties();

        if (is_array($this->properties) && !empty($this->properties) && null !== $this->targetFile) {
            $propertiesString = '';
            foreach ($this->properties as $propertyName => $propertyValue) {
                if (!$this->isDisallowedPropery($propertyName)) {
                    $propertiesString .= $propertyName . '=' . $propertyValue . PHP_EOL;
                }
            }

            if (!file_put_contents((string) $this->targetFile, $propertiesString)) {
                throw new BuildException('Failed writing to ' . $this->targetFile);
            }
        }
    }

    /**
     * Checks if a property name is disallowed
     *
     * @param string $propertyName
     *
     * @return bool
     */
    protected function isDisallowedPropery(string $propertyName): bool
    {
        foreach ($this->disallowedPropertyPrefixes as $property) {
            if (substr($propertyName, 0, strlen($property)) == $property) {
                return true;
            }
        }

        return false;
    }
}
