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

namespace Phing\Test\Task\Ext\Analyzer;

use Exception;
use Phing\Exception\BuildException;
use Phing\Test\Support\BuildFileTest;

/**
 * @author Bernhard Mendl <mail@bernhard-mendl.de>
 *
 * @requires OSFAMILY Windows|Linux
 *
 * @internal
 */
class SonarTaskTest extends BuildFileTest
{
    protected function setUp(): void
    {
        $buildXmlFile = PHING_TEST_BASE . '/etc/tasks/ext/sonar/SonarTaskTest.xml';
        $this->configureProject($buildXmlFile);
    }

    //
    // Test "executable" attribute ...
    //

    public function testExecutableAttributeIsMissingThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-attribute-is-missing',
            'executable-attribute-is-missing',
            'You must specify the path of the SonarQube Scanner using the "executable" attribute.'
        );
    }

    public function testExecutableAttributeIsEmptyThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-attribute-is-empty',
            'executable-attribute-is-empty',
            'You must specify the path of the SonarQube Scanner using the "executable" attribute.'
        );
    }

    public function testExecutablePathDoesNotExistThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-path-does-not-exist',
            'executable-path-does-not-exist',
            'Cannot find SonarQube Scanner'
        );
    }

    /**
     * the return code of the exec command is always 0 under windows.
     */
    public function testExecutableFileIsNotExecutableThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-file-is-not-executable',
            'executable-file-is-not-executable',
            'Cannot find SonarQube Scanner'
        );
    }

    public function testExecutableIsNotSonarScannerAndHasNoVersionStringThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-is-not-sonar-scanner-and-has-no-version-string',
            'executable-is-not-sonar-scanner-and-has-no-version-string',
            'Cannot find SonarQube Scanner'
        );
    }

    public function testExecutableIsNotSonarScannerAndHasVersionStringThrowsException(): void
    {
        $this->expectBuildExceptionContaining(
            'executable-is-not-sonar-scanner-and-has-version-string',
            'executable-is-not-sonar-scanner-and-has-version-string',
            'Could not find name of SonarQube Scanner in version string. Executable appears not to be SonarQube Scanner'
        );
    }

    public function testErrorsAttributeIsMissing(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->expectPropertySet('errors-attribute-is-missing', 'errors', 'false');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }
    }

    public function testErrorsAttributeIsEmpty(): void
    {
        $this->expectBuildExceptionContaining(
            'errors-attribute-is-empty',
            'errors-attribute-is-empty',
            'Expected a boolean value.'
        );
    }

    public function testErrorsValueIsInvalid(): void
    {
        $this->expectBuildExceptionContaining(
            'errors-value-is-invalid',
            'errors-value-is-invalid',
            'Expected a boolean value.'
        );
    }

    public function testDebugAttributeIsMissing(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->expectPropertySet('debug-attribute-is-missing', 'debug', 'false');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }
    }

    public function testDebugAttributeIsEmpty(): void
    {
        $this->expectBuildExceptionContaining(
            'debug-attribute-is-empty',
            'debug-attribute-is-empty',
            'Expected a boolean value.'
        );
    }

    public function testDebugValueIsInvalid(): void
    {
        $this->expectBuildExceptionContaining(
            'debug-value-is-invalid',
            'debug-value-is-invalid',
            'Expected a boolean value.'
        );
    }

    public function testConfigurationAttributeIsMissing(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->expectPropertySet('configuration-attribute-is-missing', 'configuration', null);
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }
    }

    public function testConfigurationAttributeIsEmpty(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->expectPropertySet('configuration-attribute-is-empty', 'configuration', '');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }
    }

    public function testConfigurationPathDoesNotExist(): void
    {
        $this->expectBuildExceptionContaining(
            'configuration-path-does-not-exist',
            'configuration-path-does-not-exist',
            'Cannot find configuration file'
        );
    }

    public function testPropertyAttributesAreMissing(): void
    {
        $this->expectBuildExceptionContaining(
            'attributes-are-missing',
            'attributes-are-missing',
            'Property name must not be null or empty.'
        );
    }

    public function testPropertyNameIsMissing(): void
    {
        $this->expectBuildExceptionContaining(
            'name-is-missing',
            'name-is-missing',
            'Property name must not be null or empty.'
        );
    }

    private function ignoreFailureIfDueToMissingParameters(Exception $e): void
    {
        // NOTE: Execution will finally fail due to missing properties.
        // We ignore this failure, but pass ary failures that are
        // caused by other errors.
        if (
            false !== strpos(
                $e->getMessage(),
                'SonarQube Scanner misses some parameters. The following properties are mandatory'
            )
        ) {
            throw $e;
        }
    }
}
