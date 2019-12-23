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
 * @requires extension xsl
 */
class VisualizerTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.xml');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * Test VisualizerTask with all the default values
     *
     * @return void
     */
    public function testDefaultValues(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing different diagram formats: png, puml, svg and eps
     *
     * @return void
     */
    public function testFormat(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.svg');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.eps');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.puml');
        $this->assertInLogs('VisualizerTaskTest.svg');
        $this->assertInLogs('VisualizerTaskTest.eps');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml', 1200);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.svg', 23000);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.eps', 100000);
    }

    /**
     * Test that an exception is raised when invalid format is used
     *
     * @return void
     */
    public function testInvalidFormat(): void
    {
        $this->expectBuildException(__FUNCTION__, "'jpg' is not a valid format");
        $this->assertInLogs("'jpg' is not a valid format");
    }

    /**
     * Testing custom destination including filename
     *
     * @return void
     */
    public function testDestinationFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/tmp/my-diagram.png');
        $this->assertInLogs('my-diagram.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/my-diagram.png', 80000);
    }

    /**
     * Testing custom destination without filename
     *
     * @return void
     */
    public function testDestinationDirectory(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing that an exception is raised when an invalid directory is used as destination
     *
     * @return void
     */
    public function testInvalidDestination(): void
    {
        $this->expectBuildException(__FUNCTION__, "Directory 'foo/bar/baz/' is invalid");
        $this->assertInLogs("Directory 'foo/bar/baz/' is invalid");
    }

    /**
     * Testing that a custom PlantUML server can be used
     *
     * @return void
     */
    public function testCustomServer(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing that exception is raised when an invalid URL is used
     *
     * @return void
     */
    public function testInvalidServer(): void
    {
        $this->expectBuildException(__FUNCTION__, 'Invalid PlantUml server');
        $this->assertInLogs('Invalid PlantUml server');
    }
}
