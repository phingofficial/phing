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

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the Chown Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @requires OS ^(?:(?!Win).)*$
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
        if (null === $group) {
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
