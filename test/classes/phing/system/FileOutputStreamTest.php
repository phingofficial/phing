<?php

/*
 *
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

use org\bovigo\vfs\vfsStream;

/**
 * Unit test for FileOutputStream.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.system
 */
class FileOutputStreamTest extends \PHPUnit\Framework\TestCase
{
    private const DATA_PATH = 'root';

    /**
     * @var FileOutputStream
     */
    private $outStream;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $uri;

    public function setUp(): void
    {
        $structure = [
            'tmp' => [],
        ];

        vfsStream::setup(self::DATA_PATH, null, $structure);

        $this->uri = vfsStream::url(self::DATA_PATH . '/tmp/' . get_class($this) . ".txt");

        $tmpFile = new PhingFile($this->uri);
        $this->outStream = new FileOutputStream($tmpFile);
    }

    public function tearDown(): void
    {
        if (is_object($this->outStream)) {
            $this->outStream->close();
        }
        unlink($this->uri);
    }

    public function assertFileContents($contents)
    {
        $actual = file_get_contents($this->uri);
        self::assertEquals(
            $contents,
            $actual,
            "Expected file contents to match; expected '" . $contents . "', actual '" . $actual . "'"
        );
    }

    public function testWrite()
    {
        $string = "0123456789";
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

    public function testFlush()
    {
        $this->outStream->write("Some data");
        $this->outStream->flush();
        $this->outStream->close();

        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Could not flush stream: fflush() expects parameter 1 to be resource, null given');

        $this->outStream->flush();
    }
}
