<?php

require_once 'phing/BuildFileTest.php';

/**
 * Tests the Mkdir Task
 *
 * @package phing.tasks.system
 */
class MkdirTaskTest extends BuildFileTest
{
    private $originalUmask;

    public function setUp()
    {
        $this->originalUmask = umask();
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/MkdirTaskTest.xml'
        );

        $this->executeTarget('clean');
        mkdir(PHING_TEST_BASE . '/etc/tasks/system/tmp');
    }

    public function tearDown()
    {
        $this->executeTarget('clean');
        umask($this->originalUmask);
    }

    /**
     * @dataProvider umaskIsHonouredWhenNotUsingModeArgumentDataProvider
     */
    public function testUmaskIsHonouredWhenNotUsingModeArgument($umask, $expectedDirMode)
    {
        umask($umask);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', $expectedDirMode);
    }

    public function umaskIsHonouredWhenNotUsingModeArgumentDataProvider() {
        return [
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

    public function testParentDirectoriesHaveDefaultPermissions()
    {
        umask(0077);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a', 0700);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a/b', 0700);
        $this->assertFileModeIs(PHING_TEST_BASE . '/etc/tasks/system/tmp/a/b/c', 0777);
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

    private function markTestSkippedIfAclIsNotSupported() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('POSIX ACL tests cannot be run on Windows.');
        } else {
            exec('which setfacl', $dummyOutput, $exitCode);
            if ($exitCode !== 0) {
                $this->markTestSkipped('"setfacl" command not found. POSIX ACL tests cannot be run.');
            }
        }
    }
}
