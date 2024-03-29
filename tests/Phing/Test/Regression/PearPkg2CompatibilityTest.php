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

namespace Phing\Test\Regression;

use Exception;
use Phing\Test\Support\BuildFileTest;

/**
 * Regression test for tickets
 * http://www.phing.info/trac/ticket/524.
 *
 * TODO: skip when user doesn't have pear installed (you cannot check for the class name, because
 *       it is included via composer)
 *
 * @internal
 */
class PearPkg2CompatibilityTest extends BuildFileTest
{
    private $savedErrorLevel;

    public function setUp(): void
    {
        $this->savedErrorLevel = error_reporting();
        error_reporting(E_ERROR);
        $buildFile = PHING_TEST_BASE . '/etc/regression/524/build.xml';
        $this->configureProject($buildFile);

        if (!class_exists('PEAR_PackageFileManager', false)) {
            $this->markTestSkipped('This test requires PEAR_PackageFileManager to be installed');
        }

        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        error_reporting($this->savedErrorLevel);
        $this->executeTarget('teardown');
    }

    public function testInactiveMaintainers(): void
    {
        $this->executeTarget('inactive');
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertStringContainsString('<active>no</active>', $content);
    }

    public function testActiveMaintainers(): void
    {
        $this->executeTarget('active');
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertStringContainsString('<active>yes</active>', $content);
    }

    public function testNotSetMaintainers(): void
    {
        $this->executeTarget('notset');
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertStringContainsString('<active>yes</active>', $content);
    }

    protected function assertPreConditions(): void
    {
        try {
            $this->executeTarget('inactive');
        } catch (Exception $e) {
            if (false !== strpos($e->getMessage(), 'Unknown channel')) {
                $this->markTestSkipped($e->getMessage());
            }
        }
    }
}
