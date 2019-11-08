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

    /**
     * Test the convertShorthand function
     */
    public function testConvertShorthand()
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

    public function testTimer()
    {
        self::assertInstanceOf('Timer', Phing::getTimer());
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

    public function testPrintTargets()
    {
        $target = $this->getMockBuilder(Target::class)->getMock();
        $project = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $project->method('getTargets')->willReturn([$target]);
        $phing = new Phing();
        $phing::setOutputStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        self::assertNull($phing->printTargets($project));
    }

    public function testPrintUsage(): void
    {
        $phing = new Phing();
        $phing::setErrorStream($this->getMockBuilder(OutputStream::class)->disableOriginalConstructor()->getMock());

        self::assertNull($phing::printUsage());
    }

    public function testCallStartupShutdown()
    {
        self::assertNull(Phing::startup());
        self::assertNull(Phing::shutdown());
    }

    public function testCurrentProject()
    {
        $project = new Project();
        $currProj = Phing::getCurrentProject();
        $this->assertNotSame($project, $currProj);

        Phing::setCurrentProject($project);
        self::assertSame($project, Phing::getCurrentProject());

        Phing::unsetCurrentProject();
        self::assertNull(Phing::getCurrentProject());
    }

    /**
     * Get fixtures classpath
     *
     * @return string Classpath
     */
    protected static function getClassPath()
    {
        return __DIR__ . '/../../etc/importclasses';
    }
}
