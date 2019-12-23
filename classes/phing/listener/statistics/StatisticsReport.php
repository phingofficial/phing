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
class StatisticsReport
{
    private static $IDX_NAME = 0;

    private static $IDX_COUNT = 1;

    private static $IDX_AVERAGE = 2;

    private static $IDX_TOTAL = 3;

    private static $IDX_PERCENTAGE = 4;

    private static $HEADERS = [
        'name',
        'count',
        'average',
        'total',
        '%',
    ];

    /**
     * @var TimeFormatter
     */
    private static $TIME_FORMATTER;

    /**
     * @var StringFormatter
     */
    private static $FORMATTER;

    /**
     * @var SplStack
     */
    private $stack;

    public function __construct()
    {
        self::$TIME_FORMATTER = new TimeFormatter();
        self::$FORMATTER      = new StringFormatter();
        $this->stack          = new SplStack();
    }

    /**
     * @param string    $title
     * @param SeriesMap $seriesMap
     *
     * @return string
     */
    public function create(string $title, SeriesMap $seriesMap): string
    {
        $keys = $seriesMap->getNames();
        sort($keys);
        $table = new Table(self::$HEADERS, count($keys));

        $totalTimes       = [];
        $runningTotalTime = 0;
        for ($i = 1; $i < $table->rows(); $i++) {
            $series = $seriesMap->get($keys[$i - 1]);
            $table->put($i, self::$IDX_NAME, $keys[$i - 1]);
            $table->put($i, self::$IDX_COUNT, $series->size());
            $table->put($i, self::$IDX_AVERAGE, self::$TIME_FORMATTER->format($series->getAverageTime()));
            $table->put($i, self::$IDX_TOTAL, self::$TIME_FORMATTER->format($series->getTotalTime()));
            $totalTimes[$i - 1] = $series->getTotalTime();
            $runningTotalTime  += $series->getTotalTime();
        }

        $this->updateTableWithPercentagesOfTotalTime($table, $totalTimes, $runningTotalTime);

        return $this->toString($title, $table);
    }

    /**
     * @param Table     $table
     * @param array     $totalTimes
     * @param float|int $runningTotalTime
     *
     * @return void
     */
    private function updateTableWithPercentagesOfTotalTime(Table $table, array $totalTimes, $runningTotalTime): void
    {
        $total = count($totalTimes);
        for ($i = 0; $i < $total; $i++) {
            $totalTime = $totalTimes[$i];
            $round     = round(100 * (float) $totalTime / $runningTotalTime);
            $table->put($i + 1, self::$IDX_PERCENTAGE, (string) $round);
        }
    }

    /**
     * @param string $title
     * @param Table  $table
     *
     * @return string
     */
    private function toString(string $title, Table $table): string
    {
        $sb             = '';
        $maxLengths     = $table->getMaxLengths();
        $titleBarLength = $this->calculateFixedLength($maxLengths);
        $sb            .= self::$FORMATTER->center($title, $titleBarLength);
        $sb            .= PHP_EOL . PHP_EOL;

        for ($i = 0; $i < $table->rows(); $i++) {
            for ($j = 0; $j < $table->columns(); $j++) {
                $sb .= self::$FORMATTER->left((string) $table->get($i, $j), $maxLengths[$j]);
            }
            $sb .= PHP_EOL;
            $sb .= $this->createTitleBarIfFirstRow($titleBarLength, $i);
        }

        $sb .= PHP_EOL;
        return $sb;
    }

    /**
     * @param int $titleBarLength
     * @param int $i
     *
     * @return string
     */
    private function createTitleBarIfFirstRow(int $titleBarLength, int $i): string
    {
        if ($i !== 0) {
            return '';
        }
        return self::$FORMATTER->toChars('-', $titleBarLength) . PHP_EOL;
    }

    /**
     * @param array $maxLengths
     *
     * @return int
     */
    private function calculateFixedLength(array $maxLengths): int
    {
        $fixedLength = 0;
        $total       = count($maxLengths);
        for ($i = 0; $i < $total; $i++) {
            $fixedLength += $maxLengths[$i] + 4;
        }
        return $fixedLength;
    }

    /**
     * @param StatsTimer $projectTimer
     *
     * @return void
     */
    public function push(StatsTimer $projectTimer): void
    {
        $this->stack->push($projectTimer);
    }

    /**
     * @param ProjectTimer|null $projectTimer
     *
     * @return void
     */
    public function write(?ProjectTimer $projectTimer = null): void
    {
        if ($projectTimer !== null) {
            $this->create('Target Statistics', $projectTimer->toTargetSeriesMap());
            $this->create('Task Statistics', $projectTimer->toTaskSeriesMap());
        } else {
            $projectSeriesMap = new SeriesMap();
            $sb               = '';
            while (!$this->stack->isEmpty()) {
                $projectTimer = $this->stack->pop();
                $projectSeriesMap->put($projectTimer->getName(), $projectTimer->getSeries());
                $sb .= $this->createTargetStatistics($projectTimer);
                $sb .= PHP_EOL;
                $sb .= $this->createTaskStatistics($projectTimer);
                $sb .= PHP_EOL;
            }
            print PHP_EOL;
            print $this->create('Project Statistics', $projectSeriesMap);
            print PHP_EOL . $sb;
        }
    }

    /**
     * @param ProjectTimer $projectTimer
     *
     * @return string
     */
    private function createTaskStatistics(ProjectTimer $projectTimer): string
    {
        return $this->create('Task Statistics - ' . $projectTimer->getName(), $projectTimer->toTaskSeriesMap());
    }

    /**
     * @param ProjectTimer $projectTimer
     *
     * @return string
     */
    private function createTargetStatistics(ProjectTimer $projectTimer): string
    {
        return $this->create('Target Statistics - ' . $projectTimer->getName(), $projectTimer->toTargetSeriesMap());
    }
}
