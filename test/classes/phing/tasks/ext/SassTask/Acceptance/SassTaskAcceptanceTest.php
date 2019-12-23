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

use ScssPhp\ScssPhp\Compiler;

class SassTaskAcceptanceTest extends BuildFileTest
{
    use SassCleaner;

    private const SASS_TEST_BASE = PHING_TEST_BASE . '/etc/tasks/ext/sass/';

    /** @var FileSystem */
    private $fs;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(self::SASS_TEST_BASE . 'SassTaskTest.xml');
        $this->fs = FileSystem::getFileSystem();
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->sassCleanUp(self::SASS_TEST_BASE, 'test.css');
    }

    /**
     * @return void
     */
    public function testNothing(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither sass nor scssphp are to be used.');

        $this->executeTarget('nothing');
    }

    /**
     * @return void
     */
    public function testSetStyleToUnrecognised(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither sass nor scssphp are to be used.');

        $this->executeTarget('testSettingUnrecognisedStyle');

        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
    }

    /**
     * @return void
     */
    public function testNoFilesetAndNoFileSet(): void
    {
        $this->expectBuildExceptionContaining(
            'testNoFilesetAndNoFileSet',
            'testNoFilesetAndNoFileSet',
            "Missing either a nested fileset or attribute 'file'"
        );
    }

    /**
     * @return void
     */
    public function testItCompilesWithSass(): void
    {
        if (!$this->fs->which('sass')) {
            self::markTestSkipped('Sass not found');
        }
        $this->executeTarget('testItCompilesWithSass');
        $this->assertFileExists(self::SASS_TEST_BASE . 'test.css');
    }

    /**
     * @return void
     */
    public function testItCompilesWithScssPhp(): void
    {
        if (!class_exists(Compiler::class)) {
            $this->markTestSkipped('ScssPhp not found');
        }
        $this->executeTarget('testItCompilesWithScssPhp');
        $this->assertFileExists(self::SASS_TEST_BASE . 'test.css');
    }
}
