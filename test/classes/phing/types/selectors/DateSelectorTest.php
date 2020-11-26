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


/**
 * Class SelectorUtilsTest
 *
 * Test cases for SelectorUtils
 */
class DateSelectorTest extends BuildFileTest
{
    const TWENTY_FOUR_HOURS_IN_SECONDS = (24 * 60 * 60);

    private $inputDir;
    private $outputDir;

    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/DateSelectorTest.xml'
        );
        $this->executeTarget('setup');

        $project = $this->getProject();
        $this->inputDir = $project->getProperty('input.dir');
        $this->outputDir = rtrim($project->getProperty('output.dir'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * Creates a test file in the specified directory with the specified
     * creation time.
     *
     * @param string $dir The directory where the test file should be created.
     * @param int $timestamp The timestamp to touch the file with (in seconds
     *              since the epoch).
     *
     * @return string The fully qualified name of the test file.
     */
    private function createTestFile(string $dir, int $timestamp): string
    {
        $file = tempnam($dir, 'dst');
        $writeCnt = file_put_contents($file, 'DateSelectorTest file');
        if (false !== $writeCnt) {
            touch($file, $timestamp);
        } else {
            $this->fail('Unable to create test file: ' . $file);
        }

        return $file;
    }

    /*
     * Test using seconds attribute
     *
     * when defaults to equal
     * granularity defaults to 0
     */
    public function testSecondsWithDefaults()
    {
        $epochSeconds = time();

        $this->getProject()->setProperty('epoch.seconds', $epochSeconds);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile));
        $this->assertFileExists($this->outputDir . basename($nowFile));
    }

    /*
     * Test using seconds attribute with when set to after
     *
     * granularity defaults to 0
     */
    public function testSecondsWithWhenAfter()
    {
        $epochSeconds = time();

        $this->getProject()->setProperty('epoch.seconds', $epochSeconds);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile));
        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile));
        $this->assertFileExists($this->outputDir . basename($afterFile));
    }

    /*
     * Test using seconds attribute with when set to before
     *
     * granularity defaults to 0
     */
    public function testSecondsWithWhenBefore()
    {
        $epochSeconds = time();

        $this->getProject()->setProperty('epoch.seconds', $epochSeconds);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile));
        $this->assertFileExists($this->outputDir . basename($beforeFile));
    }

    /*
     * Test using seconds attribute with granularity at 60
     *
     * when defaults to equal
     */
    public function testSecondsGranularitySixtySeconds()
    {
        $epochSeconds = time();

        $this->getProject()->setProperty('epoch.seconds', $epochSeconds);

        $inWindowFiles = array(
            $this->createTestFile($this->inputDir, $epochSeconds - 60),
            $this->createTestFile($this->inputDir, $epochSeconds - 59),
            $this->createTestFile($this->inputDir, $epochSeconds - 30),
            $this->createTestFile($this->inputDir, $epochSeconds),
            $this->createTestFile($this->inputDir, $epochSeconds + 30),
            $this->createTestFile($this->inputDir, $epochSeconds + 59),
            $this->createTestFile($this->inputDir, $epochSeconds + 60)
        );

        $outOfWindowFiles = array(
            $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS),
            $this->createTestFile($this->inputDir, $epochSeconds - 61),
            $this->createTestFile($this->inputDir, $epochSeconds + 61),
            $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS)
        );

        $this->executeTarget(__FUNCTION__);

        foreach ($inWindowFiles as $file) {
            $this->assertFileExists($this->outputDir . basename($file));
        }

        foreach ($outOfWindowFiles as $file) {
            $this->assertFileDoesNotExist($this->outputDir . basename($file));
        }
    }

    /*
     * Test using millis attribute
     *
     * when defaults to equal
     * granularity defaults to 0
     */
    public function testMillis()
    {
        $epochSeconds = time();
        $epochMillis = $epochSeconds * 1000;

        $this->getProject()->setProperty('epoch.millis', $epochMillis);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - 60);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + 60);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile));
        $this->assertFileExists($this->outputDir . basename($nowFile));
    }

    /*
     * Test using an invalid when value
     */
    public function testInvalidWhen()
    {
        $this->expectBuildException(__FUNCTION__, 'when attribute has invalid value');
    }

    /*
     * Test using an invalid when value
     */
    public function testInvalidAttribute()
    {
        $this->expectBuildException(__FUNCTION__, 'invalid attribut for task');
    }
}
