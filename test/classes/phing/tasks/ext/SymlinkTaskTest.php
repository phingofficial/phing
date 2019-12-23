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
 * Tests the Symlink Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 * @requires OS Linux
 */
class SymlinkTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/SymlinkTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * @return void
     */
    public function testSymlinkExists(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(
            PHING_TEST_BASE . '/etc/tasks/ext/tmp/fake1',
            readlink(PHING_TEST_BASE . '/etc/tasks/ext/tmp/l')
        );
        $this->assertInLogs('Link exists: ');
    }

    /**
     * @return void
     */
    public function testOverwritingSymlink(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(
            PHING_TEST_BASE . '/etc/tasks/ext/tmp/fake2',
            readlink(PHING_TEST_BASE . '/etc/tasks/ext/tmp/l')
        );
        $this->assertInLogs('Link removed: ');
    }

    /**
     * @return void
     */
    public function testOverwritingDirectory(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(
            PHING_TEST_BASE . '/etc/tasks/ext/tmp/fake1',
            readlink(PHING_TEST_BASE . '/etc/tasks/ext/tmp/l')
        );
        $this->assertInLogs('Directory removed: ');
    }

    /**
     * @return void
     */
    public function testNotOverwritingSymlink(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(
            PHING_TEST_BASE . '/etc/tasks/ext/tmp/fake1',
            readlink(PHING_TEST_BASE . '/etc/tasks/ext/tmp/l')
        );
        $this->assertInLogs('Not overwriting existing link');
    }

    /**
     * @return void
     */
    public function testOverwriteDanglingSymlink(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Link removed: ');
        $this->assertEquals(
            PHING_TEST_BASE . '/etc/tasks/ext/tmp/fake2',
            readlink(PHING_TEST_BASE . '/etc/tasks/ext/tmp/l')
        );
    }
}
