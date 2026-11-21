<?php

namespace Phing\Test\Listener;

use Phing\Listener\BuildEvent;
use Phing\Listener\DisguiseLogger;
use Phing\Project;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DisguiseLoggerTest extends TestCase
{
    private DisguiseLogger $logger;

    public function setUp(): void
    {
        $this->logger = new DisguiseLogger();
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function maskOutput()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('connecting to https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->messageLogged($event));
        $this->assertSame('connecting to https://foo:*****@example.com', $event->getMessage());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function masksGitHubTokensAndCommonSecretFormats(): void
    {
        $event = new BuildEvent(new Project());
        $event->setMessage(
            'tokens ghp_abcdefghijklmnopqrstuvwxyz0123456789 and gho_1234567890; Authorization: Bearer abc.def_123; password="s3cr3t" api_key=key-value',
            $event->getPriority()
        );

        $this->logger->messageLogged($event);

        $this->assertSame(
            'tokens ghp_***** and gho_*****; Authorization: Bearer *****; password="*****" api_key=*****',
            $event->getMessage()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function leavesUnlabelledOrdinaryTextAlone(): void
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('password policy tokenization api-key-name=; ordinary value', $event->getPriority());
        $this->logger->messageLogged($event);
        $this->assertSame('password policy tokenization api-key-name=; ordinary value', $event->getMessage());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function buildStarted()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->buildStarted($event));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function buildFinished()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->buildFinished($event));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function targetStarted()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->targetStarted($event));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function targetFinished()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->targetFinished($event));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function taskStarted()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->taskStarted($event));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function taskFinished()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->taskFinished($event));
    }
}
