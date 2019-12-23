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
 * Tests for PHPLOCTask
 *
 * @author Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext
 * @requires PHP < 7.3
 */
class PHPLOCTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/phploc/build.xml');
    }

    /**
     * @return void
     */
    public function testReportText(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.txt'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.txt');
    }

    /**
     * @return void
     */
    public function testReportCSV(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.csv'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.csv');
    }

    /**
     * @return void
     */
    public function testReportXML(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.xml'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.xml');
    }

    /**
     * @return void
     */
    public function testFormatters(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.txt'
        );
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.csv'
        );
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.xml'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.txt');
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.csv');
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/phploc/phploc-report.xml');
    }
}
