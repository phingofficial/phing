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
 * Tests the Touch Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class TouchTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/TouchTaskTest.xml'
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
    public function testSimpleTouch(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . '/etc/tasks/system/tmp/simple-file'
        );
    }

    /**
     * @return void
     */
    public function testMkdirs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . '/etc/tasks/system/tmp/this/is/a/test/file'
        );
    }

    /**
     * @return void
     */
    public function testMkdirsFails(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Error touch()ing file');

        $this->executeTarget(__FUNCTION__);

        $this->assertFileNotExists(
            PHING_TEST_BASE
            . '/etc/tasks/system/tmp/this/is/a/test/file'
        );
    }

    /**
     * @return void
     */
    public function testFilelist(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . '/etc/tasks/system/tmp/simple-file'
        );
    }

    /**
     * @return void
     */
    public function testFileset(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . '/etc/tasks/system/tmp/simple-file'
        );
    }
}
