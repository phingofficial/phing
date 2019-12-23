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
 * Task that changes the permissions on a file/directory.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class Basename extends Task
{
    /**
     * @var PhingFile $file
     */
    private $file;

    /**
     * @var string $property
     */
    private $property;

    /**
     * @var string $suffix
     */
    private $suffix;

    /**
     * file or directory to get base name from
     *
     * @param PhingFile $file file or directory to get base name from
     *
     * @return void
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
    }

    /**
     * Property to set base name to.
     *
     * @param string $property name of property
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Optional suffix to remove from base name.
     *
     * @param string $suffix suffix to remove from base name
     *
     * @return void
     */
    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * do the work
     *
     * @return void
     *
     * @throws BuildException if required attributes are not supplied
     *                        property and attribute are required attributes
     */
    public function main(): void
    {
        if ($this->property === null) {
            throw new BuildException('property attribute required', $this->getLocation());
        }

        if ($this->file === null) {
            throw new BuildException('file attribute required', $this->getLocation());
        }

        $this->getProject()->setNewProperty(
            $this->property,
            $this->removeExtension($this->file->getName(), $this->suffix)
        );
    }

    /**
     * @param string|null $s
     * @param string|null $ext
     *
     * @return string|null
     */
    private function removeExtension(?string $s, ?string $ext): ?string
    {
        if ($ext === null || !StringHelper::endsWith($ext, $s)) {
            return $s;
        }

        return rtrim(substr($s, 0, -strlen($ext)), '.');
    }
}
