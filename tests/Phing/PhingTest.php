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

namespace Phing;

use Phing\Io\OutputStream;
use Phing\Util\Timer;

/**
 * Core Phing class test
 * Do not know why there was no test at all
 *
 * // TODO implement all methods
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @package phing
 */
class PhingTest extends \PHPUnit\Framework\TestCase
{
    private const NAMESPACED_CLASS = 'Vendor\\Package\\Sub_Package\\Separated_FullSeparatedClass';
    private const SEPARATED_CLASS = 'Vendor_Package_SeparatedClass';
    private const DOTED_CLASS = 'Vendor.Package.DotedClass';
    private const DOTED_CLASS_SHORTNAME = 'DotedClass';

    protected $classpath;

    /**
     * Test a PSR-0 support of class loading
     * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
     */
    public function testImportPSR0()
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
     */
    public function testImportDotPath()
    {
        $className = Phing::import(self::DOTED_CLASS, self::getClassPath());
        self::assertEquals(self::DOTED_CLASS_SHORTNAME, $className);
        self::assertTrue(class_exists(self::DOTED_CLASS_SHORTNAME));
    }

    public function testTimer()
    {
        $this->assertInstanceOf(Timer::class, Phing::getTimer());
    }

    public function testFloatOnCurrentTimeMillis()
    {
        if (method_exists($this, 'assertIsFloat')) {
            $this->assertIsFloat(Phing::currentTimeMillis());
        } else {
            $this->assertInternalType('float', Phing::currentTimeMillis());
        }
    }

    public function testGetPhingVersion()
    {
        $this->assertStringStartsWith('Phing ', Phing::getPhingVersion());
    }

    /**
     * @requires PHP >= 7.2
     */
    public function testPrintTargets()
    {
        $target = $this->getMockBuilder(Target::class)->getMock();
        $target->method('getDependencies')->willReturn([]);
        $project = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $project->method('getTargets')->willReturn([$target]);
        $phing = new Phing();
        $phing::setOutputStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        $this->assertNull($phing->printTargets($project));
    }

    /**
     * @requires PHP >= 7.2
     */
    public function testPrintUsage(): void
    {
        $phing = new Phing();
        $phing::setErrorStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        $this->assertNull($phing::printUsage());
    }

    public function testCallStartupShutdown()
    {
        $this->assertNull(Phing::startup());
        $this->assertNull(Phing::shutdown());
    }

    public function testCurrentProject()
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
     * Get fixtures classpath
     *
     * @return string Classpath
     */
    protected static function getClassPath()
    {
        return __DIR__ . '/../etc/importclasses';
    }
}
