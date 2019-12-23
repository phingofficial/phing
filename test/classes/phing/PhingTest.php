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

use PHPUnit\Framework\TestCase;

/**
 * Core Phing class test
 * Do not know why there was no test at all
 *
 * // TODO implement all methods
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @package phing
 */
class PhingTest extends TestCase
{
    private const NAMESPACED_CLASS      = 'Vendor\\Package\\Sub_Package\\Separated_FullSeparatedClass';
    private const SEPARATED_CLASS       = 'Vendor_Package_SeparatedClass';
    private const DOTED_CLASS           = 'Vendor.Package.DotedClass';
    private const DOTED_CLASS_SHORTNAME = 'DotedClass';

    /**
     * Test a PSR-0 support of class loading
     *
     * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
     *
     * @return void
     */
    public function testImportPSR0(): void
    {
        // Test the namespace support
        $className = Phing::import(self::NAMESPACED_CLASS, self::getClassPath());
        self::assertEquals(self::NAMESPACED_CLASS, $className);
        self::assertTrue(class_exists(self::NAMESPACED_CLASS));

        // Test PEAR standard
        $className = Phing::import(self::SEPARATED_CLASS, self::getClassPath());
        self::assertEquals(self::SEPARATED_CLASS, $className);
        self::assertTrue(class_exists(self::SEPARATED_CLASS));
    }

    /**
     * Test the default dot separated class loading
     *
     * @return void
     */
    public function testImportDotPath(): void
    {
        $className = Phing::import(self::DOTED_CLASS, self::getClassPath());
        self::assertEquals(self::DOTED_CLASS_SHORTNAME, $className);
        self::assertTrue(class_exists(self::DOTED_CLASS_SHORTNAME));
    }

    /**
     * Test the convertShorthand function
     *
     * @return void
     */
    public function testConvertShorthand(): void
    {
        self::assertEquals(0, Phing::convertShorthand('0'));
        self::assertEquals(-1, Phing::convertShorthand('-1'));
        self::assertEquals(100, Phing::convertShorthand('100'));
        self::assertEquals(1024, Phing::convertShorthand('1k'));
        self::assertEquals(1024, Phing::convertShorthand('1K'));
        self::assertEquals(2048, Phing::convertShorthand('2K'));
        self::assertEquals(1048576, Phing::convertShorthand('1M'));
        self::assertEquals(1048576, Phing::convertShorthand('1m'));
        self::assertEquals(1073741824, Phing::convertShorthand('1G'));
        self::assertEquals(1073741824, Phing::convertShorthand('1g'));

        self::assertEquals(200, Phing::convertShorthand('200j'));
    }

    /**
     * @return void
     */
    public function testTimer(): void
    {
        $this->assertInstanceOf('Timer', Phing::getTimer());
    }

    /**
     * @return void
     */
    public function testFloatOnCurrentTimeMillis(): void
    {
        if (method_exists($this, 'assertIsFloat')) {
            $this->assertIsFloat(Phing::currentTimeMillis());
        } else {
            $this->assertInternalType('float', Phing::currentTimeMillis());
        }
    }

    /**
     * @return void
     *
     * @throws ConfigurationException
     */
    public function testGetPhingVersion(): void
    {
        $this->assertStringStartsWith('Phing ', Phing::getPhingVersion());
    }

    /**
     * @return void
     *
     * @requires PHP >= 7.2
     */
    public function testPrintTargets(): void
    {
        $target = $this->getMockBuilder(Target::class)->getMock();
        $target->method('getDependencies')->willReturn([]);
        $project = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $project->method('getTargets')->willReturn([$target]);
        $phing = new Phing();
        $phing::setOutputStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        $phing->printTargets($project);

        $this->assertSame(1, 1);
    }

    /**
     * @return void
     *
     * @requires PHP >= 7.2
     */
    public function testPrintUsage(): void
    {
        $phing = new Phing();
        $phing::setErrorStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        $phing::printUsage();

        $this->assertSame(1, 1);
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws Exception
     */
    public function testCallStartupShutdown(): void
    {
        Phing::startup();
        Phing::shutdown();

        $this->assertSame(1, 1);
    }

    /**
     * @return void
     */
    public function testCurrentProject(): void
    {
        $project  = new Project();
        $currProj = Phing::getCurrentProject();
        $this->assertNotSame($project, $currProj);

        Phing::setCurrentProject($project);
        $this->assertSame($project, Phing::getCurrentProject());

        Phing::unsetCurrentProject();
        $this->assertNull(Phing::getCurrentProject());
    }

    /**
     * Get fixtures classpath
     *
     * @return string Classpath
     */
    private static function getClassPath(): string
    {
        return __DIR__ . '/../../etc/importclasses';
    }
}
