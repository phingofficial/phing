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

require_once dirname(dirname(__FILE__)) . "/Amazon.php";

/**
 * Abstract Service_Amazon_S3 class.
 *
 * Provides common methods and properties to all of the S3 tasks
 *
 * @extends Service_Amazon
 * @version $ID$
 * @package phing.tasks.ext
 * @author Andrei Serdeliuc <andrei@serdeliuc.ro>
 * @abstract
 */
abstract class Service_Amazon_S3 extends Service_Amazon
{
    /**
     * Services_Amazon_S3 client
     *
     * (default value: null)
     *
     * @var Services_Amazon_S3
     * @see Services_Amazon_S3
     * @access protected
     */
    protected $_client = null;

    /**
     * We only instantiate the client once per task call
     *
     * @return Services_Amazon_S3
     */
    public function getClient()
    {

        if ($this->_client === null) {
            $s3Client = Aws\S3\S3Client::factory(array(
                'key'    => $this->getKey(),
                'secret' => $this->getSecret(),
            ));

            $this->_client = $s3Client;
        }

        return $this->_client;
    }

    /**
     * @param string $bucket
     *
     * @throws BuildException if $bucket is a empty string
     */
    public function setBucket($bucket)
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
    public function getBucket()
    {
        if (!($bucket = $this->bucket)) {
            throw new BuildException('Bucket is not set');
        }

        return $this->bucket;
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Object
     *
     * @param  mixed $object
     *
     * @return Services_Amazon_S3_Resource_Object
     */
    public function getObjectInstance($object)
    {
        return $this->getClientInstance()->getObject($object);
    }

    /**
     * Check if the object already exists in the current bucket
     *
     * @param  mixed $object
     *
     * @return bool
     */
    public function isObjectAvailable($object)
    {
        return (bool) $this->getObjectInstance($object)->load(Services_Amazon_S3_Resource_Object::LOAD_METADATA_ONLY);
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Bucket
     *
     * @access public
     *
     * @return \Aws\S3\S3Client
     */
    public function getClientInstance()
    {
        return $this->getClient();
    }

    /**
     * Check if the current bucket is available
     *
     * @access public
     *
     * @return bool
     */
    public function isBucketAvailable()
    {
        return $this->getClientInstance()->doesBucketExist($this->getBucket());
    }

    /**
     * Create a bucket
     *
     * @access public
     * @return void
     */
    public function createBucket()
    {
        $client = $this->getClientInstance();
        $client->createBucket(array('Bucket' => $this->getBucket()));

        return $this->isBucketAvailable();
    }

    /**
     * Main entry point, doesn't do anything
     *
     * @access public
     * @final
     * @return void
     */
    final public function main()
    {
        $this->execute();
    }

    /**
     * Entry point to children tasks
     *
     * @access public
     * @abstract
     * @return void
     */
    abstract public function execute();
}
