<?php

/**
 * Returns false when the connection to a database fails, and true otherwise.
 *
 * @link    https://www.php.net/manual/en/pdo.drivers.php
 * @link    https://www.php.net/manual/en/ref.pdo-mysql.connection.php
 * @link    https://www.php.net/manual/en/ref.pdo-pgsql.connection.php
 *
 * @author  Jawira Portugal <dev@tugal.be>
 * @package phing.tasks.system.condition
 */
class DatabaseCondition extends ProjectComponent implements Condition
{
    protected $dsn      = null;
    protected $username = null;
    protected $password = null;

    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function evaluate(): bool
    {
        if (empty($this->dsn)) {
            throw new BuildException('dsn is required');
        }

        $this->log('Trying to reach: ' . $this->dsn, Project::MSG_DEBUG);

        try {
            new PDO($this->dsn, $this->username, $this->password);
        } catch (PDOException $ex) {
            $this->log($ex->getMessage(), Project::MSG_VERBOSE);

            return false;
        }

        $this->log('Success: ' . $this->dsn, Project::MSG_DEBUG);

        return true;
    }
}
