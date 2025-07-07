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

use Phing\Test\Support\BuildFileTest;

/**
 * Regression test for ticket http://www.phing.info/trac/ticket/137
 * - Excluded files may be included in Zip/Tar tasks.
 *
 * @requires extension zip
 * @internal
 */
class ExcludeZipTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/regression/137/build.xml');
    }

    public function testZipTask(): void
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget('main');

        $expected = 'Adding ./.git to archive.';
        $representation = [];
        foreach ($this->logBuffer as $log) {
            $representation[] = "[msg=\"{$log['message']}\",priority={$log['priority']}]";
        }

        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log['message'], $expected)) {
                $this->fail(
                    sprintf("Expected to find '%s' in logs: %s", $expected, var_export($representation, true))
                );
            }
        }
    }
}
