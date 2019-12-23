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
 * Task for resolving relative paths and setting absolute path in property value.
 *
 * This task was created to address a need for resolving absolute paths of files / directories.
 * In many cases a relative directory (e.g. "./build") is specified, but it needs to be treated
 * as an absolute path since other build files (e.g. in subdirs) should all be using the same
 * path -- and not treating it as a relative path to their own directory.
 *
 * <code>
 * <property name="relative_path" value="./dirname"/>
 * <resolvepath propertyName="absolute_path" file="${relative_path}"/>
 * <echo>Resolved [absolute] path: ${absolute_path}</echo>
 * </code>
 *
 * TODO:
 *      - Possibly integrate this with PackageAsPath, for handling/resolving dot-path paths.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.system
 */
class ResolvePathTask extends Task
{
    use LogLevelAware;

    /**
     * Name of property to set.
     *
     * @var string
     */
    private $propertyName;

    /**
     * The [possibly] relative file/path that needs to be resolved.
     *
     * @var string|PhingFile|null
     */
    private $file;

    /**
     * Base directory used for resolution.
     *
     * @var PhingFile
     */
    private $dir;

    /**
     * Set the name of the property to set.
     *
     * @param string $v Property name
     *
     * @return void
     */
    public function setPropertyName(string $v): void
    {
        $this->propertyName = $v;
    }

    /**
     * Sets a base dir to use for resolution.
     *
     * @param PhingFile $d
     *
     * @return void
     */
    public function setDir(PhingFile $d): void
    {
        $this->dir = $d;
    }

    /**
     * Sets a path (file or directory) that we want to resolve.
     * This is the same as setFile() -- just more generic name so that it's
     * clear that you can also use it to set directory.
     *
     * @see   setFile()
     *
     * @param string $f
     *
     * @return void
     */
    public function setPath(string $f): void
    {
        $this->file = $f;
    }

    /**
     * Sets a file that we want to resolve.
     *
     * @param string $f
     *
     * @return void
     */
    public function setFile(string $f): void
    {
        $this->file = $f;
    }

    /**
     * Perform the resolution & set property.
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws Exception
     */
    public function main(): void
    {
        if (!$this->propertyName) {
            throw new BuildException('You must specify the propertyName attribute', $this->getLocation());
        }

        // Currently only files are supported
        if ($this->file === null) {
            throw new BuildException('You must specify a path to resolve', $this->getLocation());
        }

        $fs = FileSystem::getFileSystem();

        // if dir attribute was specified then we should
        // use that as basedir to which file was relative.
        // -- unless the file specified is an absolute path
        if ($this->dir !== null && !$fs->isAbsolute(new PhingFile($this->file))) {
            $this->file = new PhingFile($this->dir->getPath(), $this->file);
        }

        $resolved = $this->project->resolveFile((string) $this->file);

        $this->log('Resolved ' . $this->file . ' to ' . $resolved->getAbsolutePath(), $this->logLevel);
        $this->project->setProperty($this->propertyName, $resolved->getAbsolutePath());
    }
}
