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

namespace Phing\Test\Util;

use Phing\Io\File;
use Phing\Util\Properties;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Properties class.
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @internal
 * @coversNothing
 */
class PropertiesTest extends TestCase
{
    /**
     * @var Properties
     */
    private $props;

    public function setUp(): void
    {
        $this->props = new Properties();
    }

    public function tearDown(): void
    {
        unset($this->props);
    }

    public function testComments(): void
    {
        $file = new File(PHING_TEST_BASE . '/etc/system/util/comments.properties');
        $this->props->load($file);

        $this->assertEquals(
            'Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1',
            $this->props->getProperty('useragent')
        );
        $this->assertEquals('Testline1', $this->props->getProperty('testline1'));
        $this->assertEquals('Testline2', $this->props->getProperty('testline2'));
        $this->assertEquals(true, $this->props->getProperty('testline3'));
        $this->assertEquals(false, $this->props->getProperty('testline4'));
    }

    public function testEmpty(): void
    {
        $this->assertTrue($this->props->isEmpty());
    }

    public function testAppendPropertyValues(): void
    {
        $this->props->append('t', 'a');
        $this->props->append('t', 'b');
        $this->assertEquals('a,b', $this->props->get('t'));
    }

    public function testToString(): void
    {
        $this->props->put('a', 'b');

        $this->assertEquals('a=b' . PHP_EOL, (string) $this->props);
    }

    public function testStore(): void
    {
        $file = new File(PHING_TEST_BASE . '/tmp/props');
        $this->props->put('t', 'a');
        $this->props->store($file, 'header');
        $this->assertFileExists($file->getPath());
        $this->assertEquals('# header' . PHP_EOL . 't=a' . PHP_EOL, file_get_contents($file->getPath()));
        unlink($file->getPath());
    }
}
