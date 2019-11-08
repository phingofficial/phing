<?php

/*
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

/**
 * Unit tests for AbstractFileSet.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.types
 */
abstract class AbstractFileSetTest extends \PHPUnit\Framework\TestCase
{
    /** @var Project */
    private $project;

    public function setUp(): void
    {
        $this->project = new Project();
        $this->project->setBasedir(PHING_TEST_BASE);
    }

    abstract protected function getInstance();

    final protected function getProject()
    {
        return $this->project;
    }

    final public function testEmptyElementIfIsReference()
    {
        /** @var FileSet $f */
        $f = $this->getInstance();
        $f->setIncludes("**/*.php");
        try {
            $f->setRefid(new Reference($this->getProject(), "dummyref"));
            self::fail(
                "Can add reference to "
                . $f
                . " with elements from setIncludes"
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->createPatternSet();
        try {
            $f->setRefid(new Reference($this->getProject(), "dummyref"));
            self::fail(
                "Can add reference to "
                . $f
                . " with nested patternset element."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when "
                . "using refid",
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->createInclude();
        try {
            $f->setRefid(new Reference($this->getProject(), "dummyref"));
            self::fail(
                "Can add reference to "
                . $f
                . " with nested include element."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        $f = $this->getInstance();
        $f->setRefid(new Reference($this->getProject(), "dummyref"));
        try {
            $f->setIncludes("**/*.java");
            self::fail(
                "Can set includes in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setIncludesfile(new PhingFile("/a"));
            self::fail(
                "Can set includesfile in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setExcludes("**/*.java");
            self::fail(
                "Can set excludes in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setExcludesfile(new PhingFile("/a"));
            self::fail(
                "Can set excludesfile in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setDir($this->project->resolveFile("."));
            self::fail(
                "Can set dir in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setExpandSymbolicLinks(true);
            self::fail(
                "Can expand symbolic links in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setFile($this->project->resolveFile(__FILE__));
            self::fail(
                "Can set file in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->setCaseSensitive(true);
            self::fail(
                "Can set case sensitive in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify more than one attribute "
                . "when using refid",
                $be->getMessage()
            );
        }

        try {
            $f->createInclude();
            self::fail(
                "Can add nested include in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when using "
                . "refid",
                $be->getMessage()
            );
        }

        try {
            $f->createExclude();
            self::fail(
                "Can add nested exclude in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when using "
                . "refid",
                $be->getMessage()
            );
        }

        try {
            $f->createIncludesFile();
            self::fail(
                "Can add nested includesfile in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when using "
                . "refid",
                $be->getMessage()
            );
        }
        try {
            $f->createExcludesFile();
            self::fail(
                "Can add nested excludesfile in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when using "
                . "refid",
                $be->getMessage()
            );
        }
        try {
            $f->createPatternSet();
            self::fail(
                "Can add nested patternset in "
                . $f
                . " that is a reference."
            );
        } catch (BuildException $be) {
            self::assertEquals(
                "You must not specify nested elements when using "
                . "refid",
                $be->getMessage()
            );
        }
    }
}
