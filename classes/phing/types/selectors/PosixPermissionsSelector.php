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
 * A selector that selects files based on their POSIX permissions.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.types.selectors
 */
class PosixPermissionsSelector implements FileSelector
{
    /** @var string $permissions */
    private $permissions;

    /**
     * Sets the permissions to look for.
     * @param string $permissions the permissions string (rwxrwxrwx or octal)
     */
    public function setPermissions($permissions): void
    {
        $this->validate($permissions);

        if (strlen($permissions) === 3 && ctype_digit($permissions)) {
            $this->permissions = $permissions;
            return;
        }

        $this->permissions .= implode(
            '',
            array_map(
                'array_sum',
                array_chunk(
                    str_split(
                        strtr(
                            $permissions,
                            array_combine(
                                ['r', 'w', 'x', '-'],
                                [4, 2, 1, 0]
                            )
                        )
                    ),
                    3
                )
            )
        );
    }

    private function validate(string $permissions): void
    {
        if (
            preg_match('/^[0-7]{3}$/', $permissions) !== 1 &&
            preg_match('/^[r-][w-][x-][r-][w-][x-][r-][w-][x-]$/', $permissions) !== 1
        ) {
            throw new BuildException("the permissions attribute {$permissions} is invalid");
        }
    }

    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {
        if ($this->permissions === null) {
            throw new BuildException('the permissions attribute is required');
        }

        return (fileperms($file->getPath()) & 0777) == sprintf('%03o', $this->permissions);
    }
}
