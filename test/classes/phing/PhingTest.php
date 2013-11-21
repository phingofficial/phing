<?php
/*
 *  $Id$
 *
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

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../classes/phing/Phing.php';

/**
 * Core Phing class test
 * Do not know why there was no test at all
 *
 * // TODO implement all methods
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @version $Revision: $
 * @package phing
 */
class PhingTest  extends PHPUnit_Framework_TestCase {

    const NAMESPACED_CLASS = 'Vendor\\Package\\Sub_Package\\Separated_FullSeparatedClass';
    const SEPARATED_CLASS = 'Vendor_Package_SeparatedClass';
    const DOTED_CLASS = 'Vendor.Package.DotedClass';
    const DOTED_CLASS_SHORTNAME = 'DotedClass';

    protected $classpath;

    /**
     * Test a PSR-0 support of class loading
     * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
     */
    public function testImportPSR0() {
        // Test the namespace support only if PHP >= 5.3
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            $className = Phing::import(self::NAMESPACED_CLASS, self::getClassPath());
            self::assertEquals(self::NAMESPACED_CLASS, $className);
            self::assertTrue(class_exists(self::NAMESPACED_CLASS));
        }

        // Test PEAR stadard
        $className = Phing::import(self::SEPARATED_CLASS, self::getClassPath());
        self::assertEquals(self::SEPARATED_CLASS, $className);
        self::assertTrue(class_exists(self::SEPARATED_CLASS));
    }

    /**
     * Test the default dot separated class loading
     */
    public function testImportDotPath() {
        $className = Phing::import(self::DOTED_CLASS, self::getClassPath());
        self::assertEquals(self::DOTED_CLASS_SHORTNAME, $className);
        self::assertTrue(class_exists(self::DOTED_CLASS_SHORTNAME));
    }

    /**
     * Get fixtures classpath
     *
     * @return string Classpath
     */
    protected static function getClassPath()
    {
        return dirname(__FILE__) . '/../../etc/importclasses';
    }
}
