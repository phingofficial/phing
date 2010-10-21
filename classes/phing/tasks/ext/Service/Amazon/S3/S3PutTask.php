<?php
require_once dirname(dirname(__FILE__)) . '/S3.php';

class S3PutTask extends Service_Amazon_S3
{
    /**
     * File we're trying to upload
     *
     * (default value: null)
     * 
     * @var string
     * @access protected
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
	 * @access protected
	 */
	protected $_content = null;
	
	/**
     * Collection of filesets
     * Used for uploading multiple files
     * 
     * (default value: array())
     * 
     * @var array
     * @access protected
     */
    protected $_filesets = array();
	
	/**
	 * Wether to try to create buckets or not
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access protected
	 */
	protected $_createBuckets = false;
    
    public function setSource($source)
    {
        if(!is_readable($source)) {
            throw new BuildException('Source is not readable: ' . $source);
        }

        $this->_source = $source;
    }
    
    public function getSource()
    {
        if($this->_source === null) {
            throw new BuildException('Source is not set');
        }
        
        return $this->_source;
    }

	public function setContent($content)
	{
		if(empty($content) || !is_string($content)) {
			throw new BuildException('Content must be a non-empty string');
		}
		
		$this->_content = $content;
	}
	
	public function getContent()
	{
		if($this->_content === null) {
			throw new BuildException('Content is not set');
		}
		
		return $this->_content;
	}

	public function setObject($object)
	{
		if(empty($object) || !is_string($object)) {
			throw new BuildException('Object must be a non-empty string');
		}
		
		$this->_object = $object;
	}
	
	public function getObject()
	{
		if($this->_object === null) {
			throw new BuildException('Object is not set');
		}
		
		return $this->_object;
	}

	public function setCreateBuckets($createBuckets)
    {
        $this->_createBuckets = (bool) $createBuckets;
    }

	public function getCreateBuckets()
    {
        return (bool) $this->_createBuckets;
    }

	/**
     * creator for _filesets
     * 
     * @access public
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->_filesets, new FileSet());
        return $this->_filesets[$num-1];
    }

	/**
     * getter for _filesets
     * 
     * @access public
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
	 * @access public
	 * @return string
	 */
	public function getObjectData()
	{
		try {
			$content = $this->getContent();
		} catch(BuildException $e) {
			$source = $this->getSource();
			
			if(!is_file($source)) {
                throw new BuildException('Currently only files can be used as source');
			}
			
			$content = file_get_contents($source);
		}
		
		return $content;
	}
    
    /**
     * Store the object on S3
     * 
     * @access public
     * @return void
     */
    public function execute()
    {
		if(!$this->isBucketAvailable()) {
			if(!$this->getCreateBuckets()) {
				throw new BuildException('Bucket doesn\'t exist and createBuckets not specified');
			} else{
				if(!$this->createBucket()) {
					throw new BuildException('Bucket cannot be created');
				}
			}
		}
		
		// Filesets take precedence
		if(!empty($this->_filesets)) {
			$objects = array();
			
			foreach($this->_filesets as $fs) {
	            if(!($fs instanceof FileSet)) {
	                continue;
	            }

				$ds = $fs->getDirectoryScanner($this->getProject());
				$objects = array_merge($objects, $ds->getIncludedFiles());
			}
			
			$fromDir = $fs->getDir($this->getProject())->getAbsolutePath();
			
			foreach($objects as $object) {
				$this->saveObject($object, file_get_contents($fromDir . DIRECTORY_SEPARATOR . $object));
			}
			
			return true;
		}
		
		$this->saveObject($this->getObject(), $this->getObjectData());
    }

	protected function saveObject($object, $data)
	{
		$object = $this->getObjectInstance($object);
		$object->data = $data;
		$object->save();
		
		if(!$this->isObjectAvailable($object->key)) {
			throw new BuildException('Upload failed');
		}
	}
}