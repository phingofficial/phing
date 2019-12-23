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
 * Wrapper around git-tag
 *
 * @see     VersionControl_Git
 *
 * @author  Evan Kaufman <evan@digitalflophouse.com>
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.5
 */
class GitTagTask extends GitBaseTask
{
    /**
     * Make unsigned, annotated tag object. See -a of git-tag
     *
     * @var bool
     */
    private $annotate = false;

    /**
     * Make GPG-signed tag. See -s of git-tag
     *
     * @var bool
     */
    private $sign = false;

    /**
     * Make GPG-signed tag, using given key. See -u of git-tag
     *
     * @var string
     */
    private $keySign;

    /**
     * Replace existing tag with given name. See -f of git-tag
     *
     * @var bool
     */
    private $replace = false;

    /**
     * Delete existing tags with given names. See -d of git-tag
     *
     * @var bool
     */
    private $delete = false;

    /**
     * Verify gpg signature of given tag names. See -v of git-tag
     *
     * @var bool
     */
    private $verify = false;

    /**
     * List tags with names matching given pattern. See -l of git-tag
     *
     * @var bool
     */
    private $list = false;

    /**
     * <num> specifies how many lines from the annotation, if any, are printed
     * when using -l. See -n of git-tag
     *
     * @var int
     */
    private $num;

    /**
     * Only list tags containing specified commit. See --contains of git-tag
     *
     * @var string
     */
    private $contains;

    /**
     * Use given tag message. See -m of git-tag
     *
     * @var string
     */
    private $message;

    /**
     * Take tag message from given file. See -F of git-tag
     *
     * @var string
     */
    private $file;

    /**
     * <tagname> argument to git-tag
     *
     * @var string
     */
    private $name;

    /**
     * <commit> argument to git-tag
     *
     * @var string
     */
    private $commit;

    /**
     * <object> argument to git-tag
     *
     * @var string
     */
    private $object;

    /**
     * <pattern> argument to git-tag
     *
     * @var string
     */
    private $pattern;

    /**
     * Property name to set with output value from git-tag
     *
     * @var string
     */
    private $outputProperty;

    /**
     * The main entry point for the task
     *
     * @return void
     *
     * @throws VersionControl_Git_Exception
     * @throws Exception
     */
    public function main(): void
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('tag');
        $command
            ->setOption('a', $this->isAnnotate())
            ->setOption('s', $this->isSign())
            ->setOption('f', $this->isReplace())
            ->setOption('d', $this->isDelete())
            ->setOption('v', $this->isVerify())
            ->setOption('l', $this->isList());

        if (null !== $this->getKeySign()) {
            $command->setOption('u', $this->getKeySign());
        }

        if (null !== $this->getMessage()) {
            $command->setOption('m', $this->getMessage());
        }

        if (null !== $this->getFile()) {
            $command->setOption('F', $this->getFile());
        }

        // Use 'name' arg, if relevant
        if (null != $this->getName() && false == $this->isList()) {
            $command->addArgument($this->getName());
        }

        if (null !== $this->getKeySign() || $this->isAnnotate() || $this->isSign()) {
            // Require a tag message or file
            if (null === $this->getMessage() && null === $this->getFile()) {
                throw new BuildException('"message" or "file" required to make a tag');
            }
        }

        // Use 'commit' or 'object' args, if relevant
        if (null !== $this->getCommit()) {
            $command->addArgument($this->getCommit());
        } else {
            if (null !== $this->getObject()) {
                $command->addArgument($this->getObject());
            }
        }

        // Customize list (-l) options
        if ($this->isList()) {
            if (null !== $this->getContains()) {
                $command->setOption('contains', $this->getContains());
            }
            if (null !== $this->getPattern()) {
                $command->addArgument($this->getPattern());
            }
            if (null != $this->getNum()) {
                $command->setOption('n', $this->getNum());
            }
        }

        $this->log('git-tag command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            $this->log($e->getMessage(), Project::MSG_ERR);
            throw new BuildException('Task execution failed. ' . $e->getMessage());
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }

        $this->log(
            sprintf('git-tag: tags for "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-tag output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAnnotate(bool $flag): void
    {
        $this->annotate = $flag;
    }

    /**
     * @return bool
     */
    public function getAnnotate(): bool
    {
        return $this->annotate;
    }

    /**
     * @return bool
     */
    public function isAnnotate(): bool
    {
        return $this->getAnnotate();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSign(bool $flag): void
    {
        $this->sign = $flag;
    }

    /**
     * @return bool
     */
    public function getSign(): bool
    {
        return $this->sign;
    }

    /**
     * @return bool
     */
    public function isSign(): bool
    {
        return $this->getSign();
    }

    /**
     * @param string $keyId
     *
     * @return void
     */
    public function setKeySign(string $keyId): void
    {
        $this->keySign = $keyId;
    }

    /**
     * @return string
     */
    public function getKeySign(): string
    {
        return $this->keySign;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setReplace(bool $flag): void
    {
        $this->replace = $flag;
    }

    /**
     * @return bool
     */
    public function getReplace(): bool
    {
        return $this->replace;
    }

    /**
     * @return bool
     */
    public function isReplace(): bool
    {
        return $this->getReplace();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setForce(bool $flag): void
    {
        $this->setReplace($flag);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setDelete(bool $flag): void
    {
        $this->delete = $flag;
    }

    /**
     * @return bool
     */
    public function getDelete(): bool
    {
        return $this->delete;
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->getDelete();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setVerify(bool $flag): void
    {
        $this->verify = $flag;
    }

    /**
     * @return bool
     */
    public function getVerify(): bool
    {
        return $this->verify;
    }

    /**
     * @return bool
     */
    public function isVerify(): bool
    {
        return $this->getVerify();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setList(bool $flag): void
    {
        $this->list = $flag;
    }

    /**
     * @return bool
     */
    public function getList(): bool
    {
        return $this->list;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return $this->getList();
    }

    /**
     * @param int $num
     *
     * @return void
     */
    public function setNum(int $num): void
    {
        $this->num = (int) $num;
    }

    /**
     * @return int
     */
    public function getNum(): int
    {
        return $this->num;
    }

    /**
     * @param string $commit
     *
     * @return void
     */
    public function setContains(string $commit): void
    {
        $this->contains = $commit;
    }

    /**
     * @return string
     */
    public function getContains(): string
    {
        return $this->contains;
    }

    /**
     * @param string $msg
     *
     * @return void
     */
    public function setMessage(string $msg): void
    {
        $this->message = $msg;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $file
     *
     * @return void
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $commit
     *
     * @return void
     */
    public function setCommit(string $commit): void
    {
        $this->commit = $commit;
    }

    /**
     * @return string
     */
    public function getCommit(): string
    {
        return $this->commit;
    }

    /**
     * @param string $object
     *
     * @return void
     */
    public function setObject(string $object): void
    {
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getObject(): string
    {
        return $this->object;
    }

    /**
     * @param string $pattern
     *
     * @return void
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $prop
     *
     * @return void
     */
    public function setOutputProperty(string $prop): void
    {
        $this->outputProperty = $prop;
    }
}
