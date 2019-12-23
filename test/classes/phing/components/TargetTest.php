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
 * UTs for Target component
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @author Daniel Holmes
 * @package phing.system
 */
class TargetTest extends BuildFileTest
{
    /** @var Target */
    private $target;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/components/Target/Target.xml'
        );

        $this->target = new Target();
        $this->target->setProject($this->project);
        $this->target->setName('MyTarget');
    }

    /**
     * @return void
     */
    public function testHiddenTargets(): void
    {
        $phingExecutable = '"' . PHING_TEST_BASE . '/../bin/phing"';
        $buildFile       = '"' . PHING_TEST_BASE . '/etc/components/Target/HiddenTargets.xml"';
        $cmd             = $phingExecutable . ' -l -f ' . $buildFile;
        exec($cmd, $out);
        $out    = implode("\n", $out);
        $offset = strpos($out, 'Subtargets:');
        self::assertFalse(strpos($out, 'HideInListTarget', $offset));
        self::assertTrue(strpos($out, 'ShowInListTarget', $offset) !== false);
    }

    /**
     * @param array  $expectedDepends
     * @param string $depends
     *
     * @return void
     *
     * @dataProvider setDependsValidDataProvider
     */
    public function testSetDependsValid(array $expectedDepends, string $depends): void
    {
        $this->target->setDepends($depends);

        self::assertEquals($expectedDepends, $this->target->getDependencies());
    }

    /**
     * @return array[]
     */
    public function setDependsValidDataProvider(): array
    {
        return [
            [['target1'], 'target1'],
            [['target1', 'target2'], 'target1,target2'],
        ];
    }

    /**
     * @param string $depends
     *
     * @return void
     *
     * @dataProvider setDependsInvalidDataProvider
     */
    public function testSetDependsInvalid(string $depends): void
    {
        $this->expectException('BuildException');
        $this->expectExceptionMessage('Syntax Error: Depend attribute for target MyTarget is malformed.');

        $this->target->setDepends($depends);
    }

    /**
     * @return array[]
     */
    public function setDependsInvalidDataProvider(): array
    {
        return [
            [''],
            ['target1,'],
        ];
    }

    /**
     * @return void
     */
    public function testGetTasksReturnsCorrectTasks(): void
    {
        $task = new EchoTask();
        $task->setMessage('Hello World');
        $this->target->addTask($task);
        $this->target->addDataType('dataType');

        $tasks = $this->target->getTasks();

        self::assertEquals([$task], $tasks);
    }

    /**
     * @return void
     */
    public function testGetTasksClonesTasks(): void
    {
        $task = new EchoTask();
        $task->setMessage('Hello World');
        $this->target->addTask($task);

        $tasks = $this->target->getTasks();

        $this->assertNotSame($task, $tasks[0]);
    }

    /**
     * @return void
     */
    public function testMainAppliesConfigurables(): void
    {
        $configurable = $this->getMockBuilder(RuntimeConfigurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configurable->expects($this->once())->method('maybeConfigure')->with($this->project);
        /** @var RuntimeConfigurable $configurable */
        $this->target->addDataType($configurable);

        $this->target->main();
    }

    /**
     * @return void
     */
    public function testMainFalseIfDoesntApplyConfigurable(): void
    {
        $this->project->setProperty('ifProperty', null);
        $this->target->setIf('ifProperty');

        $configurable = $this->getMockBuilder('RuntimeConfigurable')
            ->disableOriginalConstructor()
            ->getMock();
        $configurable->expects($this->never())->method('maybeConfigure');
        $this->target->addDataType($configurable);

        $this->target->main();
    }

    /**
     * @return void
     */
    public function testMainTrueUnlessDoesntApplyConfigurable(): void
    {
        $this->project->setProperty('unlessProperty', 'someValue');
        $this->target->setUnless('unlessProperty');

        $configurable = $this->getMockBuilder('RuntimeConfigurable')
            ->disableOriginalConstructor()
            ->getMock();
        $configurable->expects($this->never())->method('maybeConfigure');
        $this->target->addDataType($configurable);

        $this->target->main();
    }

    /**
     * @return void
     */
    public function testMainPerformsTasks(): void
    {
        $task = $this->createMock('Task');
        $task->expects($this->once())->method('perform');
        $this->target->addTask($task);

        $this->target->main();
    }

    /**
     * @return void
     */
    public function testMainFalseIfDoesntPerformTasks(): void
    {
        $this->project->setProperty('ifProperty', null);
        $this->target->setIf('ifProperty');

        $task = $this->createMock('Task');
        $task->expects($this->never())->method('perform');
        $this->target->addTask($task);

        $this->target->main();
    }

    /**
     * @return void
     */
    public function testMainTrueUnlessDoesntPerformTasks(): void
    {
        $this->project->setProperty('unlessProperty', 'someValue');
        $this->target->setUnless('unlessProperty');

        $task = $this->createMock('Task');
        $task->expects($this->never())->method('perform');
        $this->target->addTask($task);

        $this->target->main();
    }
}
