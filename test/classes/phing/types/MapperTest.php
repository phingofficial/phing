<?php

/*
 *
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

/**
 * Unit test for mappers.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @package phing.types
 */
class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Project
     */
    private $project;

    public function setUp(): void
    {
        $this->project = new Project();
        $this->project->setBasedir(__DIR__);
    }

    public function testEmptyElementIfIsReference()
    {
        $m = new Mapper($this->project);
        $m->setFrom("*.java");
        try {
            $m->setRefid(new Reference($this->project, "dummyref"));
            self::fail("Can add reference to Mapper with from attribute set");
        } catch (BuildException $be) {
            self::assertEquals("You must not specify more than one attribute when using refid", $be->getMessage());
        }

        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        try {
            $m->setFrom("*.java");
            self::fail("Can set from in Mapper that is a reference.");
        } catch (BuildException $be) {
            self::assertEquals("You must not specify more than one attribute when using refid", $be->getMessage());
        }

        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        try {
            $m->setTo("*.java");
            self::fail("Can set to in Mapper that is a reference.");
        } catch (BuildException $be) {
            self::assertEquals("You must not specify more than one attribute when using refid", $be->getMessage());
        }
        try {
            $m = new Mapper($this->project);
            $m->setRefid(new Reference($this->project, "dummyref"));
            $m->setType("glob");
            self::fail("Can set type in Mapper that is a reference.");
        } catch (BuildException $be) {
            self::assertEquals("You must not specify more than one attribute when using refid", $be->getMessage());
        }
    }

    public function testCircularReferenceCheck()
    {
        $m = new Mapper($this->project);
        $this->project->addReference("dummy", $m);
        $m->setRefid(new Reference($this->project, "dummy"));
        try {
            $m->getImplementation();
            self::fail("Can make Mapper a Reference to itself.");
        } catch (BuildException $be) {
            self::assertEquals("This data type contains a circular reference.", $be->getMessage());
        }

        // dummy1 --> dummy2 --> dummy3 --> dummy1
        $m1 = new Mapper($this->project);
        $this->project->addReference("dummy1", $m1);
        $m1->setRefid(new Reference($this->project, "dummy2"));
        $m2 = new Mapper($this->project);
        $this->project->addReference("dummy2", $m2);
        $m2->setRefid(new Reference($this->project, "dummy3"));
        $m3 = new Mapper($this->project);
        $this->project->addReference("dummy3", $m3);
        $m3->setRefid(new Reference($this->project, "dummy1"));
        try {
            $m1->getImplementation();
            self::fail("Can make circular reference.");
        } catch (BuildException $be) {
            self::assertEquals("This data type contains a circular reference.", $be->getMessage());
        }

        // dummy1 --> dummy2 --> dummy3
        // (which holds a glob mapper from "*.java" to "*.class"
        $m1 = new Mapper($this->project);
        $this->project->addReference("dummy1", $m1);
        $m1->setRefid(new Reference($this->project, "dummy2"));
        $m2 = new Mapper($this->project);
        $this->project->addReference("dummy2", $m2);
        $m2->setRefid(new Reference($this->project, "dummy3"));
        $m3 = new Mapper($this->project);
        $this->project->addReference("dummy3", $m3);

        $m3->setType("glob");
        $m3->setFrom("*.java");
        $m3->setTo("*.class");

        $fmm = $m1->getImplementation();
        self::assertTrue($fmm instanceof GlobMapper, "Should be instance of GlobMapper");
        $result = $fmm->main("a.java");
        self::assertEquals(1, count($result));
        self::assertEquals("a.class", $result[0]);
    }

    public function testCopyTaskWithTwoFilesets()
    {
        $t = new TaskdefForCopyTest("test1");
        try {
            $t->setUp();
            $t->test1();
            $t->tearDown();
        } catch (Exception $e) {
            $t->tearDown();
            throw $e;
        }

        self::assertEquals(1, 1); // increase number of positive assertions
    }

    public function testSetClasspathThrowsExceptionIfReferenceSetAlready()
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        $p = new Path($this->project);
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClasspath($p);
    }

    public function testSetClasspath()
    {
        $m = new Mapper($this->project);
        $p = new Path($this->project);
        $m->setClasspath($p);
        $f = $m->createClasspath();
        $class = get_class($f);
        self::assertEquals("Path", $class);
    }

    public function testCreateClasspathThrowsExceptionIfReferenceAlreadySet()
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        $p = new Path($this->project);
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $f = $m->createClasspath();
    }

    public function testCallingsetClasspathRefThrowsExceptionIfReferenceAlreadySet()
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        $r2 = new Reference($this->project, "dummyref1");
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClasspathRef($r2);
    }

    public function testSetClassnameThrowsExceptionIfReferenceIsSet()
    {
        $m = new Mapper($this->project);
        $m->setRefid(new Reference($this->project, "dummyref"));
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify more than one attribute when using refid');
        $m->setClassname("mapper1");
    }
}

/**
 * @package phing.mappers
 */
class TaskdefForCopyTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/types/mapper.xml");
    }

    public function tearDown(): void
    {
        $this->executeTarget("cleanup");
    }

    public function test1()
    {
        $this->executeTarget("test1");
    }

    public function test2()
    {
        $this->executeTarget("test2");
    }

    public function test3()
    {
        $this->executeTarget("test3");
        $this->assertInLogs('php1');
        $this->assertInLogs('php2');
    }

    public function test4()
    {
        $this->executeTarget("test4");
        $this->assertNotInLogs('.php1');
        $this->assertInLogs('.php2');
    }

    public function testCutDirsMapper()
    {
        $this->executeTarget("testCutDirsMapper");
        $outputDir = $this->getProject()->getProperty('output');
        self::assertFileExists($outputDir . '/D');
        self::assertFileExists($outputDir . '/c/E');
    }
}
