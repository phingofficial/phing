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

namespace Phing\Test\Task\Ext\Analyzer;

use Phing\Test\Support\BuildFileTest;

/**
 * Unit tests for PHPMD task.
 *
 * @internal
 * @requires PHP <= 8.3
 * /
 */
class PHPMDTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (!class_exists('\PHPMD\PHPMD')) {
            $this->markTestSkipped('The PHPMD tasks depend on the phpmd/phpmd package being installed.');
        }
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/phpmd/build.xml');
    }

    public function testReportText(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.txt'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.txt');
    }

    public function testReportHtml(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.html'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.html');
    }

    public function testReportXml(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.xml'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phpmd/phpmd-report.xml');
    }
}
