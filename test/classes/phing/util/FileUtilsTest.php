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
 *
 * @package phing.util
 */

/**
 * Testcases for phing.util.FileUtils
 *
 * @author  Siad Ardroumli |siad.ardroumli@gmail.com>
 * @package phing.util
 */
class FileUtilsTest extends BuildFileTest
{
    /** @var FileUtils $fu */
    private $fu;

    public function setUp(): void
    {
        $this->fu = new FileUtils();
        $this->configureProject(PHING_TEST_BASE . "/etc/util/fileutils.xml");
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
        self::assertFalse($this->fu->contentEquals(new PhingFile(__FILE__), new PhingFile('does_not_exists')));
        self::assertFalse($this->fu->contentEquals(new PhingFile('does_not_exists'), new PhingFile(__FILE__)));
        self::assertFalse($this->fu->contentEquals(new PhingFile(__DIR__), new PhingFile(__DIR__)));
        self::assertFalse($this->fu->contentEquals(new PhingFile(__FILE__), new PhingFile(__DIR__)));
        self::assertFalse($this->fu->contentEquals(new PhingFile(__DIR__), new PhingFile(__FILE__)));
        self::assertTrue($this->fu->contentEquals(new PhingFile(__FILE__), new PhingFile(__FILE__)));
    }
}
