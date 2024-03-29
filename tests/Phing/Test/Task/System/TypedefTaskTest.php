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

use Phing\Exception\BuildException;
use Phing\Exception\ConfigurationException;
use Phing\Test\Support\BuildFileTest;
use Phing\Test\Support\TypedefTestType;

/**
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 *
 * @internal
 */
class TypedefTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/typedef.xml');
    }

    public function testEmpty(): void
    {
        $this->expectBuildException('empty', 'required argument not specified');
    }

    public function testNoName(): void
    {
        $this->expectBuildException('noName', 'required argument not specified');
    }

    public function testNoClassname(): void
    {
        $this->expectBuildException('noClassname', 'required argument not specified');
    }

    public function testClassNotFound(): void
    {
        $this->expectException(BuildException::class);

        try {
            $this->executeTarget('classNotFound');
            $this->fail(
                'Should throw ConfigurationException because: ' .
                "classname specified doesn't exist"
            );
        } catch (ConfigurationException $ignored) {
        }
    }

    public function testGlobal(): void
    {
        $this->expectLog('testGlobal', 'Adding reference: global -> ' . TypedefTestType::class);
        $refs = $this->project->getReferences();
        $ref = $refs['global'];
        $this->assertNotNull('ref is not null', $ref);
        $this->assertInstanceOf(TypedefTestType::class, $ref);
    }

    public function testLocal(): void
    {
        $this->expectLog('testLocal', 'Adding reference: local -> ' . TypedefTestType::class);
        $refs = $this->project->getReferences();
        $ref = $refs['local'];
        $this->assertNotNull('ref is not null', $ref);
        $this->assertInstanceOf(TypedefTestType::class, $ref);
    }
}
