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

namespace Phing\Test;

use Phing\Io\OutputStream;
use Phing\Phing;
use Phing\Project;
use Phing\Target;
use Phing\Util\DefaultClock;
use PHPUnit\Framework\TestCase;

/**
 * Core Phing class test
 * Do not know why there was no test at all.
 *
 * // TODO implement all methods
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 *
 * @internal
 */
class PhingTest extends TestCase
{
    private const NAMESPACED_CLASS = 'Vendor\\Package\\FullSeparatedClass';
    private const SEPARATED_CLASS = 'Vendor_Package_SeparatedClass';

    protected $classpath;

    /**
     * Test a PSR-0 support of class loading.
     *
     * @see http://groups.google.com/group/php-standards/web/psr-0-final-proposal
     */
    public function testImportPSR0(): void
    {
        // Test the namespace support
        $className = Phing::import(self::NAMESPACED_CLASS, self::getClassPath());
        self::assertEquals(self::NAMESPACED_CLASS, $className);
        self::assertTrue(class_exists(self::NAMESPACED_CLASS));
    }

    public function testImportPEAR(): void
    {
        // Test PEAR standard
        $className = Phing::import(self::SEPARATED_CLASS, self::getClassPath());
        self::assertEquals(self::SEPARATED_CLASS, $className);
        self::assertTrue(class_exists(self::SEPARATED_CLASS));
    }

    public function testTimer(): void
    {
        $this->assertInstanceOf(DefaultClock::class, Phing::getTimer());
    }

    public function testGetPhingVersion(): void
    {
        $this->assertStringStartsWith('Phing ', Phing::getPhingVersion());
    }

    public function testPrintTargets(): void
    {
        $target = $this->getMockBuilder(Target::class)->getMock();
        $target->method('getDependencies')->willReturn([]);
        $project = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $project->method('getTargets')->willReturn([$target]);
        $phing = new Phing();
        $phing::setOutputStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        $project->expects($this->atLeastOnce())->method('log');

        $phing->printTargets($project);
    }

    public function testPrintUsage(): void
    {
        $phing = new Phing();
        $stream = $this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock();
        $phing::setErrorStream($stream);

        $stream->expects($this->once())->method('write');

        $phing::printUsage();
    }

    public function testCallStartupShutdown(): void
    {
        Phing::startup();
        self::assertTrue(Phing::getTimer()->isRunning());
        Phing::shutdown();
        self::assertFalse(Phing::getTimer()->isRunning());
    }

    public function testCurrentProject(): void
    {
        $project = new Project();
        $currProj = Phing::getCurrentProject();
        $this->assertNotSame($project, $currProj);

        Phing::setCurrentProject($project);
        $this->assertSame($project, Phing::getCurrentProject());

        Phing::unsetCurrentProject();
        $this->assertNull(Phing::getCurrentProject());
    }

    /**
     * Get fixtures classpath.
     *
     * @return string Classpath
     */
    protected static function getClassPath(): string
    {
        return __DIR__ . '/../../etc/importclasses';
    }
}
