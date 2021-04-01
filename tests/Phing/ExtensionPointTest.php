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

namespace Phing\Test;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Test\Support\BuildFileTest;

/**
 * UTs for ExtensionPoint component.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class ExtensionPointTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/components/ExtensionPoint/ExtensionPoint.xml'
        );
    }

    public function testExtensionPointWorksLikeTarget()
    {
        $this->expectLogContaining(__FUNCTION__, 'foobar');
    }

    public function testAddToExtensionPoint()
    {
        $this->expectLogContaining(__FUNCTION__, 'In target bar');
    }

    public function testExtensionPointMustBeEmpty()
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->assertInLogs('you must not nest child elements into an extension-point');
        }
    }

    public function testCantAddToPlainTarget()
    {
        $this->expectBuildException(__FUNCTION__, 'referenced target foo is not an extension-point');
    }

    public function testExtensionPointInImportedBuildfile()
    {
        $this->expectLogContaining(__FUNCTION__, 'in target prepare');
    }

    public function testExtensionPointInImportedBuildfileWithNestedImport()
    {
        $this->expectLogContaining(__FUNCTION__, 'in compile java');
    }

    public function testMissingExtensionPointCausesError()
    {
        $this->expectBuildException(__FUNCTION__, 'can\'t add target bar to extension-point foo because the extension-point is unknown');
    }

    public function testMissingExtensionPointCausesWarningWhenConfigured()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('can\'t add target bar to extension-point foo because the extension-point is unknown', Project::MSG_WARN);
    }

    public function testMissingExtensionPointIgnoredWhenConfigured()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('can\'t add target bar to extension-point foo because the extension-point is unknown', Project::MSG_WARN);
    }

    public function testOnlyAllowsExtensionPointMissingAttributeWhenExtensionOfPresent()
    {
        $this->expectBuildException(__FUNCTION__, 'onMissingExtensionPoint attribute cannot be specified unless extensionOf is specified');
    }
}
