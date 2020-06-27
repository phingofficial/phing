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

/**
 * Tests the Mkdir Task
 *
 * @package phing.tasks.system
 */
class MkdirTaskTest extends BuildFileTest
{
    private $originalUmask;

    public function setUp(): void
    {
        $this->originalUmask = umask();
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/MkdirTaskTest.xml'
        );

        $this->executeTarget('clean');
        mkdir(PHING_TEST_BASE . '/etc/tasks/system/tmp');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
        umask($this->originalUmask);
    }

    /**
     * @dataProvider umaskIsHonouredWhenNotUsingModeArgumentDataProvider
     */
    public function testUmaskIsHonouredWhenNotUsingModeArgument($umask, $expectedDirMode)
    {
        if ($umask !== 0) {
            $this->markTestSkippedIfOsIsWindows();
        }

        umask($umask);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', $expectedDirMode);
    }

    public function umaskIsHonouredWhenNotUsingModeArgumentDataProvider()
    {
        return [
            [0000, 0777],
            [0007, 0770],
            [0077, 0700],
        ];
    }

    public function testUmaskIsIgnoredWhenUsingModeArgument()
    {
        umask(0077);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 0777);
    }

    /**
     * @dataProvider parentDirectoriesHaveDefaultPermissionsDataProvider
     */
    public function testParentDirectoriesHaveDefaultPermissions($umask, $expectedModeA, $expectedModeB)
    {
        if ($umask !== 0) {
            $this->markTestSkippedIfOsIsWindows();
        }

        umask($umask);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', $expectedModeA);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a/b', $expectedModeB);
    }

    public function parentDirectoriesHaveDefaultPermissionsDataProvider()
    {
        return [
            [
                'umask' => 0000,
                'expectedPermissionsOfA' => 0777,
                'expectedPermissionsOfB' => 0555,
            ],
            [
                'umask' => 0077,
                'expectedPermissionsOfA' => 0700,
                'expectedPermissionsOfB' => 0555,
            ],
        ];
    }

    public function testAclIsInheritedFromParentDirectoryDefaultAcl()
    {
        $this->markTestSkippedIfAclIsNotSupported();

        shell_exec('setfacl --remove-default ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');
        shell_exec('setfacl --modify default:user:root:rwx ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');

        $this->executeTarget(__FUNCTION__);

        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'user:root:rwx');
        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'default:user:root:rwx');
    }

    public function testUmaskIsIgnoredWhenAclIsUsedAndTaskDoesNotHaveModeArgument()
    {
        $this->markTestSkippedIfAclIsNotSupported();

        shell_exec('setfacl --remove-default ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');
        shell_exec('setfacl --modify default:user:root:rwx ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');

        umask(0077);
        $this->executeTarget(__FUNCTION__);

        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'mask::rwx');
        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'user:root:rwx');
        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'default:user:root:rwx');
    }

    public function testUmaskIsIgnoredWhenAclIsUsedAndTaskHasModeArgument()
    {
        $this->markTestSkippedIfAclIsNotSupported();

        shell_exec('setfacl --remove-default ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');
        shell_exec('setfacl --modify default:user:root:rwx ' . PHING_TEST_BASE . '/etc/tasks/system/tmp');

        umask(0077);
        $this->executeTarget(__FUNCTION__);

        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'mask::rwx');
        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'user:root:rwx');
        $this->assertFileAclContains(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 'default:user:root:rwx');
    }

    /**
     * @param string $filename
     * @param int $mode
     */
    private function assertFileModeIs($filename, $mode)
    {
        $stat = stat($filename);

        $this->assertSame(
            sprintf("%03o", $mode),
            sprintf("%03o", $stat['mode'] & 0777),
            sprintf('Failed asserting that file mode of "%s" is %03o', $filename, $mode)
        );
    }

    /**
     * @param string $filename
     * @param string $expectedAclEntry
     */
    private function assertFileAclContains($filename, $expectedAclEntry)
    {
        $output = shell_exec('getfacl --omit-header --absolute-names ' . escapeshellarg($filename));

        $aclEntries = preg_split('/[\r\n]+/', $output, -1,  PREG_SPLIT_NO_EMPTY);

        $matchFound = false;
        foreach ($aclEntries as $aclEntry) {
            if ($aclEntry === $expectedAclEntry) {
                $matchFound = true;
                break;
            }
        }

        $this->assertTrue(
            $matchFound,
            sprintf(
                'Failed asserting that ACL of file "%s" contains "%s" entry.' . "\n"
                . 'Following ACL entries are present:' . "\n%s\n",
                $filename,
                $expectedAclEntry,
                implode("\n", $aclEntries)
            )
        );
    }

    private function markTestSkippedIfOsIsWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('POSIX ACL tests cannot be run on Windows.');
        }
    }

    private function markTestSkippedIfAclIsNotSupported() {
        $this->markTestSkippedIfOsIsWindows();

        exec('which setfacl', $dummyOutput, $exitCode);
        if ($exitCode !== 0) {
            $this->markTestSkipped('"setfacl" command not found. POSIX ACL tests cannot be run.');
        }
    }
}
