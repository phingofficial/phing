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

namespace Phing\Test\Task\Optional;

use Phing\Test\Support\BuildFileTest;

/**
 * Test for StopwatchTask
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class StopwatchTaskTest extends BuildFileTest
{
    /**
     * Sets up the fixture.
     * @throws \Phing\Io\IOException
     */
    public function setUp(): void
    {
        if (!class_exists('Symfony\\Component\\Stopwatch\\Stopwatch')) {
            $this->markTestSkipped('Need stopwatch installed to test');
        }

        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/StopwatchTaskTest.xml');
    }

    public function testStopwatch(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'Category:   test-cat');
    }

    public function testStopwatchFails(): void
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, 'method does not exists', 'does not exist');
    }
}
