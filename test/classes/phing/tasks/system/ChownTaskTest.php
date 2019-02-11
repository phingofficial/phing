<?php

/**
 * Tests the Chown Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ChownTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ChownTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testChangeGroup()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->markTestSkipped("chown tests don't work on Windows");
        }

        $userinfo = posix_getpwuid(posix_geteuid());
        $username = $userinfo['name'];

        //we may change the group only if we belong to it
        //so find a group that we are in
        $group = null;
        foreach (['users', 'www-data', 'cdrom'] as $groupname) {
            $grpinfo = posix_getgrnam($groupname);
            if ($grpinfo['gid'] == $userinfo['gid']) {
                //current group id, the file has that group anyway
                continue;
            }
            if (!is_array($grpinfo['members'])) {
                continue;
            }
            if (in_array($username, $grpinfo['members'])) {
                $group = $grpinfo;
                break;
            }
        }
        if ($group === null) {
            $this->markTestSkipped('found no group we can change ownership to');
        }

        $this->project->setUserProperty(
            'targetuser',
            $username . '.' . $group['name']
        );
        $this->executeTarget(__FUNCTION__);
        $a = stat(PHING_TEST_BASE . '/etc/tasks/system/tmp/chowntestA');
        $b = stat(PHING_TEST_BASE . '/etc/tasks/system/tmp/chowntestB');

        $this->assertNotEquals(
            $group['gid'],
            $a['gid'],
            'chowntestA group should not have changed'
        );
        $this->assertEquals(
            $group['gid'],
            $b['gid'],
            'chowntestB group should have changed'
        );
    }
}
