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

use Phing\Test\Support\BuildFileTest;

/**
 * Class ReadWriteTest.
 *
 * Test cases for isReadable/isWritable selectors.
 *
 * @internal
 * @coversNothing
 */
class ReadWriteTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/ReadWriteTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testReadable(): void
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertTrue(is_readable(sprintf('%s/%s', $output, $file)));
    }

    public function testWritable(): void
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertTrue(is_writable(sprintf('%s/%s', $output, $file)));
    }

    public function testUnwritable(): void
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertFalse(is_writable(sprintf('%s/%s', $output, $file)));
    }
}
