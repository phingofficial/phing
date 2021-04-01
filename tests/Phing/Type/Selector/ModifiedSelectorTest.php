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

namespace Phing\Test\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Test\Support\BuildFileTest;
use Phing\Type\Selector\ModifiedSelector;

/**
 * Class ModifiedSelectorTest
 */
class ModifiedSelectorTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/ModifiedSelectorTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testOneFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('phing.dir');
        $this->assertFileExists($output . '/cc.properties');
    }

    public function testWithParam(): void
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('phing.dir');
        $this->assertFileExists($output . '/cc.properties');
    }

    /** Test correct use of cache names. */
    public function testValidateWrongCache()
    {
        $name = "this-is-not-a-valid-cache-name";
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Cache must be set');
        $sel = new ModifiedSelector();
        $sel->setCache($name);
        $sel->validate();
    }

    /** Test correct use of algorithm names. */
    public function testValidateWrongAlgorithm()
    {
        $name = "this-is-not-a-valid-algorithm-name";
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Algorithm must be set');
        $sel = new ModifiedSelector();
        $sel->setAlgorithm($name);
        $sel->validate();
    }
}
