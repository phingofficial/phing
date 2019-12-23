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
 * A simple string comparator.  Compares two strings for eqiality in a
 * binary safe manner. Implements the condition interface specification.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @package   phing.tasks.system.condition
 */
class EqualsCondition implements Condition
{
    /**
     * @var string
     */
    private $arg1;

    /**
     * @var string
     */
    private $arg2;

    /**
     * @var bool
     */
    private $trim = false;

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * @param string $a1
     *
     * @return void
     */
    public function setArg1(string $a1): void
    {
        $this->arg1 = $a1;
    }

    /**
     * @param string $a2
     *
     * @return void
     */
    public function setArg2(string $a2): void
    {
        $this->arg2 = $a2;
    }

    /**
     * Should we want to trim the arguments before comparing them?
     *
     * @param bool $b
     *
     * @return void
     */
    public function setTrim(bool $b): void
    {
        $this->trim = (bool) $b;
    }

    /**
     * Should the comparison be case sensitive?
     *
     * @param bool $b
     *
     * @return void
     */
    public function setCaseSensitive(bool $b): void
    {
        $this->caseSensitive = (bool) $b;
    }

    /**
     * @return bool
     *
     * @throws BuildException
     */
    public function evaluate(): bool
    {
        if ($this->arg1 === null || $this->arg2 === null) {
            throw new BuildException('Both arg1 and arg2 are required in equals.');
        }

        if ($this->trim) {
            $this->arg1 = trim($this->arg1);
            $this->arg2 = trim($this->arg2);
        }

        //print("[comparison] Comparing '".$this->arg1."' and '".$this->arg2."'\n");
        return $this->caseSensitive ? $this->arg1 === $this->arg2 : strtolower($this->arg1) === strtolower($this->arg2);
    }
}
