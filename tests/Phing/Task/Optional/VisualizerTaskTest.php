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

class VisualizerTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.xml');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * Testing different diagram formats: png, puml, svg and eps.
     */
    public function testFormat(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml');
        $this->assertInLogs('VisualizerTaskTest.puml');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml', 1200);
    }

    /**
     * Test that an exception is raised when invalid format is used.
     */
    public function testInvalidFormat(): void
    {
        $this->expectBuildException(__FUNCTION__, "'jpg' is not a valid format");
        $this->assertInLogs("'jpg' is not a valid format");
    }

    /**
     * Testing custom destination including filename.
     */
    public function testDestinationFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(PHING_TEST_BASE . '/tmp/my-diagram.puml');
        $this->assertInLogs('my-diagram.puml');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/my-diagram.puml', 1200);
    }

    /**
     * Testing custom destination without filename.
     */
    public function testDestinationDirectory(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.puml');
        $this->assertInLogs('VisualizerTaskTest.puml');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.puml', 1200);
    }

    /**
     * Testing that an exception is raised when an invalid directory is used as destination.
     */
    public function testInvalidDestination(): void
    {
        $this->expectBuildException(__FUNCTION__, "Directory 'foo/bar/baz/' is invalid");
        $this->assertInLogs("Directory 'foo/bar/baz/' is invalid");
    }

    /**
     * Testing that exception is raised when an invalid URL is used.
     */
    public function testInvalidServer(): void
    {
        $this->expectBuildException(__FUNCTION__, 'Invalid PlantUml server');
        $this->assertInLogs('Invalid PlantUml server');
    }
}
