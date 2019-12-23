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

use PHPUnit\Framework\TestCase;

class DefaultLoggerTest extends TestCase
{
    /**
     * @param Throwable $error
     * @param bool      $verbose
     *
     * @return string
     */
    private static function msg(Throwable $error, bool $verbose): string
    {
        $m = '';
        DefaultLogger::throwableMessage($m, $error, $verbose);
        return $m;
    }

    /**
     * @return void
     *
     * @test
     */
    public function throwableMessageOne(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        self::assertEquals('build.xml:1:0 oops' . PHP_EOL, static::msg($be, false));
    }

    /**
     * @return void
     *
     * @test
     */
    public function throwableMessageTwo(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 2, 0));
        self::assertEquals(
            'build.xml:2:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:1:0 oops' . PHP_EOL,
            static::msg($be, false)
        );
    }

    /**
     * @return void
     *
     * @test
     */
    public function throwableMessageThree(): void
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 2, 0));
        $be = ProjectConfigurator::addLocationToBuildException($be, new Location('build.xml', 3, 0));
        self::assertEquals(
            'build.xml:3:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:2:0 The following error occurred while executing this line:' . PHP_EOL .
            'build.xml:1:0 oops' . PHP_EOL,
            static::msg($be, false)
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     *
     * @test
     */
    public function buildFinished(): void
    {
        $event  = new BuildEvent(new Project());
        $logger = new class extends DefaultLogger {
            /**
             * @param string            $message
             * @param OutputStream|null $stream
             * @param int|null          $priority
             *
             * @return void
             */
            public function printMessage(string $message, ?OutputStream $stream = null, ?int $priority = null): void
            {
                echo $message;
            }

            /**
             * @param float $micros
             *
             * @return string
             */
            public static function formatTime(float $micros): string
            {
                return 'TIME_STRING';
            }
        };
        $msg    = PHP_EOL . 'BUILD FINISHED' . PHP_EOL . PHP_EOL . 'Total time: TIME_STRING' . PHP_EOL;
        $this->expectOutputString($msg);
        $logger->buildFinished($event);
    }
}
