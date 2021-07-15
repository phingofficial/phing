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
use Phing\Project;
use Phing\Type\PatternSet;
use Phing\Type\Reference;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PatternSetTest extends TestCase
{
    private $patternset;

    protected function setUp(): void
    {
        $this->patternset = new PatternSet();
    }

    public function testBothEmpty(): void
    {
        $s = '' . $this->patternset;
        $this->assertEquals('patternSet{ includes: empty  excludes: empty }', $s);
        $this->assertEquals(false, $this->patternset->hasPatterns());
    }

    public function testIfReferenceSetThenCreateIncludeThrowsException(): void
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createInclude();
    }

    public function testIfReferenceSetThenCreateExcludeThrowsException(): void
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExclude();
    }

    public function testIfReferencesSetThenCreatExcludesFileThrowsException(): void
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExcludesFile();
    }

    public function testIfReferencesSetThenCreatIncludesFileThrowsException(): void
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createIncludesFile();
    }
}
