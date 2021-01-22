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

namespace Phing\Type\Selector;
use BuildFileTest;

/**
 * Class DifferentSelectorTest
 *
 * Test cases for different selectors.
 */
class DifferentSelectorTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/DifferentSelectorTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testSameTime()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $result = $project->getProperty('result');
        $this->assertFileDoesNotExist($result . '/a.txt');
    }

    public function testDifferentTime()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $result = $project->getProperty('result');
        $this->assertFileExists($result . '/b.txt');
    }
}
