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

namespace Phing\Test\Io;

use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Io\IOException;
use Phing\Io\UnixFileSystem;
use Phing\Io\WindowsFileSystem;
use Phing\Phing;
use ReflectionClass;

/**
 * Unit test for FileSystem.
 */
class FileSystemTest extends \PHPUnit\Framework\TestCase
{
    private $oldFsType = '';

    public function setUp(): void
    {
        $this->oldFsType = Phing::getProperty('host.fstype');
    }

    public function tearDown(): void
    {
        Phing::setProperty('host.fstype', $this->oldFsType);
        $this->resetFileSystem();
    }

    public function testGetFileSystemWithUnknownTypeKeyThrowsException()
    {
        $this->resetFileSystem();

        $this->expectException(IOException::class);

        Phing::setProperty('host.fstype', 'UNRECOGNISED');

        FileSystem::getFileSystem();
    }

    /**
     * @dataProvider fileSystemMappingsDataProvider
     *
     * @param mixed $expectedFileSystemClass
     * @param mixed $fsTypeKey
     */
    public function testGetFileSystemReturnsCorrect($expectedFileSystemClass, $fsTypeKey)
    {
        $this->resetFileSystem();

        Phing::setProperty('host.fstype', $fsTypeKey);

        $system = FileSystem::getFileSystem();

        $this->assertInstanceOf($expectedFileSystemClass, $system);
    }

    public function fileSystemMappingsDataProvider()
    {
        return [
            [UnixFileSystem::class, 'UNIX'],
            [WindowsFileSystem::class, 'WINDOWS'],
        ];
    }

    public function testWhichFailsNonStringExecutable()
    {
        $fs = FileSystem::getFileSystem();
        $path = $fs->which(42);
        $this->assertEquals($path, false);
    }

    public function testWhichFailsDueToUnusualExecutableName()
    {
        $fs = FileSystem::getFileSystem();
        $path = $fs->which('tasword.bin');
        $this->assertEquals($path, false);
    }

    public function testWhichHinkyExecutableNameWithSeparator()
    {
        $fs = FileSystem::getFileSystem();
        $path = $fs->which('zx:\tasword.bin');
        $this->assertEquals($path, false);
    }

    public function testListContentsWithNumericName()
    {
        $fs = FileSystem::getFileSystem();

        $parentDir = new File(__DIR__ . '/../../etc/system/io/testdir');
        $contents = $fs->listContents($parentDir);

        foreach ($contents as $filename) {
            self::assertIsString($filename);
        }
    }

    protected function resetFileSystem()
    {
        $refClass = new ReflectionClass(FileSystem::class);
        $refProperty = $refClass->getProperty('fs');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null);
    }
}
