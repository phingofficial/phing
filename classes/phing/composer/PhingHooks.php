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

use Symfony\Component\Finder\Finder;

/**
 * PhingHooks.
 *
 * Sync custom phing tasks/types.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @package phing.composer
 */
class PhingHooks
{
    public static function syncronizeCustomPackages()
    {
        @unlink(__DIR__ . '/../../../custom.types.properties');
        @unlink(__DIR__ . '/../../../custom.tasks.properties');

        /** @var Finder $finder */
        $finder = Finder::create();
        $finder->files()->in(__DIR__ . '/../../../vendor')->name('custom.tasks.properties');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            file_put_contents(__DIR__ . '/../../../custom.tasks.properties', $file->getContents(), FILE_APPEND);
        }

        $finder = Finder::create();
        $finder->files()->in(__DIR__ . '/../../../vendor')->name('custom.types.properties');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            file_put_contents(__DIR__ . '/../../../custom.types.properties', $file->getContents(), FILE_APPEND);
        }
    }
}
