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
 * @author Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext
 */
class FileHashTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/filehash.xml");
    }

    public function tearDown(): void
    {
        if (file_exists(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.crc32")) {
            unlink(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.crc32");
        }
        if (file_exists(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.md5")) {
            unlink(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.md5");
        }
        if (file_exists(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.sha1")) {
            unlink(PHING_TEST_BASE . "/etc/tasks/ext/filehash.bin.sha1");
        }
    }

    public function testMD5()
    {
        $this->expectLog("testMD5", "c9dcdf095de0ef3d2e3f71cb4dc7ee11");
    }

    public function testSHA1()
    {
        $this->expectLog("testSHA1", "dadd0aafb79d9fb8299a928efb23c112874bbda3");
    }

    public function testCRC32()
    {
        $this->expectLog("testCRC32", "d34c2e86");
    }
}
