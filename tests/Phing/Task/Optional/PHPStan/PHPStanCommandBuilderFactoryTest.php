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

use Phing\Exception\BuildException;
use Phing\Task\Ext\Phpstan\CommandBuilder\PHPStanAnalyseCommandBuilder;
use Phing\Task\Ext\Phpstan\CommandBuilder\PHPStanCommandBuilderFactory;
use Phing\Task\Ext\Phpstan\CommandBuilder\PHPStanHelpCommandBuilder;
use Phing\Task\Ext\Phpstan\CommandBuilder\PHPStanListCommandBuilder;
use Phing\Task\Ext\Phpstan\PHPStanTask;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PHPStanCommandBuilderFactoryTest extends TestCase
{
    /** @var PHPStanCommandBuilderFactory */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new PHPStanCommandBuilderFactory();
    }

    public function testItCanCreateAnalyseCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('analyse');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanAnalyseCommandBuilder::class, $builder);
    }

    public function testItCanCreateAnalyzeCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('analyze');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanAnalyseCommandBuilder::class, $builder);
    }

    public function testItCanCreateListCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('list');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanListCommandBuilder::class, $builder);
    }

    public function testItCanCreateHelpCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('help');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanHelpCommandBuilder::class, $builder);
    }

    public function testItThrowsExceptionWhenCommandIsUnknown(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('any unknown');

        $this->expectException(BuildException::class);

        $this->factory->createBuilder($task);
    }
}
