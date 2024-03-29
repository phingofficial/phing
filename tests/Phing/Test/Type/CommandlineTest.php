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

namespace Phing\Test\Type;

use Phing\Exception\BuildException;
use Phing\Type\Commandline;
use Phing\Type\CommandlineMarker;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for mappers.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 *
 * @internal
 */
class CommandlineTest extends TestCase
{
    /**
     * @var Commandline
     */
    private $cmd;

    //private $project;

    public function setUp(): void
    {
        $this->cmd = new Commandline();
    }

    public function testTranslateCommandline(): void
    {
        // This should work fine; we expect 5 args
        $cmd1 = 'cvs -d:pserver:hans@xmpl.org:/cvs commit -m "added a new test file" Test.php';
        $c = new Commandline($cmd1);
        $this->assertCount(5, $c->getArguments());

        // This has some extra space, but we expect same number of args
        $cmd2 = 'cvs -d:pserver:hans@xmpl.org:/cvs   commit  -m "added a new test file"    Test.php';
        $c2 = new Commandline($cmd2);
        $this->assertCount(5, $c2->getArguments());

        // nested quotes should not be a problem either
        $cmd3 = "cvs -d:pserver:hans@xmpl.org:/cvs   commit  -m \"added a new test file for 'fun'\"    Test.php";
        $c3 = new Commandline($cmd3);
        $this->assertCount(5, $c3->getArguments());
        $args = $c3->getArguments();
        $this->assertEquals("added a new test file for 'fun'", $args[3]);

        // now try unbalanced quotes -- this should fail
        $cmd4 = "cvs -d:pserver:hans@xmpl.org:/cvs   commit  -m \"added a new test file for 'fun' Test.php";

        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/unbalanced quotes/');

//        try {
//            new Commandline($cmd4);
//            $this->fail("Should throw BuildException because 'unbalanced quotes'");
//        } catch (BuildException $be) {
//            if (false === strpos($be->getMessage(), "unbalanced quotes")) {
//                $this->fail("Should throw BuildException because 'unbalanced quotes'");
//            }
//        }

        new Commandline($cmd4);
    }

    public function testCreateMarkerWithArgument(): void
    {
        $this->cmd->addArguments(['foo']);
        $marker = $this->cmd->createMarker();
        self::assertInstanceOf(CommandlineMarker::class, $marker);
        self::assertEquals(1, $marker->getPosition());
    }

    public function testCreateMarkerWithoutArgument(): void
    {
        $marker = $this->cmd->createMarker();
        self::assertInstanceOf(CommandlineMarker::class, $marker);
        self::assertEquals(0, $marker->getPosition());
    }
}
