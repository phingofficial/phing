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

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the If Task.
 *
 * @author  Paul Edenburg <pauledenburg@gmail.com>
 */
class IfTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/IfTest.xml'
        );
    }

    /**
     * Test the 'elseif' conditional of the if-task.
     *
     * @test
     */
    public function testAddElseIf()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is 'foo'");
    }

    /**
     * Test the 'then' conditional of the if-task.
     *
     * @test
     */
    public function testAddThen()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is 'foo'");
    }

    /**
     * Test the 'else' conditional of the if-task.
     *
     * @test
     */
    public function testAddElse()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is not 'bar'");
    }

    /**
     * test that a buildexception is thrown when we've got two
     * <then> statements in an if-task.
     *
     * @test
     */
    public function testAddDoubleThen()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 <then> directive in your if-statement';
        $msg = 'You must not nest more than one <then> into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got two
     * <else> statements in an if-task.
     *
     * @test
     */
    public function testAddDoubleElse()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 <else> directive in your if-statement';
        $msg = 'You must not nest more than one <else> into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got two
     * <else> statements in an if-task.
     *
     * @test
     */
    public function testMultipleConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 condition in your if-statement';
        $msg = 'You must not nest more than one condition into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }
}
