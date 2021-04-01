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

namespace Phing\Test\Task\System;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Test\Support\BuildFileTest;

/**
 * Unit test for ForeachTask.
 *
 * @author  Laurent Laville <pear@laurent-laville.org>
 */
class ForeachTaskTest extends BuildFileTest
{
    /**
     * Sets up the fixture.
     *
     */
    public function setUp(): void
    {
        // Tests definitions
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/ForeachTaskTest.xml');
    }

    /**
     * Test for required attributes
     *
     */
    public function testRequiredParameters()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * Test list of values to process without the 'param' attribute
     *
     */
    public function testListWithoutParam()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * Test list of values to process without the 'target' attribute
     *
     */
    public function testListWithoutCalleeTarget()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * Test to get the right log message on fileset usage
     *
     */
    public function testLogMessageWithFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Processed 0 directories and 0 files', Project::MSG_VERBOSE);
    }

    /**
     * Test to get the right log message on fileset usage
     *
     */
    public function testLogMessageWithDirset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('Processed 0 directories and 0 files', Project::MSG_VERBOSE);
    }

    /**
     * Test to get the right log message on list usage with multiple entries
     *
     */
    public function testLogMessageWithList()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Processed 3 entries in list', Project::MSG_VERBOSE);
    }

    /**
     * Test to get the right log message on list usage with a single entry
     *
     */
    public function testLogMessageWithListUniqueEntry()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Processed 1 entry in list', Project::MSG_VERBOSE);
    }

    /**
     * Test to get the right log message on fileset usage
     *
     */
    public function testLogMessageWithPath()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('Processed 0 directories and 0 files', Project::MSG_VERBOSE);
    }

    public function testIndex()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('2 - de');
    }
}
