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

/**
 * @author <a href="mailto:stefan.bodewig@epost.de">Stefan Bodewig</a>
 * @package phing.filters
 */
class LineContainsTest extends BuildFileTest
{
    /**
     * @var FileUtils
     */
    private $fu;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/filters/linecontains.xml');
        $this->fu = new FileUtils();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testLineContains(): void
    {
        $this->executeTarget('testLineContains');

        $expected = $this->getProject()->resolveFile('expected/linecontains.test');
        $result   = $this->getProject()->resolveFile('result/linecontains.test');
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testLineContainsNegate(): void
    {
        $this->executeTarget(__FUNCTION__);

        $expected = $this->getProject()->resolveFile('expected/linecontains-negate.test');
        $result   = $this->getProject()->resolveFile('result/linecontains.test');
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testLineContainsMatchAny(): void
    {
        $this->executeTarget(__FUNCTION__);

        $expected = $this->getProject()->resolveFile('expected/linecontains-matchany.test');
        $result   = $this->getProject()->resolveFile('result/linecontains.test');
        $this->assertFileEquals($expected->getAbsolutePath(), $result->getAbsolutePath());
    }
}
