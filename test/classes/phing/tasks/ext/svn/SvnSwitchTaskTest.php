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
require_once dirname(__FILE__) . '/../GitTasks/GitTestsHelper.php';

/**
 * @author Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.ext
 */
class SvnSwitchTaskTest extends BuildFileTest { 
    public function setUp() { 
        if (is_readable(PHING_TEST_BASE . '/tmp/svn')) {
            // make sure we purge previously created directory
            // if left-overs from previous run are found
            GitTestsHelper::rmdir(PHING_TEST_BASE . '/tmp/svn');
        }
        // set temp directory used by test cases
        mkdir(PHING_TEST_BASE . '/tmp/svn');

        $this->configureProject(PHING_TEST_BASE 
                              . '/etc/tasks/ext/svn/SvnSwitchTest.xml');
    }

    public function tearDown()
    {
        GitTestsHelper::rmdir(PHING_TEST_BASE . '/tmp/svn');
    }

    public function testSwitchSimple()
    {
        $repository = PHING_TEST_BASE . '/tmp/svn';
        $this->executeTarget('switchSimple');
        $this->assertInLogs("Checking out SVN repository to '" . $repository . "'");
        $this->assertInLogs("Switching SVN repository at '$repository' to 'https://github.com/phingofficial/phing/tags/2.4.12/etc'");
    }
}
