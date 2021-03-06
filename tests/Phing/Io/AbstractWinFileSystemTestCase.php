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
use PHPUnit\Framework\TestCase;

/**
 * @author Daniel Holmes
 */
abstract class AbstractWinFileSystemTestCase extends TestCase
{
    /**
     * @var FileSystem
     */
    private $fs;

    protected function setUp(): void
    {
        $this->fs = $this->createFileSystem();
    }

    public function testGetSeparatorReturnsCorrect(): void
    {
        $this->assertSame('\\', $this->fs->getSeparator());
    }

    public function testGetPathSeparatorReturnsCorrect(): void
    {
        $this->assertSame(';', $this->fs->getPathSeparator());
    }

    /**
     * @dataProvider normaliseDataProvider
     */
    public function testNormalise(string $expected, string $path): void
    {
        $normalisedPath = $this->fs->normalize($path);

        $this->assertSame($expected, $normalisedPath);
    }

    public function normaliseDataProvider(): array
    {
        return [
            'alreadyNormal' => ['C:\\My Files\\file.txt', 'C:\\My Files\\file.txt'],
            'incorrectSlashes' => ['C:\\My Files\\file.txt', 'C:/My Files/file.txt'],
            'empty' => ['', ''],
            'relative' => ['My Files\\file.txt', 'My Files/file.txt'],
            'directoryRelative' => ['c:My Files\\file.txt', 'c:My Files\\file.txt'],
            'driveRelative' => ['\\My Files\\file.txt', '\\My Files/file.txt'],
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array('\\\\server\\My Files\\file.txt', '\\\\server\\My Files\\file.txt')
        ];
    }

    /**
     * @dataProvider prefixLengthDataPRovider
     */
    public function testPrefixLength(int $expected, string $pathname): void
    {
        $length = $this->fs->prefixLength($pathname);

        $this->assertSame($expected, $length);
    }

    public function prefixLengthDataProvider(): array
    {
        return [
            'absoluteLocal' => [3, 'D:\\My Files\\file.txt'],
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array(2, '\\\\My Files\file.txt')
            'empty' => [0, ''],
            'driveRelative' => [1, '\\My Files\\file.txt'],
            'directoryRelative' => [2, 'c:My Files\\file.txt'],
            'relative' => [0, 'My Files\\file.txt'],
        ];
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $expected, string $parent, string $child): void
    {
        $resolved = $this->fs->resolve($parent, $child);

        $this->assertSame($expected, $resolved);
    }

    public function resolveDataProvider(): array
    {
        return [
            'emptyParent' => ['My Files\\file.txt', '', 'My Files\\file.txt'],
            'emptyChild' => ['C:\\My Files', 'C:\\My Files', ''],
            // Not working properly on my version of phpunit (3.6.10)
            //'uncChild' => array('C:\\My Files\\files\\file.txt', 'C:\\My Files', '\\\\files\\file.txt')
            'driveRelativeChild' => ['C:\\My Files\\file.txt', 'C:\\My Files', '\\file.txt'],
            'endSlashParent' => ['C:\\My Files\\file.txt', 'C:\\My Files\\', '\\file.txt'],
        ];
    }

    /**
     * @dataProvider resolveFileDataProvider
     */
    public function testResolveFile(string $expected, string $path, string $prefix): void
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue($path));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue($prefix));

        $resolved = $this->fs->resolveFile($file);

        $this->assertSame($expected, $resolved);
    }

    public function resolveFileDataProvider(): array
    {
        $cwd = getcwd();
        $driveLetter = '';
        // This is a bit weird, but it lets us run the win tests on unix machines. Might be better
        // to find an abstraction for drive letter within file system
        if ('WIN' === substr(PHP_OS, 0, 3)) {
            $colonPos = strpos($cwd, ':');
            $driveLetter = substr($cwd, 0, $colonPos) . ':';
        } else {
            $cwd = str_replace('/', '\\', $cwd);
        }

        return [
            'absoluteLocal' => ['C:\\My Files\\file.txt', 'C:\\My Files\\file.txt', 3],
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array('\\\\files\\file.txt', '\\\\files\\file.txt', 2)
            'relative' => [$cwd . '\\files\file.txt', 'files\\file.txt', 0],
            'driveRelative' => [$driveLetter . '\\files\\file.txt', '\\files\\file.txt', 1],
        ];
    }

    public function testResolveFileUnknownFile(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unresolvable path: file.txt');

        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue('file.txt'));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue(5));

        $this->fs->resolveFile($file);
    }

    public function testGetDefaultParent(): void
    {
        $parent = $this->fs->getDefaultParent();

        $this->assertSame('\\', $parent);
    }

    /**
     * @dataProvider fromURIPathDataProvider
     */
    public function testFromURIPath(string $expected, string $path): void
    {
        $resultPath = $this->fs->fromURIPath($path);

        $this->assertSame($expected, $resultPath);
    }

    public function fromURIPathDataProvider(): array
    {
        return [
            'singleLetter' => ['f', 'f'],
            'slashStart' => ['/foo', '/foo/'],
            'driveLetter' => ['c:/foo', '/c:/foo'],
            'slashPath' => ['c:/foo', 'c:/foo/'],
            'slashPathRootDrive' => ['c:/', '/c:/'],
        ];
    }

    /**
     * @dataProvider isAbsoluteDataProvider
     */
    public function testIsAbsolute(bool $expected, string $path, int $prefix): void
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue($path));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue($prefix));

        $is = $this->fs->isAbsolute($file);

        $this->assertSame($expected, $is);
    }

    public function isAbsoluteDataProvider(): array
    {
        return [
            // Doesn't work for my current version of phpunit
            //'unc' => array(true, '\\\\file.txt', 2)
            'absoluteLocal' => [true, 'C:\\file.txt', 3],
            'driveRelative' => [true, '\\file.txt', 1],
            'relative' => [false, 'file.txt', 0],
        ];
    }

    abstract protected function createFileSystem();
}
