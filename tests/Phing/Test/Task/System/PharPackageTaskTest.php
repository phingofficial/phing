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

namespace Phing\Test\Task\System;

use Phar;
use Phing\Test\Support\BuildFileTest;

/**
 * Tests for PharPackageTask.
 *
 * @author François Poirotte <clicky@erebot.net>
 *
 * @internal
 */
class PharPackageTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if ('1' == ini_get('phar.readonly')) {
            $this->markTestSkipped('This test require phar.readonly php.ini setting to be disabled');
        }

        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/build.xml');
    }

    public function tearDown(): void
    {
        @unlink(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/priv.key');
        @unlink(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/pharpackage.phar.pubkey');
        @unlink(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/pass.txt');
        @unlink(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/pharpackage.phar');
        @unlink(PHING_TEST_BASE . '/etc/tasks/system/pharpackage/package.phar');
    }

    public function testPharPackage(): void
    {
        $this->executeTarget(__FUNCTION__);

        $dest = PHING_TEST_BASE . '/etc/tasks/system/pharpackage/package.phar';
        $this->assertFileExists($dest);
        $this->assertLogLineContaining('Building package');
    }

    public function testOpenSSLSignature(): void
    {
        // Generate a private key on the fly.
        $passphrase = uniqid('', true);
        $passfile = PHING_TEST_BASE . '/etc/tasks/system/pharpackage/pass.txt';
        file_put_contents($passfile, $passphrase);
        $pkey = openssl_pkey_new();
        openssl_pkey_export_to_file(
            $pkey,
            PHING_TEST_BASE . '/etc/tasks/system/pharpackage/priv.key',
            $passphrase
        );
        $this->executeTarget(__FUNCTION__);

        // Make sure we are dealing with an OpenSSL signature.
        // (Phar silently falls back to an SHA1 signature
        // whenever it fails to add an OpenSSL signature)
        $dest = PHING_TEST_BASE . '/etc/tasks/system/pharpackage/pharpackage.phar';
        $this->assertFileExists($dest);
        $phar = new Phar($dest);
        $signature = $phar->getSignature();
        $this->assertEquals('OpenSSL', $signature['hash_type']);
    }
}
