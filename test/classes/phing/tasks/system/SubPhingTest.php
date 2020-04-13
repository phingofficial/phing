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

/**
 * Tests the SubPhing Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class SubPhingTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/subphing.xml'
        );
    }

    public function testNoDirs()
    {
        $this->expectLog(__FUNCTION__, 'No sub-builds to iterate on');
    }

    public function testGenericPhingFile()
    {
        $dir1 = $this->getProject()->resolveFile('.');
        $dir2 = $this->getProject()->resolveFile('subant/subant-test1');
        $dir3 = $this->getProject()->resolveFile('subant/subant-test2');

        $this->baseDirs(
            __FUNCTION__,
            [
                $dir1->getAbsolutePath(),
                $dir2->getAbsolutePath(),
                $dir3->getAbsolutePath()
            ]
        );
    }

    public function testPhingFile()
    {
        $dir1 = $this->getProject()->resolveFile('.');
        // basedir of subant/subant-test1/subant.xml is ..
        // therefore we expect here the subant/subant-test1 subdirectory
        $dir2 = $this->getProject()->resolveFile('subant/subant-test1');
        // basedir of subant/subant-test2/subant.xml is ..
        // therefore we expect here the subant subdirectory
        $dir3 = $this->getProject()->resolveFile('subant');

        $this->baseDirs(
            __FUNCTION__,
            [
                $dir1->getAbsolutePath(),
                $dir2->getAbsolutePath(),
                $dir3->getAbsolutePath()
            ]
        );
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
}
