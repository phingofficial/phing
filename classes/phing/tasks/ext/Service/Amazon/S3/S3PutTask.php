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
 * Stores an object on S3
 *
 * @package phing.tasks.ext
 * @author  Andrei Serdeliuc <andrei@serdeliuc.ro>
 */
class S3PutTask extends S3
{
    /**
     * File we're trying to upload
     *
     * (default value: null)
     *
     * @var string
     */
    protected $_source = null;

    /**
     * Content we're trying to upload
     *
     * The user can specify either a file to upload or just a bit of content
     *
     * (default value: null)
     *
     * @var mixed
     */
    protected $_content = null;

    /**
     * Collection of filesets
     * Used for uploading multiple files
     *
     * (default value: array())
     *
     * @var array
     */
    protected $_filesets = [];

    /**
     * Whether to try to create buckets or not
     *
     * (default value: false)
     *
     * @var bool
     */
    protected $_createBuckets = false;

    /**
     * File ACL
     * Use to set the permission to the uploaded files
     *
     * (default value: 'private')
     *
     * @var string
     */
    protected $_acl = 'private';

    /**
     * File content type
     * Use this to set the content type of your static files
     * Set contentType to "auto" if you want to autodetect the content type based on the source file extension
     *
     * (default value: 'binary/octet-stream')
     *
     * @var string
     */
    protected $_contentType = 'binary/octet-stream';

    /**
     * Object maxage (in seconds).
     *
     * @var int
     */
    protected $_maxage = null;

    /**
     * Content is gzipped.
     *
     * @var boolean
     */
    protected $_gzipped = false;

    /**
     * Extension content type mapper
     *
     * @var array
     */
    protected $_extensionContentTypeMapper = [
        'js' => 'application/x-javascript',
        'css' => 'text/css',
        'html' => 'text/html',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'txt' => 'text/plain'
    ];

    /**
     * Whether filenames contain paths
     *
     * (default value: false)
     *
     * @var bool
     */
    protected $_fileNameOnly = false;
    private $_object;

    /**
     * @param string $source
     *
     * @throws BuildException if $source is not readable
     */
    public function setSource($source)
    {
        if (!is_readable($source)) {
            throw new BuildException('Source is not readable: ' . $source);
        }

        $this->_source = $source;
    }

    /**
     * @return string
     *
     * @throws BuildException if source is null
     */
    public function getSource()
    {
        if ($this->_content !== null) {
            $tempFile = tempnam($this->getProject()->getProperty('php.tmpdir'), 's3_put_');

            file_put_contents($tempFile, $this->_content);
            $this->_source = $tempFile;
        }

        if ($this->_source === null) {
            throw new BuildException('Source is not set');
        }

        return $this->_source;
    }

    /**
     * @param string $content
     *
     * @throws BuildException if $content is a empty string
     */
    public function setContent($content)
    {
        if (empty($content) || !is_string($content)) {
            throw new BuildException('Content must be a non-empty string');
        }

        $this->_content = $content;
    }

    /**
     * @return string
     *
     * @throws BuildException if content is null
     */
    public function getContent()
    {
        if ($this->_content === null) {
            throw new BuildException('Content is not set');
        }

        return $this->_content;
    }

    /**
     * @param string $object
     *
     * @throws BuildException
     */
    public function setObject($object)
    {
        if (empty($object) || !is_string($object)) {
            throw new BuildException('Object must be a non-empty string');
        }

        $this->_object = $object;
    }

    /**
     * @return string
     *
     * @throws \BuildException
     */
    public function getObject()
    {
        if ($this->_object === null) {
            throw new BuildException('Object is not set');
        }

        return $this->_object;
    }

    /**
     * @param $permission
     * @throws BuildException
     */
    public function setAcl($permission)
    {
        $valid_acl = ['private', 'public-read', 'public-read-write', 'authenticated-read'];
        if (empty($permission) || !is_string($permission) || !in_array($permission, $valid_acl)) {
            throw new BuildException('Object must be one of the following values: ' . implode('|', $valid_acl));
        }
        $this->_acl = $permission;
    }

    /**
     * @return string
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
    }

    /**
     * @return string
     * @throws BuildException
     */
    public function getContentType()
    {
        if ($this->_contentType === 'auto') {
            $ext = strtolower(substr(strrchr($this->getSource(), '.'), 1));
            if (isset($this->_extensionContentTypeMapper[$ext])) {
                return $this->_extensionContentTypeMapper[$ext];
            }

            return 'binary/octet-stream';
        }

        return $this->_contentType;
    }

    public function setCreateBuckets(bool $createBuckets)
    {
        $this->_createBuckets = $createBuckets;
    }

    /**
     * @return bool
     */
    public function getCreateBuckets()
    {
        return $this->_createBuckets;
    }

    /**
     * Set seconds in max-age, null value exclude max-age setup.
     *
     * @param int $seconds
     */
    public function setMaxage($seconds)
    {
        $this->_maxage = $seconds;
    }

    /**
     * Get seconds in max-age or null.
     *
     * @return int Number of seconds in maxage or null.
     */
    public function getMaxage()
    {
        return $this->_maxage;
    }

    /**
     * Set if content is gzipped.
     *
     * @param boolean $gzipped
     */
    public function setGzip($gzipped)
    {
        $this->_gzipped = $gzipped;
    }

    /**
     * Return if content is gzipped.
     *
     * @return boolean Indicate if content is gzipped.
     */
    public function getGzip()
    {
        return $this->_gzipped;
    }

    /**
     * Generate HTTPHEader array sent to S3.
     *
     * @return array HttpHeader to set in S3 Object.
     */
    protected function getHttpHeaders()
    {
        $headers = [];
        if (null !== $this->_maxage) {
            $headers['Cache-Control'] = 'max-age=' . $this->_maxage;
        }
        if ($this->_gzipped) {
            $headers['Content-Encoding'] = 'gzip';
        }

        return $headers;
    }

    public function setFileNameOnly(bool $fileNameOnly)
    {
        $this->_fileNameOnly = $fileNameOnly;
    }

    /**
     * creator for _filesets
     *
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->_filesets, new FileSet());

        return $this->_filesets[$num - 1];
    }

    /**
     * getter for _filesets
     *
     * @return array
     */
    public function getFilesets()
    {
        return $this->_filesets;
    }

    /**
     * Determines what we're going to store in the object
     *
     * If _content has been set, this will get stored,
     * otherwise, we read from _source
     *
     * @return string
     *
     * @throws BuildException
     */
    public function getObjectData()
    {
        $source = $this->getSource();

        if (!is_file($source)) {
            throw new BuildException('Currently only files can be used as source');
        }

        return $source;
    }

    /**
     * Store the object on S3
     *
     * @throws BuildException
     * @return void
     */
    public function execute()
    {
        if (!$this->isBucketAvailable()) {
            if (!$this->getCreateBuckets()) {
                throw new BuildException('Bucket doesn\'t exist and createBuckets not specified');
            }

            if (!$this->createBucket()) {
                throw new BuildException('Bucket cannot be created');
            }
        }

        // Filesets take precedence
        if (!empty($this->_filesets)) {
            $objects = [];

            foreach ($this->_filesets as $fs) {
                if (!($fs instanceof FileSet)) {
                    continue;
                }

                $ds = $fs->getDirectoryScanner($this->getProject());
                $objects = array_merge($objects, $ds->getIncludedFiles());
            }

            $fromDir = $fs->getDir($this->getProject())->getAbsolutePath();

            if ($this->_fileNameOnly) {
                foreach ($objects as $object) {
                    $this->_source = $object;
                    $this->saveObject(basename($object), $fromDir . DIRECTORY_SEPARATOR . $object);
                }
            } else {
                foreach ($objects as $object) {
                    $this->_source = $object;
                    $this->saveObject(
                        str_replace('\\', '/', $object),
                        $fromDir . DIRECTORY_SEPARATOR . $object
                    );
                }
            }

            return;
        }

        $this->saveObject($this->getObject(), $this->getSource());
    }

    /**
     * @param string $key
     * @param string $sourceFile
     * @throws \BuildException
     */
    protected function saveObject($key, $sourceFile)
    {
        $client = $this->getClientInstance();
        $client->putObject(
            [
                'Bucket' => $this->getBucket(),
                'Key' => $key,
                'SourceFile' => $sourceFile
            ]
        );
    }
}
