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

namespace Phing\Test\Listener;

use Phing\Exception\BuildException;
use Phing\Io\OutputStream;
use Phing\Listener\BuildEvent;
use Phing\Listener\DefaultLogger;
use Phing\Parser\Location;
use Phing\Parser\ProjectConfigurator;
use Phing\Project;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
class DefaultLoggerTest extends TestCase
{
    /**
     * @test
     */
    public function throwableMessageOne(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $this->assertEquals('build.xml:1:0 oops' . PHP_EOL, static::msg($be, false));
    }

    /**
     * @test
     */
    public function throwableMessageTwo(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 2, 0));
        $this->assertEquals(
            'build.xml:2:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:1:0 oops' . PHP_EOL,
            static::msg($be, false)
        );
    }

    /**
     * @test
     */
    public function throwableMessageThree(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 2, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 3, 0));
        $this->assertEquals(
            'build.xml:3:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:2:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:1:0 oops' . PHP_EOL,
            static::msg($be, false)
        );
    }

    /**
     * @test
     */
    public function buildFinished(): void
    {
        $event = new BuildEvent(new Project());
        $logger = new class() extends DefaultLogger {
            public function printMessage($message, ?OutputStream $stream = null, $priority = null)
            {
                echo $message;
            }

            public static function formatTime(float $seconds): string
            {
                return 'TIME_STRING';
            }
        };
        $msg = PHP_EOL . 'BUILD FINISHED' . PHP_EOL . PHP_EOL . 'Total time: TIME_STRING' . PHP_EOL;
        $this->expectOutputString($msg);
        $logger->buildFinished($event);
    }

    /**
     * @dataProvider formatTimeProvider
     *
     * @param mixed $seconds
     */
    public function testFormatTime($seconds, string $expectedText): void
    {
        $formattedText = DefaultLogger::formatTime($seconds);
        $this->assertSame($formattedText, $expectedText);
    }

    public function formatTimeProvider(): array
    {
        return [
            [0.0005, '0.0005 seconds'],
            [0.000099, '0.0001 seconds'],
            [1, '1.0000 second'],
            [30.1234, '30.1234 seconds'],
            [59.9999, '59.9999 seconds'],
            [60.00, '1 minute  0.00 seconds'],
            [61.0099, '1 minute  1.01 seconds'],
            [61.9999, '1 minute  2.00 seconds'],
            [3000.2020, '50 minutes  0.20 seconds'],
            [3061, '51 minutes  1.00 second'],
            [3600.0000, '1 hour  0 minutes  0.00 seconds'],
            [3660.0000, '1 hour  1 minute  0.00 seconds'],
            [3661.0000, '1 hour  1 minute  1.00 second'],
            [7261, '2 hours  1 minute  1.00 second'],
            [7458.5499, '2 hours  4 minutes  18.55 seconds'],
            [86400.3, '1 day  0 hours  0 minutes  0.30 seconds'],
            [90000.2, '1 day  1 hour  0 minutes  0.20 seconds'],
            [90061, '1 day  1 hour  1 minute  1.00 second'],
            [210546.8614, '2 days  10 hours  29 minutes  6.86 seconds'],
            [3938279.8591, '45 days  13 hours  57 minutes  59.86 seconds'],
        ];
    }

    private static function msg(Throwable $error, bool $verbose): string
    {
        $m = '';
        DefaultLogger::throwableMessage($m, $error, $verbose);

        return $m;
    }
}
