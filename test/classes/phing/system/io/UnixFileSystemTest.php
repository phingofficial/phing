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
 * Unit test for UnixFileSystem
 *
 * @author Michiel Rook <mrook@php.net>
 * @package phing.system.io
 */
class UnixFileSystemTest extends TestCase
{
    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->fs = new UnixFileSystem();
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function testCompare(): void
    {
        $f1 = new PhingFile(__FILE__);
        $f2 = new PhingFile(__FILE__);

        self::assertEquals($this->fs->compare($f1, $f2), 0);
    }

    /**
     * @return void
     */
    public function testHomeDirectory1(): void
    {
        self::assertEquals($this->fs->normalize('~/test'), '~/test');
    }

    /**
     * @return void
     */
    public function testHomeDirectory2(): void
    {
        self::assertEquals($this->fs->normalize('/var/~test'), '/var/~test');
    }

    /**
     * @return void
     */
    public function testHomeDirectory3(): void
    {
        self::assertEquals($this->fs->normalize('~test'), '~test');
    }
}
