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

namespace Phing\Test\Task\Optional\PHPStan;

use Phing\Task\Ext\Phpstan\CommandBuilder\PHPStanAnalyseCommandBuilder;
use Phing\Task\Ext\Phpstan\PHPStanTask;
use PHPUnit\Framework\TestCase;

class PHPStanAnalyseCommandBuilderTest extends TestCase
{
    /** @var PHPStanAnalyseCommandBuilder */
    private $builder;

    public function setUp(): void
    {
        $this->builder = new PHPStanAnalyseCommandBuilder();
    }

    public function testItHandlesCommandOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('phpstan');
        $task->setCommand('analyse');

        $task->setConfiguration('anyConfiguration');
        $task->setLevel('anyLevel');
        $task->setNoProgress(true);
        $task->setDebug(true);
        $task->setAutoloadFile('anyAutoloadFile');
        $task->setErrorFormat('anyErrorFormat');
        $task->setMemoryLimit('anyMemoryLimit');
        $task->setPaths('path1 path2');

        $this->builder->build($task);
        $expectedCommand = <<< 'CMD'
            Executing 'phpstan' with arguments:
            'analyse'
            '--configuration=anyConfiguration'
            '--level=anyLevel'
            '--no-progress'
            '--debug'
            '--autoload-file=anyAutoloadFile'
            '--error-format=anyErrorFormat'
            '--memory-limit=anyMemoryLimit'
            'path1 path2'
            The ' characters around the executable and arguments are not part of the command.
            CMD;

        $this->assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
