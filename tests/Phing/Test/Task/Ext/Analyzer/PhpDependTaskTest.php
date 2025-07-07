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

namespace Phing\Test\Task\Ext\Analyzer;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests for PhpDependTask.
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @internal
 * @requires PHP < 8.1
 */
class PhpDependTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (!class_exists('\PDepend\TextUI\Runner')) {
            $this->markTestSkipped('The PDepend tasks depend on the pdepend/pdepend package being installed.');
        }
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/pdepend/build.xml');
    }

    public function testLoggerSummary(): void
    {
        ob_start();
        $this->executeTarget(__FUNCTION__);
        ob_end_clean();
        $filename = PHING_TEST_BASE . '/etc/tasks/ext/pdepend/tempoutput';
        $this->assertFileExists($filename);
        unlink($filename);
    }

    public function testAnalyzer(): void
    {
        ob_start();
        $this->executeTarget(__FUNCTION__);
        ob_end_clean();
        $filename = PHING_TEST_BASE . '/etc/tasks/ext/pdepend/tempoutput';
        $this->assertFileExists($filename);
        unlink($filename);
    }
}
