<?php
/*
 *  $Id$
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

use Phing\Test\AbstractBuildFileTest;

/**
 * @covers \PatternSet
 * @covers \PatternSetNameEntryCreator
 * @covers \PatternSetNameEntry
 */
class Ticket571RegressionTest extends AbstractBuildFileTest
{

    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/571/build.xml");
    }

    protected function check($trg)
    {
        $this->executeTarget($trg);
        $this->assertInLogs("match a");
        $this->assertNotInLogs("match b");
    }

    public function testFilesetAttributes()
    {
        $this->check('test1');
    }

    public function testFilesetNested()
    {
        $this->check('test2');
    }

    public function testPatternsetAttributes()
    {
        $this->check('test3');
    }

    public function testPatternsetNested()
    {
        $this->check('test4');
    }
}
