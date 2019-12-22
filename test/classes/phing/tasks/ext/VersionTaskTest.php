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

/**
 * @author Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext
 */
class VersionTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/version.xml');
    }

    public function tearDown(): void
    {
        if (file_exists(PHING_TEST_BASE . '/etc/tasks/ext/build.version')) {
            unlink(PHING_TEST_BASE . '/etc/tasks/ext/build.version');
        }

        if (file_exists(PHING_TEST_BASE . '/etc/tasks/ext/property.version')) {
            unlink(PHING_TEST_BASE . '/etc/tasks/ext/property.version');
        }
    }

    public function testBugfix()
    {
        $this->expectLog('testBugfix', '1.0.1');
    }

    public function testMinor()
    {
        $this->expectLog('testMinor', '1.1.0');
    }

    public function testMajor()
    {
        $this->expectLog('testMajor', '2.0.0');
    }

    public function testDefault()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('build.version', '1.0.0');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/build.version', 'File not found');
    }

    public function testPropFile()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('propfile.version', '4.5.5');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/property.version', 'File not found');
    }

    public function testPropFileWithDefaultProperty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('build.version', '4.5.5');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/build.version', 'File not found');
    }

    public function testWithStartingVersion()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('build.version', '1.0.1');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/build.version', 'File not found');
    }

    /**
     * Testing \VersionTask::getVersion
     *
     * @dataProvider versionProvider
     */
    public function testGetVersionMethod($releaseType, $version, $expectedVersion)
    {
        $versionTask = new VersionTask();
        $versionTask->setReleasetype($releaseType);

        $reflector = new ReflectionObject($versionTask);
        $method    = $reflector->getMethod('getVersion');
        $method->setAccessible(true);

        $newVersion = $method->invoke($versionTask, $version);
        $this->assertSame($expectedVersion, $newVersion);
    }

    public function versionProvider()
    {
        return [
            [VersionTask::RELEASETYPE_MAJOR, null, '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'x', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '0', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v0', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'a3', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v3', 'v4.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'qsdf', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'vvvv', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '0.6', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v0.6', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '5.0', '6.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v5.0', 'v6.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '5.5', '6.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v5.5', 'v6.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '0.0.0', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v0.0.0', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '0.0.15', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v0.0.15', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '0.1.15', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v0.1.15', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '7.0.15', '8.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v7.0.15', 'v8.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2.3.4', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2.3.4', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2-RC1', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2-RC1', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2.3-RC1', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2.3-RC1', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2.3.4-RC1', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2.3.4-RC1', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2.3v654.4', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2.3v56465.4-RC1', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, '2.hello.world', '3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'v2.hello.world', 'v3.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'hello.world.3', '1.0.0'],
            [VersionTask::RELEASETYPE_MAJOR, 'vhello.world.3', 'v1.0.0'],
            [VersionTask::RELEASETYPE_MINOR, null, '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'x', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v', 'v0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '0', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v0', 'v0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'a3', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v3', 'v3.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'qsdf', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'vvvv', 'v0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '0.6', '0.7.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v0.6', 'v0.7.0'],
            [VersionTask::RELEASETYPE_MINOR, '5.0', '5.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v5.0', 'v5.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '5.5', '5.6.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v5.5', 'v5.6.0'],
            [VersionTask::RELEASETYPE_MINOR, '0.0.0', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v0.0.0', 'v0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '0.0.15', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v0.0.15', 'v0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '0.1.15', '0.2.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v0.1.15', 'v0.2.0'],
            [VersionTask::RELEASETYPE_MINOR, '7.0.15', '7.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v7.0.15', 'v7.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '2.3.4', '2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2.3.4', 'v2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, '2-RC1', '2.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2-RC1', 'v2.1.0'],
            [VersionTask::RELEASETYPE_MINOR, '2.3-RC1', '2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2.3-RC1', 'v2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, '2.3.4-RC1', '2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2.3.4-RC1', 'v2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, '2.3v654.4', '2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2.3v56465.4-RC1', 'v2.4.0'],
            [VersionTask::RELEASETYPE_MINOR, '2.hello.world', '2.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'v2.hello.world', 'v2.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'hello.world.3', '0.1.0'],
            [VersionTask::RELEASETYPE_MINOR, 'vhello.world.3', 'v0.1.0'],
            [VersionTask::RELEASETYPE_BUGFIX, null, '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'x', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v', 'v0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '0', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v0', 'v0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'a3', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v3', 'v3.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'qsdf', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'vvvv', 'v0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '0.6', '0.6.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v0.6', 'v0.6.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '5.0', '5.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v5.0', 'v5.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '5.5', '5.5.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v5.5', 'v5.5.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '0.0.0', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v0.0.0', 'v0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '0.0.15', '0.0.16'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v0.0.15', 'v0.0.16'],
            [VersionTask::RELEASETYPE_BUGFIX, '0.1.15', '0.1.16'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v0.1.15', 'v0.1.16'],
            [VersionTask::RELEASETYPE_BUGFIX, '7.0.15', '7.0.16'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v7.0.15', 'v7.0.16'],
            [VersionTask::RELEASETYPE_BUGFIX, '2.3.4', '2.3.5'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2.3.4', 'v2.3.5'],
            [VersionTask::RELEASETYPE_BUGFIX, '2-RC1', '2.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2-RC1', 'v2.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '2.3-RC1', '2.3.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2.3-RC1', 'v2.3.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '2.3.4-RC1', '2.3.5'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2.3.4-RC1', 'v2.3.5'],
            [VersionTask::RELEASETYPE_BUGFIX, '2.3v654.4', '2.3.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2.3v56465.4-RC1', 'v2.3.1'],
            [VersionTask::RELEASETYPE_BUGFIX, '2.hello.world', '2.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'v2.hello.world', 'v2.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'hello.world.3', '0.0.1'],
            [VersionTask::RELEASETYPE_BUGFIX, 'vhello.world.3', 'v0.0.1'],
        ];
    }
}
