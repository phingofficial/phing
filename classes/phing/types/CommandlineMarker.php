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
 * Class to keep track of the position of an Argument.
 *
 * <p>This class is there to support the srcfile and targetfile
 * elements of &lt;execon&gt; and &lt;transform&gt; - don't know
 * whether there might be additional use cases.</p> --SB
 *
 * @package phing.types
 */
class CommandlineMarker
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var int
     */
    private $realPos = -1;

    /**
     * @var Commandline
     */
    private $outer;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @param Commandline $outer
     * @param int         $position
     */
    public function __construct(Commandline $outer, int $position)
    {
        $this->outer    = $outer;
        $this->position = $position;
    }

    /**
     * Return the number of arguments that preceded this marker.
     *
     * <p>The name of the executable - if set - is counted as the
     * very first argument.</p>
     *
     * @return int
     */
    public function getPosition(): int
    {
        if ($this->realPos === -1) {
            $this->realPos = ($this->outer->executable === null ? 0 : 1);
            for ($i = 0; $i < $this->position; $i++) {
                $arg            = $this->outer->arguments[$i];
                $this->realPos += count($arg->getParts());
            }
        }

        return $this->realPos;
    }

    /**
     * Set the prefix to be placed in front of the inserted argument.
     *
     * @param string $prefix fixed prefix string.
     *
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix ?? '';
    }

    /**
     * Get the prefix to be placed in front of the inserted argument.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set the suffix to be placed at the end of the inserted argument.
     *
     * @param string $suffix fixed suffix string.
     *
     * @return void
     */
    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix ?? '';
    }

    /**
     * Get the suffix to be placed at the end of the inserted argument.
     *
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }
}
