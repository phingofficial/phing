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

namespace Phing\Task\Optional\PHPStan;

use Phing\Exception\BuildException;
use Phing\Task\Optional\PHPStan\PHPStanCommandBuilderFake;
use Phing\Task\Ext\Phpstan\PHPStanTask;
use PHPUnit\Framework\TestCase;

class PHPStanCommandBuilderTest extends TestCase
{

    /** @var PHPStanCommandBuilderFake */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new PHPStanCommandBuilderFake();
    }

    public function testItHandleBaseCommandParts(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('anyExecutable');
        $task->setCommand('anyCommand');

        $this->builder->build($task);

        $cmd = <<<CMD
Executing 'anyExecutable' with arguments:
'anyCommand'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($cmd, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }

    public function testItFailsWhenExecutableNotSet(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('');

        $this->expectException(BuildException::class);

        $this->builder->build($task);
    }

    public function testItHandlesCommonOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('anyExecutable');
        $task->setCommand('anyCommand');

        $task->setHelp(true);
        $task->setQuiet(true);
        $task->setVersion(true);
        $task->setANSI(true);
        $task->setNoANSI(true);
        $task->setNoInteraction(true);
        $task->setVerbose(true);

        $this->builder->build($task);

        $expectedCommand = <<<CMD
Executing 'anyExecutable' with arguments:
'anyCommand'
'--help'
'--quiet'
'--version'
'--ansi'
'--no-ansi'
'--no-interaction'
'--verbose'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
