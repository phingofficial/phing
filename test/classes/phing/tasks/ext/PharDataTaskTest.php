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

/**
 * Tests the Manifest Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class PharDataTaskTest extends BuildFileTest
{
    public function setUp()
    {
        if (!extension_loaded('phar')) {
                $this->markTestSkipped("PharDataTask require either PHP 5.3 or better or the PECL's Phar extension");
        }

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("PHAR tests do not run on HHVM");
        }

        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/ext/PharDataTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown()
    {
        $this->executeTarget("clean");
    }

    public function testGenerateWithoutBasedir()
    {
        $this->expectBuildException(__FUNCTION__, 'basedir attribute must be set');
    }

    public function testGenerateTar()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.tar");
        $this->assertNotFalse($manifestFile);
    }

    public function testGenerateTarGz()
    {
        $this->skipIfCompressionNotSupported(Phar::GZ);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.tar.gz");
        $this->assertNotFalse($manifestFile);
    }

    public function testGenerateTarBz2()
    {
        $this->skipIfCompressionNotSupported(Phar::BZ2);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.tar.bz2");
        $this->assertNotFalse($manifestFile);
    }

    public function testGenerateZip()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }

    public function testGenerateZipGz()
    {
        $this->skipIfCompressionNotSupported(Phar::GZ);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }

    public function testGenerateZipBz2()
    {
        $this->skipIfCompressionNotSupported(Phar::BZ2);

        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/etc/tasks/ext/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }

    private function skipIfCompressionNotSupported(int $compression): void
    {
        if (!Phar::canCompress($compression)) {
            $this->markTestSkipped('This test require Phar to support ' . $compression . ' compression');
        }
    }
}
