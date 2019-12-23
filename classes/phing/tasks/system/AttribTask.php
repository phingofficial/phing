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
 * Changes the attributes of a file or all files inside specified directories.
 * Right now it has effect only under Windows. Each of the 4 possible
 * permissions has its own attribute, matching the arguments for the `attrib`
 * command.
 *
 * Example:
 * ```
 *    <attrib file="${input}" readonly="true" hidden="true" verbose="true"/>
 * ```
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class AttribTask extends ApplyTask
{
    private static $ATTR_READONLY = 'R';
    private static $ATTR_ARCHIVE  = 'A';
    private static $ATTR_SYSTEM   = 'S';
    private static $ATTR_HIDDEN   = 'H';
    private static $SET           = '+';
    private static $UNSET         = '-';

    private $attr = false;

    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();
        parent::setExecutable('attrib');
        parent::setParallel(false);
    }

    /**
     * @return void
     *
     * @throws BuildException
     * @throws ReflectionException
     */
    public function main(): void
    {
        $this->checkConfiguration();
        parent::main();
    }

    /**
     * @param bool $b
     *
     * @return void
     */
    public function setVerbose(bool $b): void
    {
        $this->loglevel = Project::MSG_VERBOSE;
    }

    /**
     * A file to be attribed.
     *
     * @param PhingFile $src a file
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setFile(PhingFile $src): void
    {
        $fs = new FileSet();
        $fs->setFile($src);
        $this->addFileSet($fs);
    }

    /**
     * Set the ReadOnly file attribute.
     *
     * @param bool $value
     *
     * @return void
     */
    public function setReadonly(bool $value): void
    {
        $this->addArg($value, self::$ATTR_READONLY);
    }

    /**
     * Set the Archive file attribute.
     *
     * @param bool $value
     *
     * @return void
     */
    public function setArchive(bool $value): void
    {
        $this->addArg($value, self::$ATTR_ARCHIVE);
    }

    /**
     * Set the System file attribute.
     *
     * @param bool $value
     *
     * @return void
     */
    public function setSystem(bool $value): void
    {
        $this->addArg($value, self::$ATTR_SYSTEM);
    }

    /**
     * Set the Hidden file attribute.
     *
     * @param bool $value
     *
     * @return void
     */
    public function setHidden(bool $value): void
    {
        $this->addArg($value, self::$ATTR_HIDDEN);
    }

    /**
     * Check the attributes.
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function checkConfiguration(): void
    {
        if (!$this->hasAttr()) {
            throw new BuildException(
                'Missing attribute parameter',
                $this->getLocation()
            );
        }
    }

    /**
     * Set the executable.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param mixed $e
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setExecutable($e): void
    {
        throw new BuildException(
            $this->getTaskType() . ' doesn\'t support the executable attribute',
            $this->getLocation()
        );
    }

    /**
     * Add source file.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param bool $b ignored
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setAddsourcefile(bool $b): void
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the addsourcefile attribute',
            $this->getLocation()
        );
    }

    /**
     * Set max parallel.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param int $max ignored
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setMaxParallel(int $max): void
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the maxparallel attribute',
            $this->getLocation()
        );
    }

    /**
     * Set parallel.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param bool $parallel ignored
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setParallel(bool $parallel): void
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the parallel attribute',
            $this->getLocation()
        );
    }

    /**
     * @param bool $attr
     *
     * @return string
     */
    private static function getSignString(bool $attr): string
    {
        return $attr ? self::$SET : self::$UNSET;
    }

    /**
     * @param bool   $sign
     * @param string $attribute
     *
     * @return void
     */
    private function addArg(bool $sign, string $attribute): void
    {
        $this->createArg()->setValue(self::getSignString($sign) . $attribute);
        $this->attr = true;
    }

    /**
     * @return bool
     */
    private function hasAttr(): bool
    {
        return $this->attr;
    }
}
