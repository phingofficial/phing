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

use Phing\Io\File;
use Phing\Listener\BuildEvent;
use Phing\Listener\BuildListener;

/**
 * Testcase for the Phing task/condition.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class PhingTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/phing.xml');
    }

    public function tearDown(): void
    {
        $this->getProject()->executeTarget('cleanup');
    }

    public function test1(): void
    {
        $this->expectBuildException(__FUNCTION__, 'phing task self referencing.');
    }

    public function test2(): void
    {
        $this->expectBuildException(__FUNCTION__, 'phingcall without arguments.');
    }

    public function test3(): void
    {
        $this->expectBuildException(__FUNCTION__, 'No BuildException thrown.');
    }

    public function test4(): void
    {
        $this->expectBuildException(__FUNCTION__, 'phingcall with empty target.');
    }

    public function test4b(): void
    {
        $this->expectBuildException(__FUNCTION__, 'phingcall with not existing target.');
    }

    public function test5(): void
    {
        $this->expectNotToPerformAssertions();
        $this->getProject()->executeTarget(__FUNCTION__);
    }

    public function test6(): void
    {
        $this->expectNotToPerformAssertions();
        $this->getProject()->executeTarget(__FUNCTION__);
    }

    public function testExplicitBasedir1(): void
    {
        $dir1 = $this->getProject()->getBaseDir();
        $dir2 = $this->getProject()->resolveFile("..");
        $this->baseDirs('explicitBasedir1', [$dir1->getAbsolutePath(), $dir2->getAbsolutePath()]);
    }

    private function baseDirs(string $target, array $dirs): void
    {
        $bc = new class ($dirs) implements BuildListener {
            private $expectedBasedirs;
            private $calls = 0;
            private $error;

            public function __construct(array $dirs)
            {
                $this->expectedBasedirs = $dirs;
            }

            public function buildStarted(BuildEvent $event)
            {
            }

            public function buildFinished(BuildEvent $event)
            {
            }

            public function targetFinished(BuildEvent $event)
            {
            }

            public function taskStarted(BuildEvent $event)
            {
            }

            public function taskFinished(BuildEvent $event)
            {
            }

            public function messageLogged(BuildEvent $event)
            {
            }

            public function targetStarted(BuildEvent $event)
            {
                if ($event->getTarget()->getName() === '') {
                    return;
                }
                if ($this->error === null) {
                    try {
                        BuildFileTest::assertEquals(
                            $this->expectedBasedirs[$this->calls++],
                            $event->getProject()->getBaseDir()->getAbsolutePath()
                        );
                    } catch (AssertionError $e) {
                        $this->error = $e;
                    }
                }
            }

            public function getError()
            {
                return $this->error;
            }
        };
        $this->getProject()->addBuildListener($bc);
        $this->executeTarget($target);
        $ae = $bc->getError();
        if ($ae !== null) {
            throw $ae;
        }
        $this->getProject()->removeBuildListener($bc);
    }

    public function testExplicitBasedir2(): void
    {
        $dir1 = $this->getProject()->getBaseDir();
        $dir2 = $this->getProject()->resolveFile("..");
        $this->baseDirs('explicitBasedir2', [$dir1->getAbsolutePath(), $dir2->getAbsolutePath()]);
    }

    public function testInheritBasedir(): void
    {
        $basedir = $this->getProject()->getBaseDir()->getAbsolutePath();
        $this->baseDirs('inheritBasedir', [$basedir, $basedir]);
    }

    public function testDoNotInheritBasedir(): void
    {
        $dir1 = $this->getProject()->getBaseDir();
        $dir2 = $this->getProject()->resolveFile('phing');
        $this->baseDirs('doNotInheritBasedir', [$dir1->getAbsolutePath(), $dir2->getAbsolutePath()]);
    }

    public function testBasedirTripleCall(): void
    {
        $dir1 = $this->getProject()->getBaseDir();
        $dir2 = $this->getProject()->resolveFile("phing");
        $this->baseDirs('tripleCall', [$dir1->getAbsolutePath(), $dir2->getAbsolutePath(), $dir1->getAbsolutePath()]);
    }

    public function testReferenceInheritance(): void
    {
        $p = new Path($this->getProject(), 'test-path');
        $this->getProject()->addReference('path', $p);
        $this->getProject()->addReference('no-override', $p);
        $this->reference('testInherit', ['path', 'path'], [true, true], $p);
        $this->reference('testInherit', ['no-override', 'no-override'], [true, false], $p);
        $this->reference('testInherit', ['no-override', 'no-override'], [false, false], null);
    }

    protected function reference(string $target, array $keys, array $expect, $value): void
    {
        $rc = new class ($keys, $expect, $value) implements BuildListener {
            private $keys;
            private $expectSame;
            private $value;
            private $calls = 0;
            private $error;

            public function __construct(array $keys, array $expectSame, $value)
            {
                $this->keys = $keys;
                $this->expectSame = $expectSame;
                $this->value = $value;
            }

            public function buildStarted(BuildEvent $event)
            {
            }

            public function buildFinished(BuildEvent $event)
            {
            }

            public function targetFinished(BuildEvent $event)
            {
            }

            public function taskStarted(BuildEvent $event)
            {
            }

            public function taskFinished(BuildEvent $event)
            {
            }

            public function messageLogged(BuildEvent $event)
            {
            }

            public function targetStarted(BuildEvent $event)
            {
                if ($event->getTarget()->getName() === '') {
                    return;
                }
                if ($this->error === null) {
                    try {
                        $msg = "Call " . $this->calls . " refid=\'" . $this->keys[$this->calls] . "\'";
                        if ($this->value === null) {
                            $o = $event->getProject()->getReference($this->keys[$this->calls]);
                            if ($this->expectSame[$this->calls++]) {
                                PhingTaskTest::assertNull($o, $msg);
                            } else {
                                PhingTaskTest::assertNotNull($o, $msg);
                            }
                        } else {
                            // a rather convoluted equals() test
                            /** @var Path $expect */
                            $expect = $this->value;
                            $received = $event->getProject()->getReference($this->keys[$this->calls]);
                            $shouldBeEqual = $this->expectSame[$this->calls++];
                            if ($received === null) {
                                PhingTaskTest::assertFalse($shouldBeEqual, $msg);
                            } else {
                                $l1 = $expect->listPaths();
                                $l2 = $received->listPaths();
                                if (count($l1) === count($l2)) {
                                    for ($i = 0, $iMax = count($l1); $i < $iMax; $i++) {
                                        if ($l1[$i] !== $l2[$i]) {
                                            PhingTaskTest::assertFalse($shouldBeEqual, $msg);
                                        }
                                    }
                                    PhingTaskTest::assertTrue($shouldBeEqual, $msg);
                                } else {
                                    PhingTaskTest::assertFalse($shouldBeEqual, $msg);
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        $this->error = $e;
                    }
                }
            }

            public function getError()
            {
                return $this->error;
            }
        };
        $this->getProject()->addBuildListener($rc);
        $this->getProject()->executeTarget($target);
        $ae = $rc->getError();
        if ($ae !== null) {
            throw $ae;
        }
        $this->getProject()->removeBuildListener($rc);
    }

    public function testReferenceNoInheritance(): void
    {
        $p = new Path($this->getProject(), 'test-path');
        $this->getProject()->addReference("path", $p);
        $this->getProject()->addReference("no-override", $p);
        $this->reference("testNoInherit", ["path", "path"], [true, false], $p);
        $this->reference("testNoInherit", ["path", "path"], [false, true], null);
        $this->reference("testInherit", ["no-override", "no-override"], [true, false], $p);
        $this->reference("testInherit", ["no-override", "no-override"], [false, false], null);
    }

    public function testInheritPath(): void
    {
        $this->expectNotToPerformAssertions();
        $this->getProject()->executeTarget('testInheritPath');
    }

    public function testLogfilePlacement(): void
    {
        /** @var File[] $logFiles */
        $logFiles = [
            $this->getProject()->resolveFile("test1.log"),
            $this->getProject()->resolveFile("test2.log"),
            $this->getProject()->resolveFile("phing/test3.log"),
            $this->getProject()->resolveFile("phing/test4.log")
        ];

        foreach ($logFiles as $file) {
            $this->assertFalse($file->exists(), $file->getName() . " doesn't exist");
        }

        $this->getProject()->executeTarget(__FUNCTION__);

        foreach ($logFiles as $file) {
            $this->assertTrue($file->exists(), $file->getName() . " exist");
        }
    }

    public function testUserPropertyWinsInheritAll(): void
    {
        $this->getProject()->setUserProperty("test", "7");
        $this->getProject()->executeTarget("test-property-override-inheritall-start");
        $this->assertInLogs('The value of test is 7');
    }

    public function testUserPropertyWinsNoInheritAll()
    {
        $this->getProject()->setUserProperty("test", "7");
        $this->getProject()->executeTarget("test-property-override-no-inheritall-start");
        $this->assertInLogs('The value of test is 7');
    }

    public function testOverrideWinsInheritAll()
    {
        $this->expectLogContaining('test-property-override-inheritall-start', 'The value of test is 4');
    }

    public function testOverrideWinsNoInheritAll()
    {
        $this->expectLogContaining('test-property-override-no-inheritall-start', 'The value of test is 4');
    }

    /**
     * Fail due to infinite recursion loop
     */
    public function testInfiniteLoopViaDepends(): void
    {
        $this->markTestSkipped('infinite loop could occure');
//        $this->expectBuildException('infinite-loop-via-depends', 'infinite loop');
    }

    public function testMultiSameProperty(): void
    {
        $this->expectLogContaining('multi-same-property', 'prop is two');
    }

    public function testTopLevelTarget(): void
    {
        $this->expectLogContaining('topleveltarget', 'Hello world');
    }
}
