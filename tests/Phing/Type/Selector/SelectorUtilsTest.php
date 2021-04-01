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

namespace Phing\Test\Type\Selector;

use Phing\Io\File;
use Phing\Io\FileUtils;
use Phing\Type\Selector\SelectorUtils;
use PHPUnit\Framework\TestCase;

/**
 * Class SelectorUtilsTest
 *
 * Test cases for SelectorUtils
 */
class SelectorUtilsTest extends TestCase
{
    /**
     * @var SelectorUtils
     */
    private $selectorUtils;

    protected function setUp(): void
    {
        $this->selectorUtils = SelectorUtils::getInstance();
    }

    /**
     * Inspired by @link https://www.phing.info/trac/ticket/796
     */
    public function testDoNotIncludeSelfWhenMatchingSubdirectoriesAndFiles()
    {
        $result = $this->selectorUtils->matchPath("**/*", "");
        $this->assertFalse($result);
    }

    /**
     * Inspired by @link https://www.phing.info/trac/ticket/1264
     */
    public function testDoNotIncludePrefix()
    {
        $this->assertFalse($this->selectorUtils->matchPath(
            "**/example.php",
            "vendor/phplot/phplot/contrib/color_range.example.php"
        ));
    }

    /**
     * Inspired by @link https://github.com/phingofficial/phing/issues/593
     */
    public function testIncludePathsInBase()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->assertTrue($this->selectorUtils->matchPath('**\domain.ext\**', 'domain.ext\foo'));
        } else {
            $this->assertTrue($this->selectorUtils->matchPath("**/domain.ext/**", "domain.ext/foo"));
        }
    }

    public function testRemoveWhitespace()
    {
        $ret = $this->selectorUtils::removeWhitespace(" foo ");
        $this->assertEquals("foo", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\tbar\t");
        $this->assertEquals("bar", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\nfoo\t");
        $this->assertEquals("foo", $ret);
        $ret = $this->selectorUtils::removeWhitespace("\rfoo\r");
        $this->assertEquals("foo", $ret);
    }

    /**
     * Non Existing Source File Causes Out Of Date To Return False
     *
     */
    public function testNonExistingSourceFileCausesOutOfDateToReturnFalse()
    {
        $sourceFile = new File("doesNotExist");
        $targetFile = new File(__FILE__);
        $ret = $this->selectorUtils::isOutOfDate($sourceFile, $targetFile, 0);
        $this->assertEquals(false, $ret);
    }

    public function testNonExistingTargetFileCausesOutOfDateToReturnTrue()
    {
        $sourceFile = new File(__FILE__);
        $targetFile = new File("doesNotExist");
        $ret = $this->selectorUtils::isOutOfDate($sourceFile, $targetFile, 0);
        $this->assertEquals(true, $ret);
    }

    /**
     * Test Granularity of isOutOfDate
     *
     */
    public function testOutOfDate()
    {
        $source = new File(tempnam(FileUtils::getTempDir(), 'src'));
        sleep(3);
        $target = new File(tempnam(FileUtils::getTempDir(), 'tgt'));
        $ret = $this->selectorUtils::isOutOfDate($source, $target, 20);
        $this->assertEquals(false, $ret);
    }
}
