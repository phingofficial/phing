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
 * FileSizeTask
 *
 * Returns the size of a file
 *
 * @author  Johan Persson <johan162@gmail.com>
 * @package phing.tasks.ext
 */
class FileSizeTask extends Task
{
    const UNITS = ['B', 'K', 'M', 'G', 'T', 'P'];

    /**
     * Property for File
     *
     * @var PhingFile file
     */
    private $file;

    /**
     * Property where the file size will be stored
     *
     * @var string
     */
    private $propertyName = "filesize";

    /**
     * Return size in this unit
     *
     * @var string
     */
    private $unit = self::UNITS[0];

    /**
     * Which file to calculate the file size of
     *
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        if (!$file->canRead()) {
            throw new BuildException(sprintf('Input file does not exist or is not readable: %s', $file->getName()));
        }
        $this->file = $file;
    }

    /**
     * Set the name of the property to store the file size
     *
     * @param  $property
     * @return void
     */
    public function setPropertyName(string $property)
    {
        if (empty($property)) {
            throw new BuildException('Property name cannot be empty');
        }
        $this->propertyName = $property;
    }

    public function setUnit(string $originalUnit)
    {
        $unit = strtoupper($originalUnit);
        if (!in_array($unit, self::UNITS)) {
            throw new BuildException(sprintf('Invalid unit: %s', $originalUnit));
        }
        $this->unit = $unit;
    }

    /**
     * Main-Method for the Task
     *
     * @return void
     * @throws BuildException
     */
    public function main()
    {
        if (!($this->file instanceof PhingFile)) {
            throw new BuildException('Input file not specified');
        }

        $size = filesize($this->file);

        if ($size === false) {
            throw new BuildException(sprintf('Cannot determine size of file: %s', $this->file));
        }
        $this->log(sprintf('%s %s', $size, self::UNITS[0]), Project::MSG_VERBOSE);

        $size = $size / pow(1024, array_search($this->unit, self::UNITS, true));

        if (is_float($size)) {
            $size = round($size, 2);
        }
        $this->log(sprintf('%s %s', $size, $this->unit), Project::MSG_INFO);

        // publish hash value
        $this->project->setProperty($this->propertyName, $size);
    }
}
