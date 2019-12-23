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
 * Tests the Manifest Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class PharDataTaskTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     *
     * @requires extension phar
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/PharDataTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * @return void
     */
    public function testGenerateWithoutBasedir(): void
    {
        $this->expectBuildException(__FUNCTION__, 'basedir attribute must be set');
    }

    /**
     * @return void
     */
    public function testGenerateTar(): void
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.tar');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @return void
     */
    public function testGenerateTarGz(): void
    {
        $this->skipIfCompressionNotSupported(Phar::GZ);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.tar.gz');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @return void
     */
    public function testGenerateTarBz2(): void
    {
        $this->skipIfCompressionNotSupported(Phar::BZ2);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.tar.bz2');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @return void
     */
    public function testGenerateZip(): void
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.zip');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @return void
     */
    public function testGenerateZipGz(): void
    {
        $this->skipIfCompressionNotSupported(Phar::GZ);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.zip');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @return void
     */
    public function testGenerateZipBz2(): void
    {
        $this->skipIfCompressionNotSupported(Phar::BZ2);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . '/etc/tasks/ext/tmp/phar/archive.zip');
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @param int $compression
     *
     * @return void
     */
    private function skipIfCompressionNotSupported(int $compression): void
    {
        if (!Phar::canCompress($compression)) {
            self::markTestSkipped('This test require Phar to support ' . $compression . ' compression');
        }
    }
}
