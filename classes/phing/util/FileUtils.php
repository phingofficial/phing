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
 * File utility class.
 * - handles os independent stuff etc
 * - mapper stuff
 * - filter stuff
 *
 * @package phing.util
 */
class FileUtils
{
    /**
     * path separator string, static, obtained from FileSystem (; or :)
     *
     * @var string
     */
    public static $pathSeparator;
    /**
     * separator string, static, obtained from FileSystem
     *
     * @var string
     */
    public static $separator;

    /**
     * @throws IOException
     */
    public function __construct()
    {
        if (self::$separator === null || self::$pathSeparator === null) {
            $fs                  = FileSystem::getFileSystem();
            self::$separator     = $fs->getSeparator();
            self::$pathSeparator = $fs->getPathSeparator();
        }
    }

    /**
     * Returns the path to the temp directory.
     *
     * @return string
     */
    public static function getTempDir(): string
    {
        return Phing::getProperty('php.tmpdir');
    }

    /**
     * Returns the default file/dir creation mask value
     * (The mask value is prepared w.r.t the current user's file-creation mask value)
     *
     * @param bool $dirmode Directory creation mask to select
     *
     * @return int  Creation Mask in octal representation
     */
    public static function getDefaultFileCreationMask(bool $dirmode = false): int
    {
        // Preparing the creation mask base permission
        $permission = $dirmode === true ? 0777 : 0666;

        // Default mask information
        $defaultmask = sprintf('%03o', ($permission & ($permission - (int) sprintf('%04o', umask()))));

        return octdec($defaultmask);
    }

    /**
     * Returns a new Reader with filterchains applied.  If filterchains are empty,
     * simply returns passed reader.
     *
     * @param Reader  $in           Reader to modify (if appropriate).
     * @param array   $filterChains Filter chains to apply.
     * @param Project $project
     *
     * @return Reader  Assembled Reader (w/ filter chains).
     *
     * @throws Exception
     */
    public static function getChainedReader(Reader $in, array &$filterChains, Project $project): Reader
    {
        if (!empty($filterChains)) {
            $crh = new ChainReaderHelper();
            $crh->setBufferSize(65536); // 64k buffer, but isn't being used (yet?)
            $crh->setPrimaryReader($in);
            $crh->setFilterChains($filterChains);
            $crh->setProject($project);
            return $crh->getAssembledReader();
        }

        return $in;
    }

    /**
     * Copies a file using filter chains.
     *
     * @param PhingFile  $sourceFile
     * @param PhingFile  $destFile
     * @param Project    $project
     * @param bool       $overwrite
     * @param bool       $preserveLastModified
     * @param array|null $filterChains
     * @param int        $mode
     * @param bool       $preservePermissions
     *
     * @return void
     *
     * @throws Exception
     * @throws IOException
     */
    public function copyFile(
        PhingFile $sourceFile,
        PhingFile $destFile,
        Project $project,
        bool $overwrite = false,
        bool $preserveLastModified = true,
        ?array &$filterChains = null,
        int $mode = 0755,
        bool $preservePermissions = true
    ): void {
        if ($overwrite || !$destFile->exists() || $destFile->lastModified() < $sourceFile->lastModified()) {
            if ($destFile->exists() && ($destFile->isFile() || $destFile->isLink())) {
                $destFile->delete();
            }

            // ensure that parent dir of dest file exists!
            $parent = $destFile->getParentFile();
            if ($parent !== null && !$parent->exists()) {
                // Setting source directory permissions to target
                // (On permissions preservation, the target directory permissions
                // will be inherited from the source directory, otherwise the 'mode'
                // will be used)
                $dirMode = ($preservePermissions ? $sourceFile->getParentFile()->getMode() : $mode);

                $parent->mkdirs($dirMode);
            }

            if (is_array($filterChains) && (!empty($filterChains))) {
                $in  = self::getChainedReader(new BufferedReader(new FileReader($sourceFile)), $filterChains, $project);
                $out = new BufferedWriter(new FileWriter($destFile));

                // New read() methods returns a big buffer.
                while (-1 !== ($buffer = $in->read())) { // -1 indicates EOF
                    $out->write($buffer);
                }

                if ($in !== null) {
                    $in->close();
                }
                if ($out !== null) {
                    $out->close();
                }

                // Set/Copy the permissions on the target
                if ($preservePermissions === true) {
                    $destFile->setMode($sourceFile->getMode());
                }
            } else {
                // simple copy (no filtering)
                $sourceFile->copyTo($destFile);

                // By default, PHP::Copy also copies the file permissions. Therefore,
                // re-setting the mode with the "user file-creation mask" information.
                if ($preservePermissions === false) {
                    $destFile->setMode(self::getDefaultFileCreationMask(false));
                }
            }

            if ($preserveLastModified && !$destFile->isLink()) {
                $destFile->setLastModified($sourceFile->lastModified());
            }
        }
    }

    /**
     * Attempts to rename a file from a source to a destination.
     * If overwrite is set to true, this method overwrites existing file even if the destination file is newer.
     * Otherwise, the source file is renamed only if the destination file is older than it.
     *
     * @param PhingFile $sourceFile
     * @param PhingFile $destFile
     * @param bool      $overwrite
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function renameFile(PhingFile $sourceFile, PhingFile $destFile, bool $overwrite = false): void
    {
        // ensure that parent dir of dest file exists!
        $parent = $destFile->getParentFile();
        if ($parent !== null) {
            if (!$parent->exists()) {
                $parent->mkdirs();
            }
        }

        if ($overwrite || !$destFile->exists() || $destFile->lastModified() < $sourceFile->lastModified()) {
            if ($destFile->exists()) {
                try {
                    $destFile->delete();
                } catch (Throwable $e) {
                    throw new BuildException(
                        'Unable to remove existing file ' . $destFile->__toString() . ': ' . $e->getMessage()
                    );
                }
            }
        }

        $sourceFile->renameTo($destFile);
    }

    /**
     * Interpret the filename as a file relative to the given file -
     * unless the filename already represents an absolute filename.
     *
     * @param PhingFile $file     the "reference" file for relative paths. This
     *                            instance must be an absolute file and must
     *                            not contain ./ or ../ sequences (same for \
     *                            instead of /).
     * @param string    $filename a file name
     *
     * @return PhingFile A PhingFile object pointing to an absolute file that doesn't contain ./ or ../ sequences
     *                   and uses the correct separator for the current platform.
     *
     * @throws NullPointerException
     * @throws IOException
     */
    public function resolveFile(PhingFile $file, string $filename): PhingFile
    {
        // remove this and use the static class constant File::separator
        // as soon as ZE2 is ready
        $fs = FileSystem::getFileSystem();

        $filename = str_replace(['\\', '/'], $fs->getSeparator(), $filename);

        // deal with absolute files
        if (
            StringHelper::startsWith($fs->getSeparator(), $filename)
            || (strlen($filename) >= 2
            && Character::isLetter($filename[0])
            && $filename[1] === ':')
        ) {
            return new PhingFile($this->normalize($filename));
        }

        if (strlen($filename) >= 2 && Character::isLetter($filename[0]) && $filename[1] === ':') {
            return new PhingFile($this->normalize($filename));
        }

        $helpFile = new PhingFile($file->getAbsolutePath());

        $tok = strtok($filename, $fs->getSeparator());
        while ($tok !== false) {
            $part = $tok;
            if ($part === '..') {
                $parentFile = $helpFile->getParent();
                if ($parentFile === null) {
                    $msg = sprintf('The file or path you specified (%s) is invalid relative to %s', $filename, $file->getPath());
                    throw new IOException($msg);
                }
                $helpFile = new PhingFile($parentFile);
            } elseif ($part !== '.') {
                $helpFile = new PhingFile($helpFile, $part);
            }
            $tok = strtok($fs->getSeparator());
        }

        return new PhingFile($helpFile->getAbsolutePath());
    }

    /**
     * Normalize the given absolute path.
     *
     * This includes:
     *   - Uppercase the drive letter if there is one.
     *   - Remove redundant slashes after the drive spec.
     *   - resolve all ./, .\, ../ and ..\ sequences.
     *   - DOS style paths that start with a drive letter will have
     *     \ as the separator.
     *
     * @param string $path Path to normalize.
     *
     * @return string
     *
     * @throws IOException
     */
    public function normalize(string $path): string
    {
        $path = (string) $path;
        $orig = $path;

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

        // make sure we are dealing with an absolute path
        if (
            !StringHelper::startsWith(DIRECTORY_SEPARATOR, $path)
            && !(strlen($path) >= 2
            && Character::isLetter($path[0])
            && $path[1] === ':')
        ) {
            throw new IOException($path . ' is not an absolute path');
        }

        $dosWithDrive = false;
        $root         = null;

        // Eliminate consecutive slashes after the drive spec

        if (strlen($path) >= 2 && Character::isLetter($path[0]) && $path[1] === ':') {
            $dosWithDrive = true;

            $ca = str_replace('/', '\\', $path);

            $path = strtoupper($ca[0]) . ':';

            for ($i = 2, $_i = strlen($ca); $i < $_i; $i++) {
                if (
                    ($ca[$i] !== '\\')
                    || ($ca[$i] === '\\'
                    && $ca[$i - 1] !== '\\')
                ) {
                    $path .= $ca[$i];
                }
            }

            $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

            if (strlen($path) == 2) {
                $root = $path;
                $path = '';
            } else {
                $root = substr($path, 0, 3);
                $path = substr($path, 3);
            }
        } else {
            if (strlen($path) == 1) {
                $root = DIRECTORY_SEPARATOR;
                $path = '';
            } else {
                if ($path[1] == DIRECTORY_SEPARATOR) {
                    // UNC drive
                    $root = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
                    $path = substr($path, 2);
                } else {
                    $root = DIRECTORY_SEPARATOR;
                    $path = substr($path, 1);
                }
            }
        }

        $s   = [];
        $s[] = $root;
        $tok = strtok($path, DIRECTORY_SEPARATOR);
        while ($tok !== false) {
            $thisToken = $tok;
            if ('.' === $thisToken) {
                $tok = strtok(DIRECTORY_SEPARATOR);
                continue;
            }

            if ('..' === $thisToken) {
                if (count($s) < 2) {
                    // using '..' in path that is too short
                    throw new IOException('Cannot resolve path: ' . $orig);
                }

                array_pop($s);
            } else { // plain component
                $s[] = $thisToken;
            }
            $tok = strtok(DIRECTORY_SEPARATOR);
        }

        $sb = '';
        for ($i = 0, $_i = count($s); $i < $_i; $i++) {
            if ($i > 1) {
                // not before the filesystem root and not after it, since root
                // already contains one
                $sb .= DIRECTORY_SEPARATOR;
            }
            $sb .= (string) $s[$i];
        }

        $path = (string) $sb;
        if ($dosWithDrive === true) {
            $path = str_replace('/', '\\', $path);
        }

        return $path;
    }

    /**
     * Create a temporary file in a given directory.
     * <p>The file denoted by the returned abstract pathname did not
     * exist before this method was invoked, any subsequent invocation
     * of this method will yield a different file name.</p>
     *
     * @param string|null    $prefix       prefix before the random number.
     * @param string         $suffix       file extension; include the '.'.
     * @param PhingFile|null $parentDir    Directory to create the temporary file in;
     *                                     sys_get_temp_dir() used if not specified.
     * @param bool|null      $deleteOnExit whether to set the tempfile for deletion on
     *                                     normal exit.
     * @param bool|null      $createFile   true if the file must actually be created. If false
     *                                     chances exist that a file with the same name is
     *                                     created in the time between invoking this method
     *                                     and the moment the file is actually created. If
     *                                     possible set to true.
     *
     * @return PhingFile            a File reference to the new temporary file.
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     */
    public function createTempFile(
        ?string $prefix,
        string $suffix,
        ?PhingFile $parentDir = null,
        ?bool $deleteOnExit = false,
        ?bool $createFile = false
    ) {
        $result = null;
        $parent = $parentDir === null ? self::getTempDir() : $parentDir->getPath();

        if ($createFile) {
            try {
                $directory = new PhingFile($parent);
                // quick but efficient hack to create a unique filename ;-)
                $result = null;
                do {
                    $result = new PhingFile($directory, $prefix . substr(md5(time()), 0, 8) . $suffix);
                } while (file_exists($result->getPath()));

                $fs = FileSystem::getFileSystem();
                $fs->createNewFile($result->getPath());
                $fs->lock($result);
            } catch (IOException $e) {
                throw new BuildException('Could not create tempfile in ' . $parent, $e);
            }
        } else {
            do {
                $result = new PhingFile($parent, $prefix . substr(md5((string) time()), 0, 8) . $suffix);
            } while ($result->exists());
        }

        if ($deleteOnExit) {
            $result->deleteOnExit();
        }

        return $result;
    }

    /**
     * @param PhingFile $file1
     * @param PhingFile $file2
     *
     * @return bool Whether contents of two files is the same.
     *
     * @throws IOException
     */
    public function contentEquals(PhingFile $file1, PhingFile $file2): bool
    {
        if (!($file1->exists() && $file2->exists())) {
            return false;
        }

        if (!($file1->canRead() && $file2->canRead())) {
            return false;
        }

        if ($file1->isDirectory() || $file2->isDirectory()) {
            return false;
        }

        $c1 = file_get_contents($file1->getAbsolutePath());
        $c2 = file_get_contents($file2->getAbsolutePath());

        return trim((string) $c1) == trim((string) $c2);
    }
}
