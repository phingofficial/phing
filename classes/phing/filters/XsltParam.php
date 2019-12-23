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
 * Class that holds an XSLT parameter.
 *
 * @package phing.filters
 */
class XsltParam
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var RegisterSlot|string
     */
    private $expr;

    /**
     * Sets param name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get param name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets expression value (alias to the setExpression()) method.
     *
     * @see   setExpression()
     *
     * @param string $v
     *
     * @return void
     */
    public function setValue(string $v): void
    {
        $this->setExpression($v);
    }

    /**
     * Gets expression value (alias to the getExpression()) method.
     *
     * @see    getExpression()
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->getExpression();
    }

    /**
     * Sets expression value.
     *
     * @param string $expr
     *
     * @return void
     */
    public function setExpression(string $expr): void
    {
        $this->expr = $expr;
    }

    /**
     * Sets expression to dynamic register slot.
     *
     * @param RegisterSlot $expr
     *
     * @return void
     */
    public function setListeningExpression(RegisterSlot $expr): void
    {
        $this->expr = $expr;
    }

    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     *
     * @return string
     */
    public function getExpression(): string
    {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        }

        return $this->expr;
    }
}
