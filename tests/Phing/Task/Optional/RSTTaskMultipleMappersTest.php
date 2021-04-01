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

namespace Phing\Test\Task\Optional;

use Phing\Exception\BuildException;
use Phing\Test\Support\BuildFileTest;

/**
 * Unit test for reStructuredText rendering task.
 *
 * PHP version 5
 *
 * @category   Tasks
 *
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 *
 * @see       http://www.phing.info/
 *
 * @internal
 * @coversNothing
 */
class RSTTaskMultipleMappersTest extends BuildFileTest
{
    public function testMultipleMappers()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/build-error-multiple-mappers.xml'
        );

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Cannot define more than one mapper');

        $this->executeTarget(__FUNCTION__);
    }
}
