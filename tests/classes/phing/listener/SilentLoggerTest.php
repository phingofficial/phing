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

use Phing\Io\OutputStream;
use Phing\Listener\BuildEvent;
use Phing\Listener\SilentLogger;
use PHPUnit\Framework\TestCase;

class SilentLoggerTest extends TestCase
{
    /**
     * @test
     */
    public function buildFinished()
    {
        $event = new BuildEvent(new Project());
        $logger = new SilentLogger();
        $this->expectOutputString('');
        $logger->buildFinished($event);
    }
    /**
     * @test
     */
    public function buildFinishedException()
    {
        $event = new BuildEvent(new Project());
        $event->setException(new Exception('test'));
        $logger = new class extends SilentLogger {
            public function printMessage($message, ?OutputStream $stream = null, $priority = null)
            {
                echo $message;
            }

            public static function formatTime($micros)
            {
                return 'TIME_STRING';
            }
        };
        $msg = '/' . PHP_EOL . 'BUILD FAILED' . PHP_EOL . 'test' . PHP_EOL . '/';
        $this->expectOutputRegex($msg);
        $logger->buildFinished($event);
    }
}
