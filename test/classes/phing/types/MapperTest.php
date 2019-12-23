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

use PHPUnit\Framework\TestCase;

/**
 * Unit test for mappers.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @package phing.types
 */
class MapperTest extends TestCase
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->project = new Project();
        $this->project->setBasedir(__DIR__);
    }

    /**
     * @return void
     */
    public function testEmptyElementIfIsReference(): void
    {
        $m = new Mapper($this->project);
        $m->setFrom('*.java');
        try {
            $m->setRefid(new Reference($this->project, 'dummyref'));
            $this->fail('Can add reference to Mapper with from attribute set');
        } catch (BuildException $be) {
            $this->assertEquals('You must not specify more than one attribute when using refid', $be->getMessage());
        }

        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        try {
            $m->setFrom('*.java');
            $this->fail('Can set from in Mapper that is a reference.');
        } catch (BuildException $be) {
            $this->assertEquals('You must not specify more than one attribute when using refid', $be->getMessage());
        }

        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        try {
            $m->setTo('*.java');
            $this->fail('Can set to in Mapper that is a reference.');
        } catch (BuildException $be) {
            $this->assertEquals('You must not specify more than one attribute when using refid', $be->getMessage());
        }
        try {
            $m = new Mapper($this->project);
            $m->setRefid(new Reference($this->project, 'dummyref'));
            $m->setType('glob');
            $this->fail('Can set type in Mapper that is a reference.');
        } catch (BuildException $be) {
            $this->assertEquals('You must not specify more than one attribute when using refid', $be->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testCircularReferenceCheck(): void
    {
        $m = new Mapper($this->project);
        $this->project->addReference('dummy', $m);
        $m->setRefid(new Reference($this->project, 'dummy'));
        try {
            $m->getImplementation();
            $this->fail('Can make Mapper a Reference to itself.');
        } catch (BuildException $be) {
            $this->assertEquals('This data type contains a circular reference.', $be->getMessage());
        }

        // dummy1 --> dummy2 --> dummy3 --> dummy1
        $m1 = new Mapper($this->project);
        $this->project->addReference('dummy1', $m1);
        $m1->setRefid(new Reference($this->project, 'dummy2'));
        $m2 = new Mapper($this->project);
        $this->project->addReference('dummy2', $m2);
        $m2->setRefid(new Reference($this->project, 'dummy3'));
        $m3 = new Mapper($this->project);
        $this->project->addReference('dummy3', $m3);
        $m3->setRefid(new Reference($this->project, 'dummy1'));
        try {
            $m1->getImplementation();
            $this->fail('Can make circular reference.');
        } catch (BuildException $be) {
            $this->assertEquals('This data type contains a circular reference.', $be->getMessage());
        }

        // dummy1 --> dummy2 --> dummy3
        // (which holds a glob mapper from "*.java" to "*.class"
        $m1 = new Mapper($this->project);
        $this->project->addReference('dummy1', $m1);
        $m1->setRefid(new Reference($this->project, 'dummy2'));
        $m2 = new Mapper($this->project);
        $this->project->addReference('dummy2', $m2);
        $m2->setRefid(new Reference($this->project, 'dummy3'));
        $m3 = new Mapper($this->project);
        $this->project->addReference('dummy3', $m3);

        $m3->setType('glob');
        $m3->setFrom('*.java');
        $m3->setTo('*.class');

        $fmm = $m1->getImplementation();
        $this->assertTrue($fmm instanceof GlobMapper, 'Should be instance of GlobMapper');
        $result = $fmm->main('a.java');
        $this->assertEquals(1, count($result));
        $this->assertEquals('a.class', $result[0]);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCopyTaskWithTwoFilesets(): void
    {
        $t = new TaskdefForCopyTest('test1');
        try {
            $t->setUp();
            $t->test1();
            $t->tearDown();
        } catch (Throwable $e) {
            $t->tearDown();
            throw $e;
        }

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     * @return void
     */
    public function testSetClasspathThrowsExceptionIfReferenceSetAlready(): void
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        $p = new Path($this->project);
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClasspath($p);
    }

    /**
     * @return void
     */
    public function testSetClasspath(): void
    {
        $m = new Mapper($this->project);
        $p = new Path($this->project);
        $m->setClasspath($p);
        $f     = $m->createClasspath();
        $class = get_class($f);
        $this->assertEquals('Path', $class);
    }

    /**
     * @return void
     */
    public function testCreateClasspathThrowsExceptionIfReferenceAlreadySet(): void
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        $p = new Path($this->project);
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $f = $m->createClasspath();
    }

    /**
     * @return void
     */
    public function testCallingsetClasspathRefThrowsExceptionIfReferenceAlreadySet(): void
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        $r2 = new Reference($this->project, 'dummyref1');
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClasspathRef($r2);
    }

    /**
     * @return void
     */
    public function testSetClassnameThrowsExceptionIfReferenceIsSet(): void
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, 'dummyref'));
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClassname('mapper1');
    }
}
