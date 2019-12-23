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
class TimerMap
{
    /**
     * @var SeriesTimer[]
     */
    protected $map = [];

    /**
     * @param mixed $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->map[$name];
    }

    /**
     * @param mixed $name
     * @param Clock $clock
     *
     * @return StatsTimer
     */
    public function find($name, Clock $clock): StatsTimer
    {
        $timer = $this->map[$name] ?? null;
        if ($timer === null) {
            $timer            = $this->createTimer($name, $clock);
            $this->map[$name] = $timer;
        }

        return $timer;
    }

    /**
     * @param mixed $name
     * @param Clock $clock
     *
     * @return StatsTimer
     */
    protected function createTimer($name, Clock $clock): StatsTimer
    {
        return new StatsTimer($name, $clock);
    }

    /**
     * @return SeriesMap
     */
    public function toSeriesMap(): SeriesMap
    {
        $seriesMap = new SeriesMap();
        foreach ($this->map as $key => $timer) {
            $seriesMap->put($key, $timer->getSeries());
        }

        return $seriesMap;
    }
}
