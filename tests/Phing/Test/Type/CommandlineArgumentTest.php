<?php

namespace Phing\Test\Type;

use Phing\Type\Commandline;
use Phing\Type\CommandlineArgument;
use PHPUnit\Framework\TestCase;

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
 *
 * @internal
 */
class CommandlineArgumentTest extends TestCase
{
    /**
     * Test the one 'getter' method of the CommandlineArgument class.
     */
    public function testGetParts(): void
    {
        $command = 'usblamp -s -r 5 red green blue off';
        $exploded = explode(' ', '-s -r 5 red green blue off');
        $commandline = new Commandline($command);
        $arguments = ($commandline->arguments);
        foreach ($arguments as $counter => $argument) {
            $parts = $argument->getParts();
            $this->assertEquals($exploded[$counter], $parts[0]);
        }
    }

    public function testSetline(): void
    {
        $commandline = new Commandline();
        $argument = new CommandlineArgument($commandline);
        $argument->setLine(null);
        $parts = $argument->getParts();
        $this->assertEquals([], $parts);
        $argument->setLine("perl -pie 's/foo/bar/g' test.txt");
        $parts = $argument->getParts();
        $this->assertNotEquals([], $parts);
    }
}
