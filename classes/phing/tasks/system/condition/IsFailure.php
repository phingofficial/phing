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
 * Condition to test a return-code for failure.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class IsFailure implements Condition
{
    /**
     * @var int $code
     */
    private $code;

    /**
     * Set the return code to check.
     *
     * @param int|string $c the return code.
     *
     * @return void
     */
    public function setCode($c): void
    {
        $this->code = (int) $c;
    }

    /**
     * Get the return code that will be checked by this IsFailure condition.
     *
     * @return int return code as int.
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Checks whether exitValue signals a failure on the current system.
     *
     * @param int $code
     *
     * @return bool
     */
    protected function isFailureCode(int $code): bool
    {
        return $code !== 0;
    }

    /**
     * Fulfill the condition interface.
     *
     * @return bool the result of evaluating the specified return code.
     */
    public function evaluate(): bool
    {
        return $this->isFailureCode($this->code);
    }
}
