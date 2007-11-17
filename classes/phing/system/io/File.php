<?php
/*
 *  $Id$
 *
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

namespace phing::system::io;
use phing::system::io::FileSystem;
use phing::system::lang::NullPointerException;

/**
 * An representation of file and directory pathnames.
 *
 * @package   phing.system.io
 */
class File {

    /**
     * Separator string, static, obtained from FileSystem.
     * @see FileSystem::getSeparator()
	 */
    public static $separator;

    /**
	 * Path separator string, static, obtained from FileSystem (; or :)
	 * @see FileSystem::getSeparator()
	 */
    public static $pathSeparator;
    
    /**
     * This abstract pathname's normalized pathname string.  A normalized
     * pathname string uses the default name-separator character and does not
     * contain any duplicate or redundant separators.
     * @var string
     */
    private $path;

    /**
	 * The length of this abstract pathname's prefix, or zero if it has no prefix.
	 * @var int
	 */
    private $prefixLength = 0;

    /**
     * Create a new File object.
     * 
     * This method supports sevarl valid signatures:
     * 	new File(File parent, string filename)
     *  new File(string filename)
     *  new File(string parent, string filename)
	 */
    function __construct($arg1, $arg2 = null) {
        
        if (self::$separator === null || self::$pathSeparator === null) {
            $fs = FileSystem::getFileSystem();
            self::$separator = $fs->getSeparator();
            self::$pathSeparator = $fs->getPathSeparator();
        }

        /* simulate constructor overloading */
        if ($arg1 instanceof File && is_string($arg2)) {
            $this->__constructFileParentStringChild($arg1, $arg2);
        } elseif (is_string($arg1) && ($arg2 === null)) {
            $this->__constructPathname($arg1);
        } elseif(is_string($arg1) && is_string($arg2)) {
            $this->__constructStringParentStringChild($arg1, $arg2);
        } else {
            if ($arg1 === null) {
                throw new NullPointerException("Argument1 to function must not be null");
            }
            $this->path = (string) $arg1;
            $this->prefixLength = (int) $arg2;
        }
    }
    
    /**
	 * Private overloaded constructor when passed a File parent path and string child path.
	 * @param File $parent The parent path
	 * @param string $child (optional) The child path
	 */
    private function __constructFileParentStringChild(File $parent, $child) {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($child === null) {
            throw new NullPointerException("Argument to function must not be null");
        }

        if ($parent !== null) {
            if ($parent->getPath() === "") {
                $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
            } else {
                $this->path = $fs->resolve($parent->getPath(), $fs->normalize($child));
            }
        } else {
            $this->path = $fs->normalize($child);
        }
        $this->prefixLength = $fs->prefixLength($this->path);
    }
    
	/**
	 * Private constructor when passed a single path string.
	 *
	 * @param string $pathname
	 */
    private function __constructPathname($pathname) {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($pathname === null) {
            throw new NullPointerException("Argument to function must not be null");
        }

        $this->path = (string) $fs->normalize($pathname);
        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }
	
    /**
	 * Private overloaded constructor when passed a string parent path and child paths.
	 * @param string $parent The parent path
	 * @param string $child (optional) The child path
	 */
    private function __constructStringParentStringChild($parent, $child) {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($child === null) {
            throw new NullPointerException("Argument to function must not be null");
        }
        if ($parent !== null) {
            if ($parent === "") {
                $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
            } else {
                $this->path = $fs->resolve($fs->normalize($parent), $fs->normalize($child));
            }
        } else {
            $this->path = (string) $fs->normalize($child);
        }
        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }
	
    /**
	 * Returns the length of this abstract pathname's prefix.
	 * @return int
	 */
    function getPrefixLength() {
        return (int) $this->prefixLength;
    }

    /* -- Path-component accessors -- */

    /**
     * Returns the name of the file or directory denoted by this abstract
     * pathname.  This is just the last name in the pathname's name
     * sequence.  If the pathname's name sequence is empty, then the empty
     * string is returned.
     *
     * @return string The name of the file or directory.
     */
    public function getName() {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, self::$separator)) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            return substr($this->path, $this->prefixLength);
        }
        return substr($this->path, $index + 1);
    }

    /**
     * Returns the pathname string of this abstract pathname's parent, or
     * null if this pathname does not name a parent directory.
     *
     * The parent of an abstract pathname consists of the pathname's prefix,
     * if any, and each name in the pathname's name sequence except for the last.
     * If the name sequence is empty then the pathname does not name a parent
     * directory.
     *
     * @return string The pathname string of the parent directory.
     */
    public function getParent() {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, self::$separator)) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            if (($this->prefixLength > 0) && (strlen($this->path > $this->prefixLength))) {
                return substr($this->path, 0, $this->prefixLength);
            }
            return null;
        }
        return substr($this->path, 0, $index);
    }

    /**
     * Returns the parent directory as File object.
     *
     * @return File A File of the parent directory for this file. 
     */
    public function getParentFile() {
        $p = $this->getParent();
        if ($p === null) {
            return null;
        }
        return new File((string) $p, (int) $this->prefixLength);
    }

    /**
     * Converts this abstract pathname into a pathname string.  The resulting
     * string uses the default name-separator character to separate the names
     * in the name sequence.
     *
     * @return string The string form of this abstract pathname
     */
    public function getPath() {
        return (string) $this->path;
    }

    /**
     * Tests whether this abstract pathname is absolute.  The definition of
     * absolute pathname is system dependent.  On UNIX systems, a pathname is
     * absolute if its prefix is "/".  On Win32 systems, a pathname is absolute
     * if its prefix is a drive specifier followed by "\\", or if its prefix
     * is "\\".
     *
     * @return boolean true if this abstract pathname is absolute, false otherwise
     */
    public function isAbsolute() {
        return ($this->prefixLength !== 0);
    }

    /**
     * Returns the absolute pathname string of this abstract pathname.
     *
     * If this abstract pathname is already absolute, then the pathname
     * string is simply returned as if by the getPath method.
     * If this abstract pathname is the empty abstract pathname then
     * the pathname string of the current user directory, which is named by the
     * system property user.dir, is returned.  Otherwise this
     * pathname is resolved in a system-dependent way.  On UNIX systems, a
     * relative pathname is made absolute by resolving it against the current
     * user directory.  On Win32 systems, a relative pathname is made absolute
     * by resolving it against the current directory of the drive named by the
     * pathname, if any; if not, it is resolved against the current user
     * directory.
     *
     * @return  string The absolute pathname of this file/directory.
     * @see     isAbsolute()
     */
    public function getAbsolutePath() {
        $fs = FileSystem::getFileSystem();        
        return $fs->resolveFile($this);
    }

    /**
     * Returns a File object containing abs path to this file/dir.
     *
     * @see getAbsolutePath()
     * @return File A File object containing the absolute path to this file/dir.
     */
    public function getAbsoluteFile() {
        return new File((string) $this->getAbsolutePath());
    }

    /**
     * Returns the canonical pathname string of this abstract pathname.
     *
     * A canonical pathname is both absolute and unique. The precise
     * definition of canonical form is system-dependent. This method first
     * converts this pathname to absolute form if necessary, as if by invoking the
     * getAbsolutePath() method, and then maps it to its unique form in a
     * system-dependent way.  This typically involves removing redundant names
     * such as "." and .. from the pathname, resolving symbolic links
     * (on UNIX platforms), and converting drive letters to a standard case
     * (on Win32 platforms).
     *
     * Every pathname that denotes an existing file or directory has a
     * unique canonical form.  Every pathname that denotes a nonexistent file
     * or directory also has a unique canonical form.  The canonical form of
     * the pathname of a nonexistent file or directory may be different from
     * the canonical form of the same pathname after the file or directory is
     * created.  Similarly, the canonical form of the pathname of an existing
     * file or directory may be different from the canonical form of the same
     * pathname after the file or directory is deleted.
     *
     * @return string The canonical path to this file/dir.
     */
    public function getCanonicalPath() {
        $fs = FileSystem::getFileSystem();
        return $fs->canonicalize($this->path);
    }

    /**
     * Returns the canonical form of this abstract pathname.
     *
     * @see getCanonicalPath()
     * @return File The canonical File to tihs file/dir.
     */
    public function getCanonicalFile() {
        return new File($this->getCanonicalPath());
    }
	
    /**
     * Normalizes the directory separators in the path and adds a trailing '/' to directories.
     *
     * @param string $path
     * @param boolean $isDirectory
     * @return string
     */
    private function _slashify($path, $isDirectory) {
        $p = (string) $path;

        if (self::$separator !== '/') {
            $p = str_replace(self::$separator, '/', $p);
        }

        if (!StringHelper::startsWith('/', $p)) {
            $p = '/'.$p;
        }

        if (!StringHelper::endsWith('/', $p) && $isDirectory) {
            $p = $p.'/';
        }

        return $p;
    }

    /* -- Attribute accessors -- */

    /**
     * Tests whether the application can read the file denoted by this
     * abstract pathname.
     *
     * @return boolean Whether file/dir can be read by application.
     */
    public function canRead() {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess($this)) {
            return (boolean) @is_readable($this->getAbsolutePath());
        }
        return false;
    }

    /**
     * Tests whether the application can modify to the file denoted by this
     * abstract pathname.
     *
     * @return boolean Whether file/dir can be written to.
     *
     */
    public function canWrite() {
        $fs = FileSystem::getFileSystem();
        return $fs->checkAccess($this, true);
    }

    /**
     * Tests whether the file denoted by this abstract pathname exists.
     *
     * @return boolean Whether file/dir exists.
     */
    public function exists() {                
		clearstatcache();
        if ($this->isFile()) {
            return @file_exists($this->path);
        } else {
            return @is_dir($this->path);
        }
    }

    /**
     * Tests whether the path represented by this object corresponds to a directory.
     *
     * @return boolean Whether path represented is a directory.
     */
    public function isDirectory() {
		clearstatcache();
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to ".$this->path);
        }
        return @is_dir($this->path);
    }

    /**
     * Tests whether the path represented by this object corresponds to a normal file.
     * 
     * @return boolean Whether path represents a file.
     */
    public function isFile() {
		clearstatcache();
        //$fs = FileSystem::getFileSystem();
        return @is_file($this->path);
    }

    /**
     * Tests whether the path represented by this object is a hidden file.
     *  
     * @return boolean Whether file/dir is hidden.
     */
    public function isHidden() {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to ".$this->path);
        }
        return (($fs->getBooleanAttributes($this) & $fs->BA_HIDDEN) !== 0);
    }

    /**
     * Returns the time that the file denoted by this abstract pathname was
     * last modified.
     *
     * @return int  A integer value representing the time the file was
     *          last modified, measured in milliseconds since the epoch
     *          (00:00:00 GMT, January 1, 1970), or 0 if the
     *          file does not exist or if an I/O error occurs
     */
    public function lastModified() {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path);
        }
        return $fs->getLastModifiedTime($this);
    }

    /**
     * Returns the length of the file denoted by this abstract pathname.
     * The return value is unspecified if this pathname denotes a directory.
     *
     * @return int The length, in bytes, of the file denoted by this abstract
     *          	pathname, or 0 if the file does not exist
     * @throws IOException - if file cannot be read
     */
    public function length() {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to ".$this->path."\n");
        }
        return $fs->getLength($this);
    }

    /**
     * Convenience method for returning the contents of this file as a string.
     * This method uses file_get_contents() to read file in an optimized way.
     * @return string
     * @throws IOException - if file cannot be read
     */
    public function contents() {
        if (!$this->canRead() || !$this->isFile()) {
            throw new IOException("Cannot read file contents!");
        }
        return file_get_contents($this->getAbsolutePath());
    }
    
    /* -- File operations -- */

    /**
     * Atomically creates a new, empty file named by this abstract pathname if
     * and only if a file with this name does not yet exist.  The check for the
     * existence of the file and the creation of the file if it does not exist
     * are a single operation that is atomic with respect to all other
     * filesystem activities that might affect the file.
     *
     * @return  true if the named file does not exist and was
     *          successfully created; <code>false</code> if the named file
     *          already exists
     * @throws IOException if file can't be created
     */
    public function createNewFile($parents=true, $mode=0777) {
        $file = FileSystem::getFileSystem()->createNewFile($this->path);
        return $file;
    }

    /**
     * Deletes the file or directory denoted by this abstract pathname.  If
     * this pathname denotes a directory, then the directory must be empty in
     * order to be deleted.
     *
     * @return  true if and only if the file or directory is
     *          successfully deleted; false otherwise
     */
    public function delete() {
        $fs = FileSystem::getFileSystem();
        if ($fs->canDelete($this) !== true) {
            throw new IOException("Cannot delete " . $this->path . "\n"); 
        }
        return $fs->delete($this);
    }

    /**
     * Requests that the file or directory denoted by this abstract pathname
     * be deleted when php terminates.  Deletion will be attempted only for
     * normal termination of php and if and if only Phing::shutdown() is
     * called.
     *
     * Once deletion has been requested, it is not possible to cancel the
     * request.  This method should therefore be used with care.
     *
     */
    public function deleteOnExit() {
        $fs = FileSystem::getFileSystem();
        $fs->deleteOnExit($this);
    }

    /**
     * Return an array of names for contents of directory represented by this object.
     *
     * If this abstract pathname does not denote a directory, then this
     * method returns null.
     * 
     * @return array string[] An array of file and directory names 
     */
    public function listDir($filter = null) {
        $fs = FileSystem::getFileSystem();
        return $fs->lister($this, $filter);
    }
	
    /**
     * Return an array of File objects for contents of directory represented by this object.
     *
     * @param unknown_type $filter
     * @return array File[]
     */
    public function listFiles($filter = null) {
        $ss = $this->listDir($filter);
        if ($ss === null) {
            return null;
        }
        $n = count($ss);
        $fs = array();
        for ($i = 0; $i < $n; $i++) {
            $fs[$i] = new File((string)$this->path, (string)$ss[$i]);
        }
        return $fs;
    }

    /**
     * Creates the directory named by this abstract pathname, including any
     * necessary but nonexistent parent directories.  Note that if this
     * operation fails it may have succeeded in creating some of the necessary
     * parent directories.
     *
     * @return  true if and only if the directory was created,
     *          along with all necessary parent directories; false
     *          otherwise
     * @throws  IOException
     */
    public function mkdirs() {
        if ($this->exists()) {
            return false;
        }
		try {
			if ($this->mkdir()) {
	            return true;
	        }
		} catch (IOException $ioe) {
			// IOException from mkdir() means that directory propbably didn't exist.
		}        
        $parentFile = $this->getParentFile();
        return (($parentFile !== null) && ($parentFile->mkdirs() && $this->mkdir()));
    }

    /**
     * Creates the directory named by this abstract pathname.
     *
     * @return  true if and only if the directory was created; false otherwise
     * @throws  IOException - If no write access
     */
    public function mkdir() {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess(new File($this->path), true) !== true) {
            throw new IOException("No write access to " . $this->getPath());
        }
        return $fs->createDirectory($this);
    }

    /**
     * Renames the file denoted by this abstract pathname.
     *
     * @param   destFile  The new abstract pathname for the named file
     * @return  true if and only if the renaming succeeded; false otherwise
     */
    public function renameTo(File $destFile) {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No write access to ".$this->getPath());
        }
        return $fs->rename($this, $destFile);
    }

    /**
     * Simple-copies file denoted by this abstract pathname into another
     * File
     *
     * @param File $destFile  The new abstract pathname for the named file
     * @return true if and only if the renaming succeeded; false otherwise
     */
    public function copyTo(File $destFile) {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to ".$this->getPath()."\n");
        }

        if ($fs->checkAccess($destFile, true) !== true) {
            throw new IOException("File::copyTo() No write access to ".$destFile->getPath());
        }
        return $fs->copy($this, $destFile);
    }

    /**
     * Sets the last-modified time of the file or directory named by this
     * abstract pathname.
     *
     * All platforms support file-modification times to the nearest second,
     * but some provide more precision.  The argument will be truncated to fit
     * the supported precision.  If the operation succeeds and no intervening
     * operations on the file take place, then the next invocation of the
     * lastModified method will return the (possibly truncated) time argument
     * that was passed to this method.
     *
     * @param  int $time  The new last-modified time, measured in milliseconds since
     *               the epoch (00:00:00 GMT, January 1, 1970)
     * @return boolean Whether operation succeeded
     */
    public function setLastModified($time) {
        $time = (int) $time;
        if ($time < 0) {
            throw new Exception("IllegalArgumentException, Negative $time\n");
        }

        // FIXME check if accessible
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this, true) !== true) {
            throw new IOException("File::setLastModified(). No write access to file\n");
        }
        return $fs->setLastModifiedTime($this, $time);
    }

    /**
     * Marks the file or directory named by this abstract pathname so that
     * only read operations are allowed.
     * 
     * @return boolean Whether operation succeeded
     */
    public function setReadOnly() {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this, true) !== true) {
            // Error, no write access
            throw new IOException("No write access to " . $this->getPath());
        }
        return $fs->setReadOnly($this);
    }

    /**
     * Sets the mode of the file
     * @param int $mode Ocatal mode.
     */
    public function setMode($mode) {
        $fs = FileSystem::getFileSystem();
        return $fs->chmod($this->getPath(), $mode);
    }

    /**
     * Retrieve the mode of this file.
     * @return int
     */
    public function getMode() {
        return @fileperms($this->getPath());
    }

    /* -- Filesystem interface -- */

    /**
     * List the available filesystem roots.
     *
     * A particular platform may support zero or more hierarchically-organized
     * file systems.  Each file system has a root  directory from which all
     * other files in that file system can be reached.
     * Windows platforms, for example, have a root directory for each active
     * drive; UNIX platforms have a single root directory, namely "/".
     * The set of available filesystem roots is affected by various system-level
     * operations such the insertion or ejection of removable media and the
     * disconnecting or unmounting of physical or virtual disk drives.
     *
     * This method returns an array of File objects that
     * denote the root directories of the available filesystem roots.  It is
     * guaranteed that the canonical pathname of any file physically present on
     * the local machine will begin with one of the roots returned by this
     * method.
     *
     * The canonical pathname of a file that resides on some other machine
     * and is accessed via a remote-filesystem protocol such as SMB or NFS may
     * or may not begin with one of the roots returned by this method.  If the
     * pathname of a remote file is syntactically indistinguishable from the
     * pathname of a local file then it will begin with one of the roots
     * returned by this method.  Thus, for example, File objects
     * denoting the root directories of the mapped network drives of a Windows
     * platform will be returned by this method, while File
     * objects containing UNC pathnames will not be returned by this method.
     *
     * @return  array File[] An array of File objects denoting the available
     *          filesystem roots, or null if the set of roots
     *          could not be determined.  The array will be empty if there are
     *          no filesystem roots.
     */
    public function listRoots() {
        $fs = FileSystem::getFileSystem();
        return (array) $fs->listRoots();
    }

    /* -- Tempfile management -- */

    /**
     * Returns the path to the temp directory.
     * @return string
     */
    public function getTempDir() {
        return Phing::getProperty('php.tmpdir');
    }

    /**
     * Static method that creates a unique filename whose name begins with
     * $prefix and ends with $suffix in the directory $directory. $directory
     * is a reference to a File Object.
     * Then, the file is locked for exclusive reading/writing.
     *
     * @throws IOException
     */
    public static function createTempFile($prefix, $suffix, File $directory) {
        
        // quick but efficient hack to create a unique filename ;-)
        $result = null;
        do {
            $result = new File($directory, $prefix . substr(md5(time()), 0, 8) . $suffix);
        } while (file_exists($result->getPath()));

        $fs = FileSystem::getFileSystem();
        $fs->createNewFile($result->getPath());
        $fs->lock($result);

        return $result;
    }

    /**
     * If necessary, $File the lock on $File is removed and then the file is
     * deleted
     *
     * @access      public
     */
    public function removeTempFile() {
        $fs = FileSystem::getFileSystem();
        // catch IO Exception
        $fs->unlock($this);
        $this->delete();
    }


    /* -- Basic infrastructure -- */

    /**
     * Compares two abstract pathnames lexicographically.  The ordering
     * defined by this method depends upon the underlying system.  On UNIX
     * systems, alphabetic case is significant in comparing pathnames; on Win32
     * systems it is not.
     *
     * @param File $file Th file whose pathname sould be compared to the pathname of this file.
     *
     * @return int Zero if the argument is equal to this abstract pathname, a
     *        value less than zero if this abstract pathname is
     *        lexicographically less than the argument, or a value greater
     *        than zero if this abstract pathname is lexicographically
     *        greater than the argument
     */
    public function compareTo(File $file) {
        $fs = FileSystem::getFileSystem();
        return $fs->compare($this, $file);
    }

    /**
     * Tests to see whether two File objects are equal.
     * @return boolean
     */
    public function equals($obj) {
        if (($obj !== null) && ($obj instanceof File)) {
            return ($this->compareTo($obj) === 0);
        }
        return false;
    }

    /**
	 * Backwards compatibility -- use PHP5's native __tostring method.
	 * @deprecated
	 */
    public function toString() {
        return $this->getPath();
    }
    
    /**
	 * PHP5's semi-magic __toString() method.
	 * @return string 
	 */
    public function __toString() {
        return $this->getPath();
    }
}
