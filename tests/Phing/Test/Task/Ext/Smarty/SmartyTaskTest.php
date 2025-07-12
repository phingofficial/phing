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

namespace Phing\Test\Task\Ext\Smarty;

use Phing\Test\Support\BuildFileTest;

class SmartyTaskTest extends BuildFileTest
{
    protected function setUp(): void
    {
        if (!class_exists('\Smarty\Smarty')) {
            $this->markTestSkipped('The Smarty tasks depend on the smarty/smarty package being installed.');
        }
        $buildXmlFile = PHING_TEST_BASE . '/etc/tasks/ext/smarty/SmartyTaskTest.xml';
        $this->configureProject($buildXmlFile);
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testRenderSimpleTemplate(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertStringEqualsFile(PHING_TEST_BASE . "/etc/tasks/ext/smarty/tmp/test.txt", "Foo\n");
    }
}
