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
 * Tests the Move Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class MoveTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/system/MoveTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testMoveSingleFile()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/fileB');
    }

    public function testMoveFileSet()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileNotExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/base/fileA');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/new/fileA');
    }

    public function testRenameDirectory()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileNotExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/base/fileA');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/new/fileA');
    }

    /**
     * Regression test for ticket {@link http://www.phing.info/trac/ticket/582}
     * - Add haltonerror attribute to copy/move tasks
     */
    public function testIgnoreErrors()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs("Could not find file ");
    }

    /**
     * Regression test for ticket {@link http://www.phing.info/trac/ticket/307}
     * - Replaceregexp filter works in Copy task but not Move task
     */
    public function testReplaceRegexp()
    {
        $this->executeTarget(__FUNCTION__);

        $contents = file_get_contents(PHING_TEST_BASE . "/etc/tasks/system/tmp/anotherfile.bak");

        $this->assertEquals("BAR", $contents);
    }
}
