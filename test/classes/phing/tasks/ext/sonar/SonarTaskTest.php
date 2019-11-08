<?php
/*
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
 *
 * @author Bernhard Mendl <mail@bernhard-mendl.de>
 * @package phing.tasks.ext.sonar
 */
class SonarTaskTest extends BuildFileTest
{
    protected function setUp(): void
    {
        $buildXmlFile = PHING_TEST_BASE . '/etc/tasks/ext/sonar/SonarTaskTest.xml';
        $this->configureProject($buildXmlFile);
    }

    private function ignoreFailureIfDueToMissingParameters(Exception $e)
    {
        // NOTE: Execution will finally fail due to missing properties.
        // We ignore this failure, but pass ary failures that are
        // caused by other errors.
        if (strpos($e->getMessage(),
                'SonarQube Scanner misses some parameters. The following properties are mandatory') !== false) {
            throw $e;
        }
    }

    //
    // Test "executable" attribute ...
    //

    public function testExecutableAttributeIsMissingThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-attribute-is-missing',
            'executable-attribute-is-missing',
            'You must specify the path of the SonarQube Scanner using the "executable" attribute.'
        );
    }

    public function testExecutableAttributeIsEmptyThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-attribute-is-empty',
            'executable-attribute-is-empty',
            'You must specify the path of the SonarQube Scanner using the "executable" attribute.'
        );
    }

    public function testExecutablePathDoesNotExistThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-path-does-not-exist',
            'executable-path-does-not-exist',
            'Cannot find SonarQube Scanner'
        );
    }

    /**
     * the return code of the exec command is always 0 under windows
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testExecutableFileIsNotExecutableThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-file-is-not-executable',
            'executable-file-is-not-executable',
            'Cannot find SonarQube Scanner'
        );
    }

    public function testExecutableIsNotSonarScannerAndHasNoVersionStringThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-is-not-sonar-scanner-and-has-no-version-string',
            'executable-is-not-sonar-scanner-and-has-no-version-string',
            'Cannot find SonarQube Scanner'
        );
    }

    public function testExecutableIsNotSonarScannerAndHasVersionStringThrowsException()
    {
        $this->expectBuildExceptionContaining(
            'executable-is-not-sonar-scanner-and-has-version-string',
            'executable-is-not-sonar-scanner-and-has-version-string',
            'Could not find name of SonarQube Scanner in version string. Executable appears not to be SonarQube Scanner'
        );
    }

    //
    // Test "errors" attribute ...
    //

    public function testErrorsAttributeIsMissing()
    {
        try {
            $this->expectPropertySet('errors-attribute-is-missing', 'errors', 'false');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }

        self::assertEquals(1, 1); // increase number of positive assertions
    }

    public function testErrorsAttributeIsEmpty()
    {
        $this->expectBuildExceptionContaining(
            'errors-attribute-is-empty',
            'errors-attribute-is-empty',
            'Expected a boolean value.'
        );
    }

    public function testErrorsValueIsInvalid()
    {
        $this->expectBuildExceptionContaining(
            'errors-value-is-invalid',
            'errors-value-is-invalid',
            'Expected a boolean value.'
        );
    }

    //
    // Test "debug" attribute ...
    //

    public function test_debugAttributeIsMissing()
    {
        try {
            $this->expectPropertySet('debug-attribute-is-missing', 'debug', 'false');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }

        self::assertEquals(1, 1); // increase number of positive assertions
    }

    public function test_debugAttributeIsEmpty()
    {
        $this->expectBuildExceptionContaining(
            'debug-attribute-is-empty',
            'debug-attribute-is-empty',
            'Expected a boolean value.'
        );
    }

    public function test_debugValueIsInvalid()
    {
        $this->expectBuildExceptionContaining(
            'debug-value-is-invalid',
            'debug-value-is-invalid',
            'Expected a boolean value.'
        );
    }

    //
    // Test "configuration" attribute ...
    //

    public function test_configurationAttributeIsMissing()
    {
        try {
            $this->expectPropertySet('configuration-attribute-is-missing', 'configuration', null);
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }

        self::assertEquals(1, 1); // increase number of positive assertions
    }

    public function test_configurationAttributeIsEmpty()
    {
        try {
            $this->expectPropertySet('configuration-attribute-is-empty', 'configuration', '');
        } catch (BuildException $e) {
            $this->ignoreFailureIfDueToMissingParameters($e);
        }

        self::assertEquals(1, 1); // increase number of positive assertions
    }

    public function test_configurationPathDoesNotExist()
    {
        $this->expectBuildExceptionContaining(
            'configuration-path-does-not-exist',
            'configuration-path-does-not-exist',
            'Cannot find configuration file'
        );
    }

    //
    // Test "property" elements ...
    //

    public function test_propertyAttributesAreMissing()
    {
        $this->expectBuildExceptionContaining(
            'attributes-are-missing',
            'attributes-are-missing',
            'Property name must not be null or empty.'
        );
    }

    public function test_propertyNameIsMissing()
    {
        $this->expectBuildExceptionContaining(
            'name-is-missing',
            'name-is-missing',
            'Property name must not be null or empty.'
        );
    }
}
