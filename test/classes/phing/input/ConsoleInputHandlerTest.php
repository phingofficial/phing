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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class ConsoleInputHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefaultValue(): void
    {
        $inputStream = $this->createStream([' ']);
        $output      = new NullOutput();
        $request     = new InputRequest('Enter a value');
        $request->setDefaultValue('default');
        $handler = new ConsoleInputHandler($inputStream, $output);

        $handler->handleInput($request);

        $this->assertEquals('default', $request->getInput());
    }

    /**
     * @return void
     */
    public function testMultipleChoiceQuestion(): void
    {
        $inputStream = $this->createStream(['choice1']);
        $output      = new NullOutput();
        $request     = new MultipleChoiceInputRequest('Enter a choice', ['choice1', 'choice2']);
        $handler     = new ConsoleInputHandler($inputStream, $output);

        $handler->handleInput($request);

        $this->assertEquals('choice1', $request->getInput());
    }

    /**
     * @return void
     */
    public function testYesNoQuestion(): void
    {
        $inputStream = $this->createStream(['no']);
        $output      = new NullOutput();
        $request     = new YesNoInputRequest('Enter a choice', ['yes', 'no']);
        $handler     = new ConsoleInputHandler($inputStream, $output);

        $handler->handleInput($request);

        $this->assertFalse($request->getInput());
    }

    /**
     * @param array $inputs
     *
     * @return resource|false
     */
    private function createStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, implode(PHP_EOL, $inputs));
        rewind($stream);
        return $stream;
    }
}
