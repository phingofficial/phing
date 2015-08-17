<?php

require_once 'phing/Task.php';

class RenameTask extends Task
{

    /**
     * @var PhingFile
     */
    protected $file;

    /**
     * @var PhingFile
     */
    protected $destFile;

    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    public function setTofile(PhingFile $file)
    {
        $this->destFile = $file;
    }

    public function main()
    {
        $this->validateAttributes();
        $this->file->renameTo($this->destFile);
        $this->log('rename file to ' . $this->destFile->getName());
    }

    protected function validateAttributes()
    {
        if ($this->file === null) {
            throw new BuildException("RenameTask. Attribute file is required.");
        }
        if (!$this->file->exists()) {
            throw new BuildException("RenameTask. File not found.");
        }
        if ($this->destFile === null) {
            throw new BuildException("RenameTask. Attribute toFile is required.");
        }
        if ($this->destFile->exists()) {
            throw new BuildException("RenameTask. ToFile exists");
        }
    }

}
