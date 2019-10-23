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

/**
 * Tests the SleepTask
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class SleepTaskTest extends BuildFileTest
{
    private const ERROR_RANGE = 1000000000;

    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/SleepTaskTest.xml'
        );
    }

    public function test1()
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(0, $timer->time());
    }

    private function timer()
    {
        return new class extends Timer
        {
            public function time()
            {
                return $this->etime - $this->stime;
            }
        };
    }

    public function test2()
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(0, $timer->time());
    }

    public function test3()
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(2000000000 - self::ERROR_RANGE, $timer->time());
    }

    public function test4()
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertTrue($timer->time() >= (2000000000 - self::ERROR_RANGE) && $timer->time() < 60000000000);
    }

    /**
     * Expected failure: negative sleep periods are not supported
     */
    public function test5()
    {
        $this->expectException(BuildException::class);
        $this->executeTarget(__FUNCTION__);
    }

    public function test6()
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertLessThan(2000000000, $timer->time());
    }
}
