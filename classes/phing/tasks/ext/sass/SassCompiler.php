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

class SassCompiler implements SassTaskCompiler
{
    /**
     * @var string
     */
    private $executable;

    /**
     * @var string
     */
    private $flags;

    /**
     * @param string $executable
     * @param string $flags
     */
    public function __construct(string $executable, string $flags)
    {
        $this->executable = $executable;
        $this->flags      = $flags;
    }

    /**
     * @param string $inputFilePath
     * @param string $outputFilePath
     * @param bool   $failOnError
     *
     * @return void
     */
    public function compile(string $inputFilePath, string $outputFilePath, bool $failOnError): void
    {
        try {
            $output = $this->executeCommand($inputFilePath, $outputFilePath);
            if ($failOnError && $output[0] !== 0) {
                throw new BuildException(
                    'Result returned as not 0. Result: ' . $output[0],
                    Project::MSG_INFO
                );
            }
        } catch (Throwable $e) {
            if ($failOnError) {
                throw new BuildException($e);
            }
        }
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @param string $inputFile  Input file
     * @param string $outputFile Output file
     *
     * @return array array(return code, array with output)
     *
     * @throws BuildException
     *
     * @access protected
     */
    private function executeCommand(string $inputFile, string $outputFile): array
    {
        // Prevent over-writing existing file.
        if ($inputFile == $outputFile) {
            throw new BuildException('Input file and output file are the same!');
        }

        $output = [];
        $return = null;

        $fullCommand = $this->executable;

        if (strlen($this->flags) > 0) {
            $fullCommand .= ' ' . $this->flags;
        }

        $fullCommand .= ' ' . $inputFile . ' ' . $outputFile;

        exec($fullCommand, $output, $return);

        return [$return, $output];
    }
}
