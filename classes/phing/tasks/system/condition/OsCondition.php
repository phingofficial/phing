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
 * Condition that tests the OS type.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @package   phing.tasks.system.condition
 */
class OsCondition implements Condition
{
    public const FAMILY_WINDOWS = 'windows';
    public const FAMILY_9X = 'win9x';
    public const FAMILY_NT = 'winnt';
    public const FAMILY_OS2 = 'os/2';
    public const FAMILY_NETWARE = 'netware';
    public const FAMILY_DOS = 'dos';
    public const FAMILY_MAC = 'mac';
    public const FAMILY_TANDEM = 'tandem';
    public const FAMILY_UNIX = 'unix';
    public const FAMILY_ZOS = 'z/os';
    public const FAMILY_OS400 = 'os/400';

    private const DARWIN = 'darwin';

    private $family;

    /**
     * @param $f
     */
    public function setFamily($f)
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
     * @param string $family the family to check for
     * @return true if the OS matches
     */
    public static function isFamily($family)
    {
        return self::isOS($family);
    }

    /**
     * @param string $family
     * @return bool
     * @throws \BuildException
     */
    public static function isOS($family)
    {
        $osName = strtolower(Phing::getProperty('os.name'));

        if ($family !== null) {
            if ($family === self::FAMILY_WINDOWS) {
                return StringHelper::startsWith('win', $osName);
            }

            if ($family === self::FAMILY_MAC) {
                return (strpos($osName, self::FAMILY_MAC) !== false || strpos($osName, self::DARWIN) !== false);
            }

            if ($family === self::FAMILY_NETWARE) {
                return (strpos($osName, self::FAMILY_NETWARE) !== false);
            }

            if ($family === self::FAMILY_DOS) {
                return PATH_SEPARATOR === ';' && self::isFamily(self::FAMILY_NETWARE);
            }

            if ($family === 'unix') {
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
