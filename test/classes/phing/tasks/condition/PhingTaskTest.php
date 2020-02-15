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
        $this->getProject()->executeTarget(__FUNCTION__);
    }

    public function test6(): void
    {
        $this->getProject()->executeTarget(__FUNCTION__);
    }
}
