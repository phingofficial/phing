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
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->messageLogged($event));
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
