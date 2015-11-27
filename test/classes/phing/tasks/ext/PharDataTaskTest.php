<?php
/*
 *  $Id$
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

require_once 'phing/BuildFileTest.php';

/**
 * Tests the Manifest Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.system
 */
class PharDataTaskTest extends BuildFileTest
{
    public function setUp()
    {
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

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateWithoutBasedir()
    {
        $this->expectBuildException(__FUNCTION__, 'basedir attribute must be set');
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateTar()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.tar");
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateTarGz()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.tar.gz");
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateTarBz2()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.tar.bz2");
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateZip()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateZipGz()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }

    /**
     * @requires PHP 5.3.2
     */
    public function testGenerateZipBz2()
    {
        $this->executeTarget(__FUNCTION__);
        $manifestFile = realpath(PHING_TEST_BASE . "/tmp/phar/archive.zip");
        $this->assertNotFalse($manifestFile);
    }
}
