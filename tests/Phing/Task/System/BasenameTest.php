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
 * Tests the Diagnostics Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class BasenameTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/BasenameTest.xml'
        );
    }

    public function test1()
    {
        $this->expectBuildException('test1', '');
    }

    public function test2()
    {
        $this->expectBuildException('test2', '');
    }

    public function test3()
    {
        $this->expectBuildException('test3', '');
    }

    public function test4()
    {
        $this->executeTarget('test4');
        $checkprop = $this->getProject()->getProperty('file.w.suf');
        $this->assertEquals('foo.txt', $checkprop);
    }

    public function test5()
    {
        $this->executeTarget('test5');
        $checkprop = $this->getProject()->getProperty('file.wo.suf');
        $this->assertEquals('foo', $checkprop);
    }

    public function testMultipleDots()
    {
        $this->executeTarget('testMultipleDots');
        $checkprop = $this->getProject()->getProperty('file.wo.suf');
        $this->assertEquals('foo.bar', $checkprop);
    }

    public function testNoDots()
    {
        $this->executeTarget('testNoDots');
        $checkprop = $this->getProject()->getProperty('file.wo.suf');
        $this->assertEquals('foo.bar', $checkprop);
    }

    public function testValueEqualsSuffixWithDot()
    {
        $this->executeTarget('testValueEqualsSuffixWithDot');
        $checkprop = $this->getProject()->getProperty('file.wo.suf');
        $this->assertEquals('', $checkprop);
    }

    public function testValueEqualsSuffixWithoutDot()
    {
        $this->executeTarget('testValueEqualsSuffixWithoutDot');
        $checkprop = $this->getProject()->getProperty('file.wo.suf');
        $this->assertEquals('', $checkprop);
    }
}
