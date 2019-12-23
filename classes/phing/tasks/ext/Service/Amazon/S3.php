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

use Aws\S3\S3Client;

/**
 * Abstract Service_Amazon_S3 class.
 *
 * Provides common methods and properties to all of the S3 tasks
 *
 * @version $ID$
 * @package phing.tasks.ext
 * @author  Andrei Serdeliuc <andrei@serdeliuc.ro>
 */
abstract class S3 extends Amazon
{
    /**
     * Services_Amazon_S3 client
     *
     * (default value: null)
     *
     * @var S3Client
     */
    protected $client = null;

    /**
     * We only instantiate the client once per task call
     *
     * @return S3Client
     *
     * @throws BuildException
     */
    public function getClient(): Aws\S3\S3Client
    {
        if ($this->client === null) {
            try {
                $s3Client = new S3Client(
                    [
                        'key' => $this->getKey(),
                        'secret' => $this->getSecret(),
                    ]
                );
            } catch (InvalidArgumentException $e) {
                throw new BuildException($e);
            }

            $this->client = $s3Client;
        }

        return $this->client;
    }

    /**
     * @param string $bucket
     *
     * @return void
     *
     * @throws BuildException if $bucket is a empty string
     */
    public function setBucket(string $bucket): void
    {
        if (empty($bucket) || !is_string($bucket)) {
            throw new BuildException('Bucket must be a non-empty string');
        }

        $this->bucket = (string) $bucket;
    }

    /**
     * @return string
     *
     * @throws BuildException if bucket is not set
     */
    public function getBucket(): string
    {
        if (!($bucket = $this->bucket)) {
            throw new BuildException('Bucket is not set');
        }

        return $this->bucket;
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Object
     *
     * @param mixed $object
     *
     * @return Aws\Result
     *
     * @throws BuildException
     */
    public function getObjectInstance($object): Aws\Result
    {
        return $this->getClientInstance()->getObject($object);
    }

    /**
     * Check if the object already exists in the current bucket
     *
     * @param mixed $object
     *
     * @return bool
     */
    public function isObjectAvailable($object): bool
    {
        return (bool) $this->getObjectInstance($object)->load(Services_Amazon_S3_Resource_Object::LOAD_METADATA_ONLY);
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Bucket
     *
     * @return S3Client
     */
    public function getClientInstance(): Aws\S3\S3Client
    {
        return $this->getClient();
    }

    /**
     * Check if the current bucket is available
     *
     * @return bool
     *
     * @throws BuildException
     */
    public function isBucketAvailable(): bool
    {
        return $this->getClientInstance()->doesBucketExist($this->getBucket());
    }

    /**
     * Create a bucket
     *
     * @return bool
     *
     * @throws BuildException
     */
    public function createBucket(): bool
    {
        $client = $this->getClientInstance();
        $client->createBucket(['Bucket' => $this->getBucket()]);

        return $this->isBucketAvailable();
    }

    /**
     * Main entry point, doesn't do anything
     *
     * @return void
     */
    final public function main(): void
    {
        $this->execute();
    }

    /**
     * Entry point to children tasks
     *
     * @return void
     */
    abstract public function execute(): void;
}
