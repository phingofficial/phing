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
    protected $source = null;

    /**
     * Content we're trying to upload
     *
     * The user can specify either a file to upload or just a bit of content
     *
     * (default value: null)
     *
     * @var mixed
     */
    protected $content = null;

    /**
     * Collection of filesets
     * Used for uploading multiple files
     *
     * (default value: array())
     *
     * @var FileSet[]
     */
    protected $filesets = [];

    /**
     * Whether to try to create buckets or not
     *
     * (default value: false)
     *
     * @var bool
     */
    protected $createBuckets = false;

    /**
     * File ACL
     * Use to set the permission to the uploaded files
     *
     * (default value: 'private')
     *
     * @var string
     */
    protected $acl = 'private';

    /**
     * File content type
     * Use this to set the content type of your static files
     * Set contentType to "auto" if you want to autodetect the content type based on the source file extension
     *
     * (default value: 'binary/octet-stream')
     *
     * @var string
     */
    protected $contentType = 'binary/octet-stream';

    /**
     * Object maxage (in seconds).
     *
     * @var int
     */
    protected $maxage = null;

    /**
     * Content is gzipped.
     *
     * @var bool
     */
    protected $gzipped = false;

    /**
     * Extension content type mapper
     *
     * @var array
     */
    protected $extensionContentTypeMapper = [
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
    protected $fileNameOnly = false;

    /**
     * @var string
     */
    private $object;

    /**
     * @param string $source
     *
     * @return void
     *
     * @throws BuildException if $source is not readable
     */
    public function setSource(string $source): void
    {
        if (!is_readable($source)) {
            throw new BuildException('Source is not readable: ' . $source);
        }

        $this->source = $source;
    }

    /**
     * @return string
     *
     * @throws BuildException if source is null
     */
    public function getSource(): string
    {
        if ($this->content !== null) {
            $tempFile = tempnam($this->getProject()->getProperty('php.tmpdir'), 's3_put_');

            file_put_contents((string) $tempFile, $this->content);
            $this->source = $tempFile;
        }

        if ($this->source === null) {
            throw new BuildException('Source is not set');
        }

        return $this->source;
    }

    /**
     * @param string $content
     *
     * @return void
     *
     * @throws BuildException if $content is a empty string
     */
    public function setContent(string $content): void
    {
        if (empty($content) || !is_string($content)) {
            throw new BuildException('Content must be a non-empty string');
        }

        $this->content = $content;
    }

    /**
     * @return string
     *
     * @throws BuildException if content is null
     */
    public function getContent(): string
    {
        if ($this->content === null) {
            throw new BuildException('Content is not set');
        }

        return $this->content;
    }

    /**
     * @param string $object
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setObject(string $object): void
    {
        if (empty($object) || !is_string($object)) {
            throw new BuildException('Object must be a non-empty string');
        }

        $this->object = $object;
    }

    /**
     * @return string
     *
     * @throws BuildException
     */
    public function getObject(): string
    {
        if ($this->object === null) {
            throw new BuildException('Object is not set');
        }

        return $this->object;
    }

    /**
     * @param string $permission
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setAcl(string $permission): void
    {
        $valid_acl = ['private', 'public-read', 'public-read-write', 'authenticated-read'];
        if (empty($permission) || !is_string($permission) || !in_array($permission, $valid_acl)) {
            throw new BuildException('Object must be one of the following values: ' . implode('|', $valid_acl));
        }
        $this->acl = $permission;
    }

    /**
     * @return string
     */
    public function getAcl(): string
    {
        return $this->acl;
    }

    /**
     * @param string $contentType
     *
     * @return void
     */
    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     *
     * @throws BuildException
     */
    public function getContentType(): string
    {
        if ($this->contentType === 'auto') {
            $ext = strtolower(substr(strrchr($this->getSource(), '.'), 1));
            return $this->extensionContentTypeMapper[$ext] ?? 'binary/octet-stream';
        }

        return $this->contentType;
    }

    /**
     * @param bool $createBuckets
     *
     * @return void
     */
    public function setCreateBuckets(bool $createBuckets): void
    {
        $this->createBuckets = $createBuckets;
    }

    /**
     * @return bool
     */
    public function getCreateBuckets(): bool
    {
        return $this->createBuckets;
    }

    /**
     * Set seconds in max-age, null value exclude max-age setup.
     *
     * @param int $seconds
     *
     * @return void
     */
    public function setMaxage(int $seconds): void
    {
        $this->maxage = $seconds;
    }

    /**
     * Get seconds in max-age or null.
     *
     * @return int Number of seconds in maxage or null.
     */
    public function getMaxage(): int
    {
        return $this->maxage;
    }

    /**
     * Set if content is gzipped.
     *
     * @param bool $gzipped
     *
     * @return void
     */
    public function setGzip(bool $gzipped): void
    {
        $this->gzipped = $gzipped;
    }

    /**
     * Return if content is gzipped.
     *
     * @return bool Indicate if content is gzipped.
     */
    public function getGzip(): bool
    {
        return $this->gzipped;
    }

    /**
     * Generate HTTPHEader array sent to S3.
     *
     * @return array HttpHeader to set in S3 Object.
     */
    protected function getHttpHeaders(): array
    {
        $headers = [];
        if (null !== $this->maxage) {
            $headers['Cache-Control'] = 'max-age=' . $this->maxage;
        }
        if ($this->gzipped) {
            $headers['Content-Encoding'] = 'gzip';
        }

        return $headers;
    }

    /**
     * @param bool $fileNameOnly
     *
     * @return void
     */
    public function setFileNameOnly(bool $fileNameOnly): void
    {
        $this->fileNameOnly = $fileNameOnly;
    }

    /**
     * creator for _filesets
     *
     * @return FileSet
     */
    public function createFileset(): FileSet
    {
        $num = array_push($this->filesets, new FileSet());

        return $this->filesets[$num - 1];
    }

    /**
     * getter for _filesets
     *
     * @return FileSet[]
     */
    public function getFilesets(): array
    {
        return $this->filesets;
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
    public function getObjectData(): string
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
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function execute(): void
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
        if (!empty($this->filesets)) {
            $objects = [];

            foreach ($this->filesets as $fs) {
                if (!($fs instanceof FileSet)) {
                    continue;
                }

                $ds      = $fs->getDirectoryScanner($this->getProject());
                $objects = array_merge($objects, $ds->getIncludedFiles());
            }

            $fromDir = $fs->getDir($this->getProject())->getAbsolutePath();

            if ($this->fileNameOnly) {
                foreach ($objects as $object) {
                    $this->source = $object;
                    $this->saveObject(basename((string) $object), $fromDir . DIRECTORY_SEPARATOR . $object);
                }
            } else {
                foreach ($objects as $object) {
                    $this->source = $object;
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
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function saveObject(string $key, string $sourceFile): void
    {
        $client = $this->getClientInstance();
        $client->putObject(
            [
                'Bucket' => $this->getBucket(),
                'Key' => $key,
                'SourceFile' => $sourceFile,
            ]
        );
    }
}
