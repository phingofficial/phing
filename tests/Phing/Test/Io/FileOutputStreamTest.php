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
use Phing\Io\FileOutputStream;
use Phing\Io\FileSystem;
use Phing\Io\IOException;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for FileOutputStream.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @internal
 */
class FileOutputStreamTest extends TestCase
{
    /**
     * @var FileOutputStream
     */
    private $outStream;

    /**
     * @var File
     */
    private $tmpFile;

    public function setUp(): void
    {
        $this->tmpFile = new File(PHING_TEST_BASE . '/tmp/' . get_class($this) . '.txt');
        $this->outStream = new FileOutputStream($this->tmpFile);
    }

    public function tearDown(): void
    {
        if (is_object($this->outStream)) {
            $this->outStream->close();
        }
        FileSystem::getFileSystem()->unlink($this->tmpFile->getAbsolutePath());
    }

    public function assertFileContents($contents): void
    {
        $actual = file_get_contents($this->tmpFile->getAbsolutePath());
        $this->assertEquals(
            $contents,
            $actual,
            "Expected file contents to match; expected '" . $contents . "', actual '" . $actual . "'"
        );
    }

    public function testWrite(): void
    {
        $string = '0123456789';
        $this->outStream->write($string);

        $this->assertFileContents($string);

        $newstring = $string;

        // check offset (no len)
        $this->outStream->write($string, 1);
        $this->outStream->flush();
        $newstring .= '123456789';
        $this->assertFileContents($newstring);

        // check len (no offset)
        $this->outStream->write($string, 0, 3);
        $this->outStream->flush();
        $newstring .= '012';
        $this->assertFileContents($newstring);
    }

    public function testFlush(): void
    {
        $this->expectNotToPerformAssertions();
        $this->outStream->write('Some data');
        $this->outStream->flush();
        $this->outStream->close();

        try {
            $this->outStream->flush();
            $this->fail('Expected IOException when attempting to flush a closed stream.');
        } catch (IOException $ioe) {
            // exception is expected
        }
    }
}
