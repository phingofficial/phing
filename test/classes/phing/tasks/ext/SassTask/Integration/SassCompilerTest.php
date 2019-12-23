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

use PHPUnit\Framework\TestCase;

class SassCompilerTest extends TestCase
{
    use SassCleaner;

    private const SASS_TEST_BASE = PHING_TEST_BASE . '/etc/tasks/ext/sass/';

    /** @var SassCompiler */
    private $compiler;

    /**
     * @return void
     *
     * @throws IOException
     */
    protected function setUp(): void
    {
        $fs = FileSystem::getFileSystem();
        if (!$fs->which('sass')) {
            $this->markTestSkipped('Sass not found');
        }

        $this->compiler = new SassCompiler('sass', '');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->sassCleanUp(self::SASS_TEST_BASE, 'test.css');
    }

    /**
     * @return void
     */
    public function testItProducesAnyCompiledOutput(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'test.sass',
            self::SASS_TEST_BASE . 'test.css',
            false
        );

        $this->assertFileExists(self::SASS_TEST_BASE . 'test.css');
    }

    /**
     * @return void
     */
    public function testItNotProducesAnyCompiledOutputWhenNoInput(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'non-existing.sass',
            self::SASS_TEST_BASE . 'test.css',
            false
        );

        $this->assertFileNotExists(self::SASS_TEST_BASE . 'test.css');
    }

    /**
     * @return void
     */
    public function testItThrowsExceptionWhenFailOnErrorIsSet(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'non-existing.sass',
            self::SASS_TEST_BASE . 'test.css',
            true
        );

        $this->expectException(BuildException::class);

        $this->assertFileNotExists(self::SASS_TEST_BASE . 'test.css');
    }
}
