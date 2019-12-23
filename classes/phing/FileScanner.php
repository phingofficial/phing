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
 * An interface used to describe the actions required of any type of
 * directory scanner.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing
 */
interface FileScanner
{
    /**
     * Adds default exclusions to the current exclusions set.
     *
     * @return void
     */
    public function addDefaultExcludes(): void;

    /**
     * Returns the base directory to be scanned.
     * This is the directory which is scanned recursively.
     *
     * @return string the base directory to be scanned
     */
    public function getBasedir(): string;

    /**
     * Returns the names of the directories which matched at least one of the
     * include patterns and at least one of the exclude patterns.
     * The names are relative to the base directory.
     *
     * @return string[] the names of the directories which matched at least one of the
     *                  include patterns and at least one of the exclude patterns.
     */
    public function getExcludedDirectories(): array;

    /**
     * Returns the names of the files which matched at least one of the
     * include patterns and at least one of the exclude patterns.
     * The names are relative to the base directory.
     *
     * @return string[] the names of the files which matched at least one of the
     *                  include patterns and at least one of the exclude patterns.
     */
    public function getExcludedFiles(): array;

    /**
     * Returns the names of the directories which matched at least one of the
     * include patterns and none of the exclude patterns.
     * The names are relative to the base directory.
     *
     * @return string[] the names of the directories which matched at least one of the
     *                  include patterns and none of the exclude patterns.
     *
     * @throws UnexpectedValueException
     */
    public function getIncludedDirectories(): array;

    /**
     * Returns the names of the files which matched at least one of the
     * include patterns and none of the exclude patterns.
     * The names are relative to the base directory.
     *
     * @return string[] the names of the files which matched at least one of the
     *                  include patterns and none of the exclude patterns.
     *
     * @throws UnexpectedValueException
     */
    public function getIncludedFiles(): array;

    /**
     * Returns the names of the directories which matched none of the include
     * patterns. The names are relative to the base directory.
     *
     * @return string[] the names of the directories which matched none of the include
     *                  patterns.
     */
    public function getNotIncludedDirectories(): array;

    /**
     * Returns the names of the files which matched none of the include
     * patterns. The names are relative to the base directory.
     *
     * @return array the names of the files which matched none of the include
     *         patterns.
     */
    public function getNotIncludedFiles(): array;

    /**
     * Scans the base directory for files which match at least one include
     * pattern and don't match any exclude patterns.
     *
     * @return bool
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function scan(): bool;

    /**
     * Sets the base directory to be scanned. This is the directory which is
     * scanned recursively. All '/' and '\' characters should be replaced by
     * <code>File.separatorChar</code>, so the separator used need not match
     * <code>File.separatorChar</code>.
     *
     * @param string $basedir The base directory to scan.
     *                        Must not be <code>null</code>.
     *
     * @return void
     */
    public function setBasedir(string $basedir): void;

    /**
     * Sets the list of exclude patterns to use.
     *
     * @param string[] $excludes A list of exclude patterns.
     *                           May be <code>null</code>, indicating that no files
     *                           should be excluded. If a non-<code>null</code> list is
     *                           given, all elements must be non-<code>null</code>.
     *
     * @return void
     */
    public function setExcludes(array $excludes = []): void;

    /**
     * Sets the list of include patterns to use.
     *
     * @param string[]|null $includes A list of include patterns.
     *                                May be <code>null</code>, indicating that all files
     *                                should be included. If a non-<code>null</code>
     *                                list is given, all elements must be
     * non-<code>null</code>.
     *
     * @return void
     */
    public function setIncludes(?array $includes = []): void;

    /**
     * Sets whether or not the file system should be regarded as case sensitive.
     *
     * @param bool $isCaseSensitive whether or not the file system should be
     *                              regarded as a case sensitive one
     *
     * @return void
     */
    public function setCaseSensitive(bool $isCaseSensitive): void;
}
