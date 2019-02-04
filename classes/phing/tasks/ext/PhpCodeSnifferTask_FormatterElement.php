<?php

/**
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTask_FormatterElement extends DataType
{

    /**
     * Type of output to generate
     *
     * @var string
     */
    protected $type = "";

    /**
     * Output to file?
     *
     * @var bool
     */
    protected $useFile = true;

    /**
     * Output file.
     *
     * @var string
     */
    protected $outfile = "";

    /**
     * Validate config.
     */
    public function parsingComplete()
    {
        if (empty($this->type)) {
            throw new BuildException("Format missing required 'type' attribute.");
        }
        if ($this->useFile && empty($this->outfile)) {
            throw new BuildException("Format requires 'outfile' attribute when 'useFile' is true.");
        }
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * @return bool
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * @param $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * @return string
     */
    public function getOutfile()
    {
        return $this->outfile;
    }
}
