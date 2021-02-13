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

namespace Phing\Task\System\Condition;

use Phing\Exception\BuildException;
use Phing\Phing;
use Phing\Util\StringHelper;

/**
 * Condition that tests the OS type.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class OsCondition implements Condition
{
    public const FAMILY_WINDOWS = 'windows';
    public const FAMILY_MAC = 'mac';
    public const FAMILY_UNIX = 'unix';

    private const DARWIN = 'darwin';

    private $family;

    public function setFamily(string $f)
    {
        $this->family = strtolower($f);
    }

    public function evaluate()
    {
        return self::isOS($this->family);
    }

    /**
     * Determines if the OS on which Ant is executing matches the
     * given OS family.
     *
     * @param  string $family the family to check for
     * @return true if the OS matches
     */
    public static function isFamily($family)
    {
        return self::isOS($family);
    }

    /**
     * @param string $family
     * @return bool
     * @throws BuildException
     */
    public static function isOS($family)
    {
        $osName = strtolower(Phing::getProperty('os.name'));

        if ($family !== null) {
            $isWindows = StringHelper::startsWith('win', $osName);

            if ($family === 'windows') {
                return $isWindows;
            }

            if ($family === 'win32') {
                return $isWindows && $osName === 'win32';
            }

            if ($family === 'winnt') {
                return $isWindows && $osName === 'winnt';
            }

            if ($family === self::FAMILY_MAC) {
                return (strpos($osName, self::FAMILY_MAC) !== false || strpos($osName, self::DARWIN) !== false);
            }

            if ($family === self::FAMILY_UNIX) {
                return (
                    StringHelper::endsWith('ix', $osName) ||
                    StringHelper::endsWith('ux', $osName) ||
                    StringHelper::endsWith('bsd', $osName) ||
                    StringHelper::startsWith('sunos', $osName) ||
                    StringHelper::startsWith(self::DARWIN, $osName)
                );
            }
            throw new BuildException("Don't know how to detect os family '" . $family . "'");
        }

        return false;
    }
}
