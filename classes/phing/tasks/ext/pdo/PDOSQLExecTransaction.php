<?php

/**
 * "Inner" class that contains the definition of a new transaction element.
 * Transactions allow several files or blocks of statements
 * to be executed using the same JDBC connection and commit
 * operation in between.
 *
 * @package   phing.tasks.ext.pdo
 */
class PDOSQLExecTransaction
{
    private $tSrcFile = null;
    private $tSqlCommand = "";
    private $parent;

    /**
     * @param $parent
     */
    public function __construct($parent)
    {
        // Parent is required so that we can log things ...
        $this->parent = $parent;
    }

    /**
     * @param PhingFile $src
     */
    public function setSrc(PhingFile $src)
    {
        $this->tSrcFile = $src;
    }

    /**
     * @param $sql
     */
    public function addText($sql)
    {
        $this->tSqlCommand .= $sql;
    }

    /**
     * @throws IOException, PDOException
     */
    public function runTransaction()
    {
        if (!empty($this->tSqlCommand)) {
            $this->parent->log("Executing commands", Project::MSG_INFO);
            $this->parent->runStatements(new StringReader($this->tSqlCommand));
        }

        if ($this->tSrcFile !== null) {
            $this->parent->log(
                "Executing file: " . $this->tSrcFile->getAbsolutePath(),
                Project::MSG_INFO
            );
            $reader = new FileReader($this->tSrcFile);
            $this->parent->runStatements($reader);
            $reader->close();
        }
    }
}
