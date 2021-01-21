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

use Phing\Exception\BuildException;
use Phing\Listener\BuildEvent;
use Phing\Listener\DefaultLogger;
use PHPUnit\Framework\TestCase;

class DefaultLoggerTest extends TestCase
{
    private static function msg(Throwable $error, bool $verbose): string
    {
        $m = '';
        DefaultLogger::throwableMessage($m, $error, $verbose);
        return $m;
    }

    /**
     * @test
     */
    public function throwableMessageOne()
    {
        $be = new BuildException('oops', new Location('build.xml', 1, 0));
        $this->assertEquals('build.xml:1:0 oops' . PHP_EOL, static::msg($be, false));
    }

    /**
     * @test
     */
    public function throwableMessageTwo()
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
    public function throwableMessageThree()
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
    public function buildFinished()
    {
        $event = new BuildEvent(new Project());
        $logger = new class extends DefaultLogger {
            public function printMessage($message, ?OutputStream $stream = null, $priority = null)
            {
                echo $message;
            }

            public static function formatTime($micros)
            {
                return 'TIME_STRING';
            }
        };
        $msg = PHP_EOL . 'BUILD FINISHED' . PHP_EOL . PHP_EOL . 'Total time: TIME_STRING' . PHP_EOL;
        $this->expectOutputString($msg);
        $logger->buildFinished($event);
    }
}
