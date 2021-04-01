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
use Phing\Io\FileUtils;
use Phing\Test\Support\BuildFileTest;

/**
 * Testcases for phing.util.FileUtils.
 *
 * @author  Siad Ardroumli |siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class FileUtilsTest extends BuildFileTest
{
    /** @var FileUtils */
    private $fu;

    public function setUp(): void
    {
        $this->fu = new FileUtils();
        $this->configureProject(PHING_TEST_BASE . '/etc/util/fileutils.xml');
        $this->executeTarget('dummy');
    }

    public function tearDown(): void
    {
        $this->fu = null;
    }

    /**
     * @test
     */
    public function contentEquals()
    {
        $this->assertFalse($this->fu->contentEquals(new File(__FILE__), new File('does_not_exists')));
        $this->assertFalse($this->fu->contentEquals(new File('does_not_exists'), new File(__FILE__)));
        $this->assertFalse($this->fu->contentEquals(new File(__DIR__), new File(__DIR__)));
        $this->assertFalse($this->fu->contentEquals(new File(__FILE__), new File(__DIR__)));
        $this->assertFalse($this->fu->contentEquals(new File(__DIR__), new File(__FILE__)));
        $this->assertTrue($this->fu->contentEquals(new File(__FILE__), new File(__FILE__)));
    }

    /**
     * @test
     */
    public function copyFile()
    {
        $this->fu->copyFile(new File(__FILE__), new File('tmp/test.php'), $this->getProject());

        try {
            $this->assertFileExists('tmp/test.php');
        } finally {
            @unlink('tmp/test.php');
        }
    }
}
