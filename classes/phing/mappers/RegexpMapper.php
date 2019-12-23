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
 * Uses regular expressions to perform filename transformations.
 *
 * @author Andreas Aderhold <andi@binarycloud.com>
 * @author Hans Lellelid <hans@velum.net>
 * @package phing.mappers
 */
class RegexpMapper implements FileNameMapper
{
    /**
     * @var string $to
     */
    private $to;

    /**
     * The Regexp engine.
     *
     * @var Regexp $reg
     */
    private $reg;

    /**
     * @var bool
     */
    private $handleDirSep = false;

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * Instantiage regexp matcher here.
     */
    public function __construct()
    {
        $this->reg = new Regexp();
        $this->reg->setIgnoreCase(!$this->caseSensitive);
    }

    /**
     * Attribute specifying whether to ignore the difference
     * between / and \ (the two common directory characters).
     *
     * @param bool $handleDirSep a boolean, default is false.
     *
     * @return void
     */
    public function setHandleDirSep(bool $handleDirSep): void
    {
        $this->handleDirSep = $handleDirSep;
    }

    /**
     * Attribute specifying whether to ignore the difference
     * between / and \ (the two common directory characters).
     *
     * @return bool
     */
    public function getHandleDirSep(): bool
    {
        return $this->handleDirSep;
    }

    /**
     * Attribute specifying whether to ignore the case difference
     * in the names.
     *
     * @param bool $caseSensitive a boolean, default is false.
     *
     * @return void
     */
    public function setCaseSensitive(bool $caseSensitive): void
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Sets the &quot;from&quot; pattern. Required.
     * {@inheritdoc}
     *
     * @param string|null $from
     *
     * @return void
     */
    public function setFrom(?string $from): void
    {
        if ($from === null) {
            throw new BuildException("this mapper requires a 'from' attribute");
        }

        $this->reg->setPattern($from);
    }

    /**
     * Sets the &quot;to&quot; pattern. Required.
     *
     * {@inheritdoc}
     *
     * @param string|null $to
     *
     * @return void
     *
     * @intern [HL] I'm changing the way this works for now to just use string
     *              <code>$this->to = StringHelper::toCharArray($to);</code>
     */
    public function setTo(?string $to): void
    {
        if ($to === null) {
            throw new BuildException("this mapper requires a 'to' attribute");
        }

        $this->to = $to;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sourceFileName
     *
     * @return array|null
     *
     * @throws RegexpException
     */
    public function main(string $sourceFileName): ?array
    {
        if ($this->handleDirSep) {
            if (strpos('\\', $sourceFileName) !== false) {
                $sourceFileName = str_replace('\\', '/', $sourceFileName);
            }
        }
        if ($this->reg === null || $this->to === null || !$this->reg->matches((string) $sourceFileName)) {
            return null;
        }

        return [$this->replaceReferences($sourceFileName)];
    }

    /**
     * Replace all backreferences in the to pattern with the matched groups.
     * groups of the source.
     *
     * @param string $source The source filename.
     *
     * @return array|string|null
     *
     * FIXME Can't we just use engine->replace() to handle this?  the Preg engine will automatically convert \1 references to $1
     *
     * @intern the expression has already been processed (when ->matches() was run in Main())
     *         so no need to pass $source again to the engine.
     *         Replaces \1 with value of reg->getGroup(1) and return the modified "to" string.
     */
    private function replaceReferences(string $source)
    {
        return preg_replace_callback('/\\\([\d]+)/', [$this, 'replaceReferencesCallback'], $this->to);
    }

    /**
     * Gets the matched group from the Regexp engine.
     *
     * @param array $matches Matched elements.
     *
     * @return string
     */
    private function replaceReferencesCallback(array $matches): string
    {
        return (string) $this->reg->getGroup($matches[1]);
    }
}
