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

/**
 * Unit test for FileSystem
 *
 * @package phing.system.io
 */
class FileSystemTest extends TestCase
{
    /**
     * @var string
     */
    private $oldFsType = '';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->oldFsType = Phing::getProperty('host.fstype');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        Phing::setProperty('host.fstype', $this->oldFsType);
        $this->resetFileSystem();
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    protected function resetFileSystem(): void
    {
        $refClass    = new ReflectionClass('FileSystem');
        $refProperty = $refClass->getProperty('fs');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null);
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws ReflectionException
     */
    public function testGetFileSystemWithUnknownTypeKeyThrowsException(): void
    {
        $this->resetFileSystem();

        $this->expectException('IOException');

        Phing::setProperty('host.fstype', 'UNRECOGNISED');

        FileSystem::getFileSystem();
    }

    /**
     * @param string $expectedFileSystemClass
     * @param string $fsTypeKey
     *
     * @return void
     *
     * @throws IOException
     * @throws ReflectionException
     *
     * @dataProvider fileSystemMappingsDataProvider
     */
    public function testGetFileSystemReturnsCorrect(string $expectedFileSystemClass, string $fsTypeKey): void
    {
        $this->resetFileSystem();

        Phing::setProperty('host.fstype', $fsTypeKey);

        $system = FileSystem::getFileSystem();

        self::assertInstanceOf($expectedFileSystemClass, $system);
    }

    /**
     * @return array[]
     */
    public function fileSystemMappingsDataProvider(): array
    {
        return [
            ['UnixFileSystem', 'UNIX'],
            ['WindowsFileSystem', 'WINDOWS'],
        ];
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testWhichFailsNonStringExecutable(): void
    {
        $fs   = FileSystem::getFileSystem();
        $path = $fs->which(42);
        self::assertEquals($path, false);
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testWhichFailsDueToUnusualExecutableName(): void
    {
        $fs   = FileSystem::getFileSystem();
        $path = $fs->which('tasword.bin');
        self::assertEquals($path, false);
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testWhichHinkyExecutableNameWithSeparator(): void
    {
        $fs   = FileSystem::getFileSystem();
        $path = $fs->which('zx:\tasword.bin');
        self::assertEquals($path, false);
    }
}
