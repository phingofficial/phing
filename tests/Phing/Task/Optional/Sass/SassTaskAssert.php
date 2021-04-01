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

namespace Phing\Test\Task\Optional\Sass;

use PHPUnit\Framework\Assert;
use Phing\Task\Ext\SassTask;

class SassTaskAssert extends Assert
{
    public function assertDefaults(SassTask $task): void
    {
        $this->assertEquals('', $task->getPath());
        $this->assertEquals('', $task->getOutputpath());
        $this->assertEquals('utf-8', $task->getEncoding());
        $this->assertEquals('nested', $task->getStyle());
        $this->assertEquals('css', $task->getNewext());
        $this->assertFalse($task->getTrace());
        $this->assertFalse($task->getCheck());
        $this->assertTrue($task->getUnixnewlines());
        $this->assertTrue($task->getKeepsubdirectories());
        $this->assertTrue($task->getRemoveoldext());
        $this->assertEquals('sass', $task->getExecutable(), "Executable is not 'sass'");
        $this->assertEquals('', $task->getExtfilter(), "Extfilter is not ''");
        $this->assertTrue($task->getRemoveoldext());
        $this->assertFalse($task->getCompressed());
        $this->assertFalse($task->getCompact());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertTrue($task->getNested());
    }

    public function assertCompactStyle(SassTask $task): void
    {
        $this->assertTrue($task->getCompact());
        $this->assertEquals('compact', $task->getStyle());
        $this->assertEquals('--style compact', $task->getFlags());
        $this->assertFalse($task->getCompressed());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertFalse($task->getNested());
    }

    public function assertCompressedStyle(SassTask $task): void
    {
        $this->assertTrue($task->getCompressed());
        $this->assertEquals('compressed', $task->getStyle());
        $this->assertEquals('--style compressed', $task->getFlags());
        $this->assertFalse($task->getCompact());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertFalse($task->getNested());
    }
}
