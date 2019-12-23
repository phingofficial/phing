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

use PHPUnit\Framework\Assert;

class PHPStanTaskAssert extends Assert
{
    /**
     * @param PHPStanTask $task
     *
     * @return void
     */
    public function assertDefaults(PHPStanTask $task): void
    {
        $this->assertEquals('phpstan', $task->getExecutable());
        $this->assertEquals('analyse', $task->getCommand());

        $this->assertCommonDefaults($task);
        $this->assertAnalyseDefaults($task);
        $this->assertHelpDefaults($task);
        $this->assertListDefaults($task);
    }

    /**
     * @param PHPStanTask $task
     *
     * @return void
     */
    private function assertCommonDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->isHelp());
        $this->assertNull($task->isQuiet());
        $this->assertNull($task->isVersion());
        $this->assertNull($task->isANSI());
        $this->assertNull($task->isNoANSI());
        $this->assertNull($task->isNoInteraction());
        $this->assertNull($task->isVerbose());
        $this->assertNull($task->isCheckreturn());
    }

    /**
     * @param PHPStanTask $task
     *
     * @return void
     */
    private function assertAnalyseDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getConfiguration());
        $this->assertNull($task->getLevel());
        $this->assertNull($task->isNoProgress());
        $this->assertNull($task->isDebug());
        $this->assertNull($task->getAutoloadFile());
        $this->assertNull($task->getErrorFormat());
        $this->assertNull($task->getMemoryLimit());
        $this->assertNull($task->getPaths());
    }

    /**
     * @param PHPStanTask $task
     *
     * @return void
     */
    private function assertHelpDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getFormat());
        $this->assertNull($task->isRaw());
        $this->assertNull($task->getCommandName());
    }

    /**
     * @param PHPStanTask $task
     *
     * @return void
     */
    private function assertListDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getFormat());
        $this->assertNull($task->isRaw());
        $this->assertNull($task->getNamespace());
    }
}
