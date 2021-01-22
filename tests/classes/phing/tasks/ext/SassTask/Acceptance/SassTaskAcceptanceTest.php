<?php

use Phing\Exception\BuildException;
use Phing\Io\FileSystem;
use Phing\Project;

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

class SassTaskAcceptanceTest extends BuildFileTest
{
    use SassCleaner;

    private const SASS_TEST_BASE = PHING_TEST_BASE . "/etc/tasks/ext/sass/";

    /** @var FileSystem */
    private $fs;

    public function setUp(): void
    {
        $this->configureProject(self::SASS_TEST_BASE . "SassTaskTest.xml");
        $this->fs = FileSystem::getFileSystem();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->sassCleanUp(self::SASS_TEST_BASE, 'test.css');
    }

    public function testNothing(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither sass nor scssphp are to be used.');

        $this->executeTarget("nothing");
    }

    public function testSetStyleToUnrecognised(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither sass nor scssphp are to be used.');

        $this->executeTarget("testSettingUnrecognisedStyle");

        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
    }

    public function testNoFilesetAndNoFileSet(): void
    {
        $this->expectBuildExceptionContaining(
            'testNoFilesetAndNoFileSet',
            'testNoFilesetAndNoFileSet',
            "Missing either a nested fileset or attribute 'file'"
        );
    }

    public function testItCompilesWithSass(): void
    {
        if (!$this->fs->which('sass')) {
            $this->markTestSkipped('Sass not found');
        }
        $this->executeTarget("testItCompilesWithSass");
        $this->assertFileExists(self::SASS_TEST_BASE . "test.css");
    }

    public function testItCompilesWithScssPhp(): void
    {
        if (!class_exists('\ScssPhp\ScssPhp\Compiler')) {
            $this->markTestSkipped('ScssPhp not found');
        }
        $this->executeTarget("testItCompilesWithScssPhp");
        $this->assertFileExists(self::SASS_TEST_BASE . "test.css");
    }
}
