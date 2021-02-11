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

namespace Phing\Type\Selector;

use Phing\Support\BuildFileTest;

/**
 * Class SelectorUtilsTest
 *
 * Test cases for SelectorUtils
 */
class DateSelectorTest extends BuildFileTest
{
    public const TWENTY_FOUR_HOURS_IN_SECONDS = (24 * 60 * 60);

    private $inputDir;
    private $outputDir;

    private static $fileStateMsgs = [];


    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        self::$fileStateMsgs['Before'] = [];
        self::$fileStateMsgs['Now'] = [];
        self::$fileStateMsgs['After'] = [];

        self::$fileStateMsgs['Before'][true] = 'Before file should not exist in the output directory';
        self::$fileStateMsgs['Before'][false] = 'Before file should exist in the output directory';

        self::$fileStateMsgs['Now'][true] = 'Now file should not exist in the output directory';
        self::$fileStateMsgs['Now'][false] = 'Now file should exist in the output directory';

        self::$fileStateMsgs['After'][true] = 'After file should not exist in the output directory';
        self::$fileStateMsgs['After'][false] = 'After file should exist in the output directory';
    }

    private static function getFileStateMsg(string $file, bool $isNot): string
    {
        return self::$fileStateMsgs[$file][$isNot];
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDownAfterClass()
     */
    public static function tearDownAfterClass(): void
    {
        self::$fileStateMsgs = [];
    }

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

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', false));
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

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileExists($this->outputDir . basename($afterFile), self::getFileStateMsg('After', false));
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

        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', false));
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

        $inWindowFiles = [
            '-60s' => $this->createTestFile($this->inputDir, $epochSeconds - 60),
            '-59s' => $this->createTestFile($this->inputDir, $epochSeconds - 59),
            '-30s' => $this->createTestFile($this->inputDir, $epochSeconds - 30),
            '-+0s' => $this->createTestFile($this->inputDir, $epochSeconds),
            '+30s' => $this->createTestFile($this->inputDir, $epochSeconds + 30),
            '+59s' => $this->createTestFile($this->inputDir, $epochSeconds + 59),
            '+60s' => $this->createTestFile($this->inputDir, $epochSeconds + 60),
        ];

        $outOfWindowFiles = [
            '-24h' => $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS),
            '-61s' => $this->createTestFile($this->inputDir, $epochSeconds - 61),
            '+61s' => $this->createTestFile($this->inputDir, $epochSeconds + 61),
            '+24h' => $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS),
        ];

        $this->executeTarget(__FUNCTION__);

        foreach ($inWindowFiles as $offset => $file) {
            $this->assertFileExists($this->outputDir . basename($file), $offset . ' file missing from output directory');
        }

        foreach ($outOfWindowFiles as $file) {
            $this->assertFileDoesNotExist($this->outputDir . basename($file), $offset . ' file unexpected in output directory');
        }
    }

    /*
     * Test using datetime attribute
     *
     * when defaults to equal
     * granularity defaults to 0
     */
    public function testDateTimeWithDefaults()
    {
        $dateTime = '01/01/2001 12:00 AM';

        $epochSeconds = strtotime($dateTime);

        $this->getProject()->setProperty('datetime', $dateTime);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', false));
    }

    /*
     * Test using datetime attribute with when set to after
     *
     * granularity defaults to 0
     */
    public function testDateTimeWithWhenAfter()
    {
        $dateTime = '01/01/1999 12:00 AM';

        $epochSeconds = strtotime($dateTime);

        $this->getProject()->setProperty('datetime', '01/01/1999 12:00 AM');

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileExists($this->outputDir . basename($afterFile), self::getFileStateMsg('After', false));
    }

    /*
     * Test using datetime attribute with when set to before
     *
     * granularity defaults to 0
     */
    public function testDateTimeWithWhenBefore()
    {
        $dateTime = '01/01/1975 12:00 PM';

        $epochSeconds = strtotime($dateTime);

        $this->getProject()->setProperty('datetime', $dateTime);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($beforeFile), self::getFileStateMsg('Now', false));
    }

    /*
     * Test using datetime attribute with granularity at 60
     *
     * when defaults to equal
     */
    public function testDateTimeGranularityThirtySeconds()
    {
        $dateTime = '01/01/1971 05:12 AM';

        $epochSeconds = strtotime($dateTime);

        $this->getProject()->setProperty('datetime', $dateTime);

        $inWindowFiles = [
            '-30s' => $this->createTestFile($this->inputDir, $epochSeconds - 30),
            '-29s' => $this->createTestFile($this->inputDir, $epochSeconds - 29),
            '-15s' => $this->createTestFile($this->inputDir, $epochSeconds - 15),
            '-+0s' => $this->createTestFile($this->inputDir, $epochSeconds),
            '+15s' => $this->createTestFile($this->inputDir, $epochSeconds + 15),
            '+29s' => $this->createTestFile($this->inputDir, $epochSeconds + 29),
            '+30s' => $this->createTestFile($this->inputDir, $epochSeconds + 30),
        ];

        $outOfWindowFiles = [
            '-24h' => $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS),
            '-31s' => $this->createTestFile($this->inputDir, $epochSeconds - 31),
            '+31s' => $this->createTestFile($this->inputDir, $epochSeconds + 31),
            '+24h' => $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS),
        ];

        $this->executeTarget(__FUNCTION__);

        foreach ($inWindowFiles as $offset => $file) {
            $this->assertFileExists($this->outputDir . basename($file), $offset . ' file missing from output directory');
        }

        foreach ($outOfWindowFiles as $offset => $file) {
            $this->assertFileDoesNotExist($this->outputDir . basename($file), $offset . ' file unexpected in output directory');
        }
    }

    /*
     * Test using millis attribute
     *
     * when defaults to equal
     * granularity defaults to 0
     */
    public function testMillisWithDefaults()
    {
        $epochSeconds = time();
        $epochMillis = ($epochSeconds * 1000) + rand(0, 999);

        $this->getProject()->setProperty('epoch.millis', $epochMillis);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - 60 * 1000);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + 60 * 1000);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', false));
    }


    /*
     * Test using millis attribute with when set to after
     *
     * granularity defaults to 0
     */
    public function testMillisWithWhenAfter()
    {
        $epochSeconds = time();
        $epochMillis = ($epochSeconds * 1000) + rand(0, 999);

        $this->getProject()->setProperty('epoch.millis', $epochMillis);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileExists($this->outputDir . basename($afterFile), self::getFileStateMsg('After', false));
    }

    /*
     * Test using millis attribute with when set to before
     *
     * granularity defaults to 0
     */
    public function testMillisWithWhenBefore()
    {
        $epochSeconds = time();
        $epochMillis = ($epochSeconds * 1000) + rand(0, 999);

        $this->getProject()->setProperty('epoch.millis', $epochMillis);

        $beforeFile = $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS);
        $nowFile = $this->createTestFile($this->inputDir, $epochSeconds);
        $afterFile = $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS);

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist($this->outputDir . basename($nowFile), self::getFileStateMsg('Now', true));
        $this->assertFileDoesNotExist($this->outputDir . basename($afterFile), self::getFileStateMsg('After', true));
        $this->assertFileExists($this->outputDir . basename($beforeFile), self::getFileStateMsg('Before', false));
    }

    /*
     * Test using millis attribute with granularity at 60
     *
     * when defaults to equal
     */
    public function testMillisGranularitySixSeconds()
    {
        $epochSeconds = time();
        $epochMillis = ($epochSeconds * 1000) + rand(0, 999);

        $this->getProject()->setProperty('epoch.millis', $epochMillis);

        $inWindowFiles = [
            '-6s' => $this->createTestFile($this->inputDir, $epochSeconds - 6),
            '-5s' => $this->createTestFile($this->inputDir, $epochSeconds - 5),
            '-3s' => $this->createTestFile($this->inputDir, $epochSeconds - 3),
            '+-0s' => $this->createTestFile($this->inputDir, $epochSeconds),
            '+3s' => $this->createTestFile($this->inputDir, $epochSeconds + 3),
            '+5s' => $this->createTestFile($this->inputDir, $epochSeconds + 5),
            '+6s' => $this->createTestFile($this->inputDir, $epochSeconds + 6),
        ];

        $outOfWindowFiles = [
            '-24h' => $this->createTestFile($this->inputDir, $epochSeconds - self::TWENTY_FOUR_HOURS_IN_SECONDS),
            '-7s' => $this->createTestFile($this->inputDir, $epochSeconds - 7),
            '+7s' => $this->createTestFile($this->inputDir, $epochSeconds + 7),
            '+24h' => $this->createTestFile($this->inputDir, $epochSeconds + self::TWENTY_FOUR_HOURS_IN_SECONDS),
        ];

        $this->executeTarget(__FUNCTION__);

        foreach ($inWindowFiles as $offset => $file) {
            $this->assertFileExists($this->outputDir . basename($file), $offset . ' file missing from the output directory');
        }

        foreach ($outOfWindowFiles as $offset => $file) {
            $this->assertFileDoesNotExist($this->outputDir . basename($file), $offset . ' file unexpected in the output directory');
        }
    }

    /*
     * Test using seconds attribute
     *
     * when defaults to equal
     * granularity defaults to 0
     */
    public function testSecondsInvalidSeconds()
    {
        $this->expectBuildException(__FUNCTION__, 'seconds has invalid value');
    }

    /*
     * Test using datetime attribute with and invalid value
     */
    public function testDateTimeInvalidDateTime()
    {
        $this->expectBuildException(__FUNCTION__, 'datetime has invalid value');
    }

    /*
     * Test using invalid datetime attribute
     */
    public function testDateTimeNotDateTime()
    {
        $this->expectBuildException(__FUNCTION__, 'datetime has invalid value');
    }

    /*
     * Test using invalid millis attribute
     */
    public function testMillisInvalidMillis()
    {
        $this->expectBuildException(__FUNCTION__, 'millis has invalid value');
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
        $this->expectBuildException(__FUNCTION__, 'invalid attribute for task');
    }
}
