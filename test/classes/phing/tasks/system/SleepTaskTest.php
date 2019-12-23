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
 * Tests the SleepTask
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class SleepTaskTest extends BuildFileTest
{
    private const ERROR_RANGE = 1000000000;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/SleepTaskTest.xml'
        );
    }

    /**
     * @return void
     */
    public function test1(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        self::assertGreaterThanOrEqual(0, $timer->time());
    }

    /**
     * @return Timer
     */
    private function timer(): Timer
    {
        return new class extends Timer
        {
            /**
             * @return float
             */
            public function time(): float
            {
                return $this->etime - $this->stime;
            }
        };
    }

    /**
     * @return void
     */
    public function test2(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        self::assertGreaterThanOrEqual(0, $timer->time());
    }

    /**
     * @return void
     */
    public function test3(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        self::assertGreaterThanOrEqual(2000000000 - self::ERROR_RANGE, $timer->time());
    }

    /**
     * @return void
     */
    public function test4(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        self::assertTrue($timer->time() >= (2000000000 - self::ERROR_RANGE) && $timer->time() < 60000000000);
    }

    /**
     * Expected failure: negative sleep periods are not supported
     *
     * @return void
     */
    public function test5(): void
    {
        $this->expectException(BuildException::class);
        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @return void
     */
    public function test6(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        self::assertLessThan(2000000000, $timer->time());
    }
}
