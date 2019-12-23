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

/**
 * Runs SonarQube Scanner.
 *
 * @see     http://www.sonarqube.org
 *
 * @author  Bernhard Mendl <mail@bernhard-mendl.de>
 * @package phing.tasks.ext.sonar
 */
class SonarTask extends Task
{
    public const EXIT_SUCCESS = 0;

    /**
     * @var string|null
     */
    private $executable = null;

    /**
     * @var string
     */
    private $errors = 'false';

    /**
     * @var string
     */
    private $debug = 'false';

    /**
     * @var string|null
     */
    private $configuration = null;

    /**
     * @see Property
     *
     * @var SonarProperty[] Nested *Property* elements.
     */
    private $propertyElements = [];

    /**
     * The command-line options passed to the SonarQube Scanner executable.
     *
     * @var array
     */
    private $commandLineOptions = [];

    /**
     * Map containing SonarQube's "analysis parameters".
     *
     * Map keys are SonarQube parameter names. Map values are parameter values.
     * See {@link http://docs.sonarqube.org/display/SONAR/Analysis+Parameters}.
     *
     * @var array
     */
    private $properties = [];

    /**
     * Sets the path of the SonarQube Scanner executable.
     * If the SonarQube Scanner is included in the PATH environment variable,
     * the file name is sufficient.
     *
     * @param string $executable
     *
     * @return void
     *
     * @throws Exception
     */
    public function setExecutable(string $executable): void
    {
        $this->executable = (string) $executable;

        $message = sprintf('Set executable to [%s].', $this->executable);
        $this->log($message, Project::MSG_DEBUG);
    }

    /**
     * Sets or unsets the "--errors" flag of SonarQube Scanner.
     *
     * @param string $errors Allowed values are "true"/"false", "yes"/"no", or "1"/"0".
     *
     * @return void
     *
     * @throws Exception
     */
    public function setErrors(string $errors): void
    {
        $this->errors = strtolower((string) $errors);

        $message = sprintf('Set errors flag to [%s].', $this->errors);
        $this->log($message, Project::MSG_DEBUG);
    }

    /**
     * Sets or unsets the "--debug" flag of SonarQube Scanner.
     *
     * @param string $debug Allowed values are "true"/"false", "yes"/"no", or "1"/"0".
     *
     * @return void
     *
     * @throws Exception
     */
    public function setDebug(string $debug): void
    {
        $this->debug = strtolower((string) $debug);

        $message = sprintf('Set debug flag to [%s].', $this->debug);
        $this->log($message, Project::MSG_DEBUG);
    }

    /**
     * Sets the path of a configuration file for SonarQube Scanner.
     *
     * @param string $configuration
     *
     * @return void
     *
     * @throws Exception
     */
    public function setConfiguration(string $configuration): void
    {
        $this->configuration = (string) $configuration;

        $message = sprintf('Set configuration to [%s].', $this->configuration);
        $this->log($message, Project::MSG_DEBUG);
    }

    /**
     * Adds a nested Property element.
     *
     * @param SonarProperty $property
     *
     * @return void
     *
     * @throws Exception
     */
    public function addProperty(SonarProperty $property): void
    {
        $this->propertyElements[] = $property;

        $message = sprintf('Added property: [%s] = [%s].', $property->getName(), $property->getValue());
        $this->log($message, Project::MSG_DEBUG);
    }

    /**
     * {@inheritdoc}
     *
     * @see Task::init()
     *
     * @return void
     */
    public function init(): void
    {
        $this->checkExecAllowed();
    }

    /**
     * {@inheritdoc}
     *
     * @see Task::main()
     *
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        $this->validateErrors();
        $this->validateDebug();
        $this->validateConfiguration();
        $this->validateProperties();
        $this->validateExecutable();

        $command = sprintf('%s %s', escapeshellcmd($this->executable), $this->constructOptionsString());

        $message = sprintf('Executing: [%s]', $command);
        $this->log($message, Project::MSG_VERBOSE);

        exec($command, $output, $returnCode);

        foreach ($output as $line) {
            $this->log($line);
        }

        if ($returnCode !== self::EXIT_SUCCESS) {
            throw new BuildException('Execution of SonarQube Scanner failed.');
        }
    }

    /**
     * Constructs command-line options string for SonarQube Scanner.
     *
     * @return string
     */
    private function constructOptionsString()
    {
        $options = implode(' ', $this->commandLineOptions);

        foreach ($this->properties as $name => $value) {
            $arg      = sprintf('%s=%s', $name, $value);
            $options .= ' -D ' . escapeshellarg($arg);
        }

        return $options;
    }

    /**
     * Check whether PHP function 'exec()' is available.
     *
     * @return void
     *
     * @throws BuildException
     */
    private function checkExecAllowed(): void
    {
        if (!function_exists('exec') || !is_callable('exec')) {
            $message = 'Cannot execute SonarQube Scanner because calling PHP function exec() is not permitted by PHP configuration.';
            throw new BuildException($message);
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    private function validateExecutable(): void
    {
        if (($this->executable === null) || ($this->executable === '')) {
            $message = 'You must specify the path of the SonarQube Scanner using the "executable" attribute.';
            throw new BuildException($message);
        }

        // Note that executable is used as argument here.
        $escapedExecutable = escapeshellarg($this->executable);

        if ($this->isWindows()) {
            $message = 'Assuming a Windows system. Looking for SonarQube Scanner ...';
            $command = 'where ' . $escapedExecutable;
        } else {
            $message = 'Assuming a Linux or Mac system. Looking for SonarQube Scanner ...';
            $command = 'which ' . $escapedExecutable;
        }

        $this->log($message, Project::MSG_VERBOSE);
        unset($output);
        exec($command, $output, $returnCode);

        if ($returnCode !== self::EXIT_SUCCESS) {
            $message = sprintf('Cannot find SonarQube Scanner: [%s].', $this->executable);
            throw new BuildException($message);
        }

        // Verify that executable is indeed SonarQube Scanner ...
        $escapedExecutable = escapeshellcmd($this->executable);
        unset($output);
        exec($escapedExecutable . ' --version', $output, $returnCode);

        if ($returnCode !== self::EXIT_SUCCESS) {
            $message = sprintf(
                'Could not check version string. Executable appears not to be SonarQube Scanner: [%s].',
                $this->executable
            );
            throw new BuildException($message);
        }

        $isOk = false;
        foreach ($output as $line) {
            if (preg_match('/SonarQube Scanner \d+\\.\d+/', (string) $line) === 1) {
                $isOk = true;
                break;
            }
        }

        if ($isOk) {
            $message = sprintf('Found SonarQube Scanner: [%s].', $this->executable);
            $this->log($message, Project::MSG_VERBOSE);
        } else {
            $message = sprintf(
                'Could not find name of SonarQube Scanner in version string. Executable appears not to be SonarQube Scanner: [%s].',
                $this->executable
            );
            throw new BuildException($message);
        }
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    private function validateErrors(): void
    {
        if (($this->errors === '1') || ($this->errors === 'true') || ($this->errors === 'yes')) {
            $errors = true;
        } elseif (($this->errors === '0') || ($this->errors === 'false') || ($this->errors === 'no')) {
            $errors = false;
        } else {
            throw new BuildException('Expected a boolean value.');
        }

        if ($errors) {
            $this->commandLineOptions[] = '--errors';
        }
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    private function validateDebug(): void
    {
        if (($this->debug === '1') || ($this->debug === 'true') || ($this->debug === 'yes')) {
            $debug = true;
        } elseif (($this->debug === '0') || ($this->debug === 'false') || ($this->debug === 'no')) {
            $debug = false;
        } else {
            throw new BuildException('Expected a boolean value.');
        }

        if ($debug) {
            $this->commandLineOptions[] = '--debug';
        }
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    private function validateConfiguration(): void
    {
        if (($this->configuration === null) || ($this->configuration === '')) {
            // NOTE: Ignore an empty configuration. This allows for
            // using Phing properties as attribute values, e.g.
            // <sonar ... configuration="{sonar.config.file}">.
            return;
        }

        if (!@file_exists($this->configuration)) {
            $message = sprintf('Cannot find configuration file [%s].', $this->configuration);
            throw new BuildException($message);
        }

        if (!@is_readable((string) $this->configuration)) {
            $message = sprintf('Cannot read configuration file [%s].', $this->configuration);
            throw new BuildException($message);
        }

        // TODO: Maybe check file type?
    }

    /**
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    private function validateProperties(): void
    {
        $this->properties = $this->parseConfigurationFile();

        foreach ($this->propertyElements as $property) {
            $name  = $property->getName();
            $value = $property->getValue();

            if ($name === null || $name === '') {
                throw new BuildException('Property name must not be null or empty.');
            }

            if (array_key_exists($name, $this->properties)) {
                $message = sprintf(
                    'Property [%s] overwritten: old value [%s], new value [%s].',
                    $name,
                    $this->properties[$name],
                    $value
                );
                $this->log($message, Project::MSG_WARN);
            }

            $this->properties[$name] = $value;
        }

        // Check if all properties required by SonarQube Scanner are set ...
        $requiredProperties = [
            'sonar.projectKey',
            'sonar.projectName',
            'sonar.projectVersion',
            'sonar.sources',
        ];
        $intersection       = array_intersect($requiredProperties, array_keys($this->properties));
        if (count($intersection) < count($requiredProperties)) {
            $message = 'SonarQube Scanner misses some parameters. The following properties are mandatory: ' . implode(
                ', ',
                $requiredProperties
            ) . '.';
            throw new BuildException($message);
        }
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    private function parseConfigurationFile(): array
    {
        if (($this->configuration === null) || ($this->configuration === '')) {
            return [];
        }

        $parser = new SonarConfigurationFileParser($this->configuration, $this->project);
        return $parser->parse();
    }

    /**
     * @return bool
     */
    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
