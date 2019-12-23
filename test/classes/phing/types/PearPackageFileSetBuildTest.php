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

declare(strict_types=1);

/**
 * Testcases for phing.types.PearPackageFileSet
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.types
 */
class PearPackageFileSetBuildTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!class_exists('PEAR_Config')) {
            $this->markTestSkipped('This test requires PEAR to be installed');
        }

        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_STRICT);

        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/PearPackageFileSetBuildTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testConsoleGetopt(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Console' . DIRECTORY_SEPARATOR . 'Getopt.php');
    }

    /**
     * @return void
     */
    public function testDirect(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Console' . DIRECTORY_SEPARATOR . 'Getopt.php');
    }

    /**
     * @return void
     */
    public function testRoleDoc(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs(DIRECTORY_SEPARATOR . 'Archive_Tar.txt');
    }

    /**
     * @return void
     */
    public function testCopyConsoleGetopt(): void
    {
        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @return void
     */
    public function testCopyMapperConsoleGetopt(): void
    {
        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @return void
     */
    public function testPackageXmlFilelist(): void
    {
        $registry = new PEAR_Registry();
        if (!$registry->channelExists('pear.phpunit.de')) {
            $this->markTestSkipped('PEAR channel pear.phpunit.de not registered');
        }

        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('CONTRIBUTING.md');
    }

    /**
     * @return void
     */
    public function testPackageXmlIncludeCondition(): void
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
