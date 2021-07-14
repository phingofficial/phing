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
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @requires extension xdebug
 *
 * @internal
 * @coversNothing
 */
class CoverageMergeTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (!file_exists(PHING_TEST_BASE . '/etc/tasks/ext/coverage/workspace')) {
            mkdir(PHING_TEST_BASE . '/etc/tasks/ext/coverage/workspace');
        }
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/coverage/build.xml');
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/etc/tasks/ext/coverage/workspace');
    }

    public function testCoverage(): void
    {
        $workspace = PHING_TEST_BASE . '/etc/tasks/ext/coverage/workspace';
        $this->executeTarget('collect');
        $this->assertFileExists($workspace . '/1/clover-coverage.xml');
        $this->assertFileExists($workspace . '/2/clover-coverage.xml');
        $this->assertFileExists($workspace . '/3/clover-coverage.xml');
        $this->assertFileExists($workspace . '/output.xml');
        $this->assertFileExists($workspace . '/test.db');
        $this->assertFileExists($workspace . '/test-results1.xml');
        $this->assertFileExists($workspace . '/test-results2.xml');
        $this->assertFileExists($workspace . '/test-results3.xml');
        $this->assertStringNotContainsString('"coverage";a:0:{}', file_get_contents($workspace . '/test.db'));
        $this->assertStringContainsString('Dummy1Test.php', file_get_contents($workspace . '/test.db'));
        $this->assertStringContainsString('Dummy2Test.php', file_get_contents($workspace . '/test.db'));
        $this->assertStringContainsString('Dummy3Test.php', file_get_contents($workspace . '/test.db'));
    }
}
