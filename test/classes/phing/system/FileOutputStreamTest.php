<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit test for FileOutputStream.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.system
 */
class FileOutputStreamTest extends TestCase
{
    /**
     * @var FileOutputStream
     */
    private $outStream;

    /**
     * @var PhingFile
     */
    private $tmpFile;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->tmpFile   = new PhingFile(PHING_TEST_BASE . '/tmp/' . static::class . '.txt');
        $this->outStream = new FileOutputStream($this->tmpFile);
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    protected function tearDown(): void
    {
        if (is_object($this->outStream)) {
            $this->outStream->close();
        }
        unlink((string) $this->tmpFile);
    }

    /**
     * @param string $contents
     *
     * @return void
     */
    public function assertFileContents(string $contents): void
    {
        $actual = file_get_contents((string) $this->tmpFile);
        $this->assertEquals(
            $contents,
            $actual,
            "Expected file contents to match; expected '" . $contents . "', actual '" . $actual . "'"
        );
    }

    /**
     * @return void
     *
     * @throws IOException
     */
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

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testFlush(): void
    {
        $this->outStream->write('Some data');
        $this->outStream->flush();
        $this->outStream->close();

        $this->expectException(IOException::class);

        $this->outStream->flush();
    }
}
