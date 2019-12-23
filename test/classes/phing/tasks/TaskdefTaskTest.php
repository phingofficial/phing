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
 * @package phing.tasks.system
 */
class TaskdefTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/taskdef.xml');
    }

    /**
     * @return void
     */
    public function testEmpty(): void
    {
        $this->expectBuildException('empty', 'required argument not specified');
    }

    /**
     * @return void
     */
    public function testNoName(): void
    {
        $this->expectBuildException('noName', 'required argument not specified');
    }

    /**
     * @return void
     */
    public function testNoClassname(): void
    {
        $this->expectBuildException('noClassname', 'required argument not specified');
    }

    /**
     * @return void
     */
    public function testClassNotFound(): void
    {
        $this->expectException(BuildException::class);

        try {
            $this->executeTarget('classNotFound');
            $this->fail(
                'Should throw ConfigurationException because: ' .
                "classname specified doesn't exist"
            );
        } catch (ConfigurationException $e) {
            //ignored
        }
    }

    /**
     * @return void
     */
    public function testGlobal(): void
    {
        $this->expectLog('testGlobal', 'simpletask: testGlobal echo');
        $refs = $this->project->getReferences();
        $ref  = $refs['global'];
        $this->assertNotNull('ref is not null');
        $this->assertEquals('TaskdefTestSimpleTask', get_class($ref));
    }

    /**
     * @return void
     */
    public function testLocal(): void
    {
        $this->expectLog('testLocal', 'Task local will be handled by class example.tasks.TaskdefTestSimpleTask');
        $refs = $this->project->getReferences();
        $ref  = $refs['local'];
        $this->assertNotNull('ref is not null');
        $this->assertInstanceOf('TaskdefTestSimpleTask', $ref);
    }

    /**
     * @return void
     */
    public function tesFile(): void
    {
        $this->expectLog('testFile', 'simpletask: testTdfile echo');
        $refs = $this->project->getReferences();
        $ref  = $refs['tdfile'];
        $this->assertNotNull('ref is not null');
        $this->assertEquals('TaskdefTestSimpleTask', get_class($ref));
        $ref = $refs['tdfile2'];
        $this->assertNotNull('ref is not null');
        $this->assertEquals('TaskdefTestSimpleTask', get_class($ref));
    }
}
