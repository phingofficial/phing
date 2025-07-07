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
use Phing\Io\File;
use Phing\Project;
use Phing\Type\FileSet;
use Phing\Type\Reference;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for AbstractFileSet.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 */
abstract class AbstractFileSetTest extends TestCase
{
    /** @var Project */
    private $project;

    public function setUp(): void
    {
        $this->project = new Project();
        $this->project->setBasedir(PHING_TEST_BASE);
    }

    final public function testEmptyElementIfIsReference(): void
    {
        /** @var FileSet $f */
        $f = $this->getInstance();
        $f->setIncludes('**/*.php');

        try {
            $f->setRefid(new Reference($this->getProject(), 'dummyref'));
            $this->fail(
                'Can add reference to '
                . $f
                . ' with elements from setIncludes'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->createPatternSet();

        try {
            $f->setRefid(new Reference($this->getProject(), 'dummyref'));
            $this->fail(
                'Can add reference to '
                . $f
                . ' with nested patternset element.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when '
                . 'using refid',
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->createInclude();

        try {
            $f->setRefid(new Reference($this->getProject(), 'dummyref'));
            $this->fail(
                'Can add reference to '
                . $f
                . ' with nested include element.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->setRefid(new Reference($this->getProject(), 'dummyref'));

        try {
            $f->setIncludes('**/*.java');
            $this->fail(
                'Can set includes in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setIncludesfile(new File('/a'));
            $this->fail(
                'Can set includesfile in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setExcludes('**/*.java');
            $this->fail(
                'Can set excludes in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setExcludesfile(new File('/a'));
            $this->fail(
                'Can set excludesfile in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setDir($this->project->resolveFile('.'));
            $this->fail(
                'Can set dir in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setExpandSymbolicLinks(true);
            $this->fail(
                'Can expand symbolic links in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setFile($this->project->resolveFile(__FILE__));
            $this->fail(
                'Can set file in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->setCaseSensitive(true);
            $this->fail(
                'Can set case sensitive in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify more than one attribute '
                . 'when using refid',
                $be->getMessage()
            );
        }

        try {
            $f->createInclude();
            $this->fail(
                'Can add nested include in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when using '
                . 'refid',
                $be->getMessage()
            );
        }

        try {
            $f->createExclude();
            $this->fail(
                'Can add nested exclude in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when using '
                . 'refid',
                $be->getMessage()
            );
        }

        try {
            $f->createIncludesFile();
            $this->fail(
                'Can add nested includesfile in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when using '
                . 'refid',
                $be->getMessage()
            );
        }

        try {
            $f->createExcludesFile();
            $this->fail(
                'Can add nested excludesfile in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when using '
                . 'refid',
                $be->getMessage()
            );
        }

        try {
            $f->createPatternSet();
            $this->fail(
                'Can add nested patternset in '
                . $f
                . ' that is a reference.'
            );
        } catch (BuildException $be) {
            $this->assertEquals(
                'You must not specify nested elements when using '
                . 'refid',
                $be->getMessage()
            );
        }
    }

    final public function testDoNotFailBuildOnMissingDir()
    {
        /** @var FileSet $f */
        $f = $this->getInstance();

        $f->setErrorOnMissingDir(false);
        $f->setProject($this->getProject());
        $f->setDir($this->getProject()->resolveFile('not_exists'));
        $ds = $f->getDirectoryScanner();
        $this->assertEmpty($ds->getIncludedFiles());
    }

    final public function testFailBuildOnMissingDir()
    {
        /** @var FileSet $f */
        $f = $this->getInstance();

        $f->setErrorOnMissingDir(true);
        $f->setProject($this->getProject());
        $f->setDir($this->getProject()->resolveFile('not_exists'));

        $this->expectException(BuildException::class);
        $ds = $f->getDirectoryScanner();
    }

    abstract protected function getInstance();

    final protected function getProject(): Project
    {
        return $this->project;
    }
}
