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
 * Remove from cache files where contains errors
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Rui Filipe Da Cunha Alves <ruifil@ruifil.com>
 */
class ReportsPhingRemoveFromCache implements PHP_CodeSniffer_Report
{
    /**
     * Cache data storage
     *
     * @var DataStore
     */
    protected static $cache;

    /**
     * Set cache object
     *
     * @param DataStore $cache
     *
     * @return void
     */
    public static function setCache(DataStore $cache): void
    {
        self::$cache = $cache;
    }

    /**
     * Remove file from cache if contains errors
     *
     * @param array                $report      Prepared report data.
     * @param PHP_CodeSniffer_File $phpcsFile   The file being reported on.
     * @param bool                 $showSources Show sources?
     * @param int                  $width       Maximum allowed line width.
     *
     * @return bool
     *
     * @throws IOException
     */
    public function generateFileReport(
        array $report,
        PHP_CodeSniffer_File $phpcsFile,
        bool $showSources = false,
        int $width = 80
    ): bool {
        if (!self::$cache || ($report['errors'] === 0 && $report['warnings'] === 0)) {
            // Nothing to do
            return false;
        }

        self::$cache->remove($report['filename']);
        return false;
    }

    /**
     * Do nothing
     *
     * @param string $cachedData    Any partial report data that was returned from
     *                              generateFileReport during the run.
     * @param int    $totalFiles    Total number of files processed during the run.
     * @param int    $totalErrors   Total number of errors found during the run.
     * @param int    $totalWarnings Total number of warnings found during the run.
     * @param int    $totalFixable  Total number of problems that can be fixed.
     * @param bool   $showSources   Show sources?
     * @param int    $width         Maximum allowed line width.
     * @param bool   $toScreen      Is the report being printed to screen?
     *
     * @return void
     */
    public function generate(
        string $cachedData,
        int $totalFiles,
        int $totalErrors,
        int $totalWarnings,
        int $totalFixable,
        bool $showSources = false,
        int $width = 80,
        bool $toScreen = true
    ): void {
        // Do nothing
    }
}
