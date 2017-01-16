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
 * Tests the Symlink Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.system
 */
class SymlinkTaskTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/ext/SymlinkTaskTest.xml"
        );
        $this->executeTarget("setUp");
    }

    public function tearDown()
    {
        $this->executeTarget("tearDown");
    }

    public function testCreateDouble()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testCreateDoubleHanging()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testCreateOverFile()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testDeleteOfBrokenLink()
    {
        $this->executeTarget(__FUNCTION__);
        $output = $this->getProject()->getProperty('output');
        $this->assertFileNotExists($output . '/link');
    }

    public function testDeleteLinkToParent()
    {
        $this->executeTarget(__FUNCTION__);
        $output = $this->getProject()->getProperty('output');
        $this->assertFileNotExists($output . '/link');
    }

    public function testDeleteWithNoPermissionToRenameTarget()
    {
        $this->executeTarget(__FUNCTION__);
        $output = $this->getProject()->getProperty('output');
        $this->assertFileNotExists($output . '/link');
    }

    public function testDeleteLinkInSameDirAsBuildFile()
    {
        $this->executeTarget(__FUNCTION__);
    }
}
