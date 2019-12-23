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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PatternSetTest extends TestCase
{
    /**
     * @var PatternSet
     */
    private $patternset;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->patternset = new PatternSet();
    }

    /**
     * @return void
     */
    public function testBothEmpty(): void
    {
        $s = '' . $this->patternset;
        $this->assertEquals($s, 'patternSet{ includes: empty  excludes: empty }');
        $this->assertEquals(false, $this->patternset->hasPatterns());
    }

    /**
     * @return void
     */
    public function testIfReferenceSetThenCreateIncludeThrowsException(): void
    {
        $project   = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createInclude();
    }

    /**
     * @return void
     */
    public function testIfReferenceSetThenCreateExcludeThrowsException(): void
    {
        $project   = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExclude();
    }

    /**
     * @return void
     */
    public function testIfReferencesSetThenCreatExcludesFileThrowsException(): void
    {
        $project   = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExcludesFile();
    }

    /**
     * @return void
     */
    public function testIfReferencesSetThenCreatIncludesFileThrowsException(): void
    {
        $project   = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createIncludesFile();
    }
}
