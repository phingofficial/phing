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
 * Coverts a path to a fileset.
 * This is useful if you have a path but need to use a fileset as input in a phing task.
 *
 * Example
 * =======
 *
 * ```
 *   <path id="modified.sources.path" dir="C:\Path\to\phing\classes\phing\" />
 *   <pathtofileset name="modified.sources.fileset"
 *                  pathrefid="modified.sources.path"
 *                  dir="." />
 *
 *   <copy todir="C:\Path\to\phing\docs\api">
 *     <mapper type="glob" from="*.php" to="*.php.bak" />
 *     <fileset refid="modified.sources.fileset" />
 *   </copy>
 * ```
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class PathToFileSet extends Task
{
    /**
     * @var PhingFile $dir
     */
    private $dir;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $pathRefId
     */
    private $pathRefId;

    /**
     * @var bool $ignoreNonRelative
     */
    private $ignoreNonRelative = false;

    /**
     * @param PhingFile $dir
     *
     * @return void
     */
    public function setDir(PhingFile $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $pathRefId
     *
     * @return void
     */
    public function setPathRefId(string $pathRefId): void
    {
        $this->pathRefId = $pathRefId;
    }

    /**
     * @param bool $ignoreNonRelative
     *
     * @return void
     */
    public function setIgnoreNonRelative(bool $ignoreNonRelative): void
    {
        $this->ignoreNonRelative = $ignoreNonRelative;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function main(): void
    {
        if ($this->dir == null) {
            throw new BuildException('missing dir');
        }
        if ($this->name == null) {
            throw new BuildException('missing name');
        }
        if ($this->pathRefId == null) {
            throw new BuildException('missing pathrefid');
        }
        if (!$this->dir->isDirectory()) {
            throw new BuildException(
                (string) $this->dir . ' is not a directory'
            );
        }
        $path = $this->getProject()->getReference($this->pathRefId);
        if ($path == null) {
            throw new BuildException('Unknown reference ' . $this->pathRefId);
        }
        if (!($path instanceof Path)) {
            throw new BuildException($this->pathRefId . ' is not a path');
        }
        $sources = $path->listPaths();
        $fileSet = new FileSet();
        $fileSet->setProject($this->getProject());
        $fileSet->setDir($this->dir);
        $fileUtils = new FileUtils();
        $dirNormal = $fileUtils->normalize($this->dir->getAbsolutePath());
        $dirNormal = rtrim($dirNormal, FileUtils::$separator) . FileUtils::$separator;

        $atLeastOne = false;
        for ($i = 0, $resourcesCount = count($sources); $i < $resourcesCount; ++$i) {
            $sourceFile = new PhingFile($sources[$i]);
            if (!$sourceFile->exists()) {
                continue;
            }
            $includePattern = $this->getIncludePattern($dirNormal, $sourceFile);
            if ($includePattern === false && !$this->ignoreNonRelative) {
                throw new BuildException(
                    $sources[$i] . ' is not relative to ' . $this->dir->getAbsolutePath()
                );
            }
            if ($includePattern === false) {
                continue;
            }
            $fileSet->createInclude()->setName($includePattern);
            $atLeastOne = true;
        }
        if (!$atLeastOne) {
            $fileSet->createInclude()->setName('a:b:c:d//THis si &&& not a file !!! ');
        }
        $this->getProject()->addReference($this->name, $fileSet);
    }

    /**
     * @param string    $dirNormal
     * @param PhingFile $file
     *
     * @return string
     *
     * @throws IOException
     */
    private function getIncludePattern(string $dirNormal, PhingFile $file): string
    {
        $fileUtils  = new FileUtils();
        $fileNormal = $fileUtils->normalize($file->getAbsolutePath());

        return rtrim(str_replace('\\', '/', substr($fileNormal, strlen($dirNormal))), '/') . '/';
    }
}
