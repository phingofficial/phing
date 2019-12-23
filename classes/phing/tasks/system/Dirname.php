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
 * Determines the directory name of the specified file.
 *
 * This task can accept the following attributes:
 * <ul>
 * <li>file
 * <li>property
 * </ul>
 * Both <b>file</b> and <b>property</b> are required.
 * <p>
 * When this task executes, it will set the specified property to the
 * value of the specified file up to, but not including, the last path
 * element. If file is a file, the directory will be the current
 * directory.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class Dirname extends Task
{
    /**
     * @var PhingFile $file
     */
    private $file;

    /**
     * @var string
     */
    private $property;

    /**
     * Path to take the dirname of.
     *
     * @param string|PhingFile $file a <code>File</code> value
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setFile($file): void
    {
        if ($file instanceof PhingFile) {
            $this->file = $file;
        } else {
            $this->file = new PhingFile($file);
        }
    }

    /**
     * The name of the property to set.
     *
     * @param string $property the name of the property
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Execute this task.
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function main(): void
    {
        if ($this->property == null) {
            throw new BuildException('property attribute required', $this->getLocation());
        }
        if ($this->file == null) {
            throw new BuildException('file attribute required', $this->getLocation());
        }

        $value = $this->file->getAbsoluteFile()->getParent();
        $this->getProject()->setNewProperty($this->property, $value);
    }
}
