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
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.listener.statistics
 */
class Series
{
    /**
     * @var SplStack
     */
    private $stack;

    /**
     * @var Duration[] $list
     */
    private $list = [];

    public function __construct()
    {
        $this->stack = new SplStack();
    }

    /**
     * @param Duration $duration
     *
     * @return void
     */
    public function add(Duration $duration): void
    {
        $this->list[] = $duration;
        $this->stack->push($duration);
    }

    /**
     * @param int|float $time
     *
     * @return void
     */
    public function setFinishTime($time): void
    {
        /** @var Duration $duration */
        $duration = $this->stack->pop();
        $duration->setFinishTime($time);
    }

    /**
     * @return <int|float>[]
     */
    public function getTimes(): array
    {
        return array_map(
            static function (Duration $elem) {
                return $elem->getTime();
            },
            $this->list
        );
    }

    /**
     * @return float|int
     */
    public function getTotalTime()
    {
        return array_sum($this->getTimes());
    }

    /**
     * @return float|int
     */
    public function getAverageTime()
    {
        if (count($this->list) === 0) {
            return 0;
        }
        return $this->getTotalTime() / count($this->list);
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return count($this->list);
    }

    /**
     * @return Duration
     */
    public function current(): Duration
    {
        if ($this->stack->isEmpty()) {
            $this->stack->push(new Duration());
        }
        return $this->stack->top();
    }
}
