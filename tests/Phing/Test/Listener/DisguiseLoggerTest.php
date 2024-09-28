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
    public function maskOutput()
    {
        $event = new BuildEvent(new Project());
        $event->setMessage('https://foo:bar@example.com', $event->getPriority());
        $this->assertNull($this->logger->messageLogged($event));
    }
}
