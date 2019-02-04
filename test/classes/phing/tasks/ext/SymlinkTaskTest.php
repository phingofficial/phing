<?php
/*
 *
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
 * Tests the Symlink Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 * @requires OS Linux
 */
class SymlinkTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/ext/SymlinkTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testSymlinkExists()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(PHING_TEST_BASE . "/etc/tasks/ext/tmp/fake1",
            readlink(PHING_TEST_BASE . "/etc/tasks/ext/tmp/l"));
        $this->assertInLogs("Link exists: ");
    }

    public function testOverwritingSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(PHING_TEST_BASE . "/etc/tasks/ext/tmp/fake2",
            readlink(PHING_TEST_BASE . "/etc/tasks/ext/tmp/l"));
        $this->assertInLogs("Link removed: ");
    }

    public function testOverwritingDirectory()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(PHING_TEST_BASE . "/etc/tasks/ext/tmp/fake1",
            readlink(PHING_TEST_BASE . "/etc/tasks/ext/tmp/l"));
        $this->assertInLogs("Directory removed: ");
    }

    public function testNotOverwritingSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(PHING_TEST_BASE . "/etc/tasks/ext/tmp/fake1",
            readlink(PHING_TEST_BASE . "/etc/tasks/ext/tmp/l"));
        $this->assertInLogs("Not overwriting existing link");
    }

    public function testOverwriteDanglingSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs("Link removed: ");
        $this->assertEquals(PHING_TEST_BASE . "/etc/tasks/ext/tmp/fake2",
            readlink(PHING_TEST_BASE . "/etc/tasks/ext/tmp/l"));
    }
}
