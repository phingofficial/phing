<?php

/**
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
 *
 * @package phing.util
 */


/**
 * Testcases for phing.types.PearPackageFileSet
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.types
 */
class PearPackageFileSetBuildTest extends BuildFileTest
{
    public function setUp(): void    {
        if (!class_exists('PEAR_Config')) {
            $this->markTestSkipped("This test requires PEAR to be installed");
        }

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("PEAR tests do not run on HHVM");
        }

        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_STRICT);

        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/PearPackageFileSetBuildTest.xml'
        );
    }

    public function testConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Console' . DIRECTORY_SEPARATOR . 'Getopt.php');
    }

    public function testDirect()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Console' . DIRECTORY_SEPARATOR . 'Getopt.php');
    }

    public function testRoleDoc()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs(DIRECTORY_SEPARATOR . 'Archive_Tar.txt');
    }

    public function testCopyConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testCopyMapperConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testPackageXmlFilelist()
    {
        $registry = new PEAR_Registry();
        if (!$registry->channelExists('pear.phpunit.de')) {
            $this->markTestSkipped('PEAR channel pear.phpunit.de not registered');
        }

        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('CONTRIBUTING.md');
    }

    public function testPackageXmlIncludeCondition()
    {
        $registry = new PEAR_Registry();
        if (!$registry->channelExists('pear.phpunit.de')) {
            $this->markTestSkipped('PEAR channel pear.phpunit.de not registered');
        }

        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ResultPrinter.php');
        $this->assertNotInLogs('BaseTestRunner.php');
    }
}
