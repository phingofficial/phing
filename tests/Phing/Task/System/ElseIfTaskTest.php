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

namespace Phing\Task\System;

use Phing\Support\BuildFileTest;

/**
 * Tests the ElseIf Task
 *
 * @author  Paul Edenburg <pauledenburg@gmail.com>
 */
class ElseIfTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ElseIfTest.xml'
        );
    }

    /**
     * Test the 'elseif' conditional of the if-task
     *
     * @test
     */
    public function testAddThen()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("Elseif: The value of property foo is 'foo'");
    }

    /**
     * Test that evaluating a correct elseif condition gives the
     * expected result
     *
     * @test
     */
    public function testEvaluate()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("Elseif: The value of property foo is foo");
    }

    /**
     * test that a BuildException is thrown when we've got two
     * conditions inside an elseif-task
     *
     * @test
     */
    public function testMultipleConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 condition in your elseif-statement';
        $msg = 'You must not nest more than one condition into <elseif>';

        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got
     * no conditions inside an elseif-task
     *
     * @test
     */
    public function testNoConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you need to have a condition inside the <elseif>';
        $msg = 'You must nest a condition into <elseif>';

        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }
}
