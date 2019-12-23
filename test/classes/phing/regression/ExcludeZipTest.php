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

declare(strict_types=1);

/**
 * Regression test for ticket http://www.phing.info/trac/ticket/137
 * - Excluded files may be included in Zip/Tar tasks
 *
 * @package phing.regression
 * @requires extension zip
 */
class ExcludeZipTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/regression/137/build.xml');
    }

    /**
     * @return void
     */
    public function testZipTask(): void
    {
        $this->executeTarget('main');

        $expected       = 'Adding ./.git to archive.';
        $representation = [];
        foreach ($this->logBuffer as $log) {
            $representation[] = sprintf('[msg="%s",priority=%s]', $log['message'], $log['priority']);
        }

        $this->assertIsArray($this->logBuffer);
        $this->assertGreaterThanOrEqual(1, count($this->logBuffer));

        foreach ($this->logBuffer as $log) {
            $this->assertStringNotContainsString($expected, $log['message'], sprintf("Expected to find '%s' in logs: %s", $expected, var_export($representation, true)));
        }
    }
}
