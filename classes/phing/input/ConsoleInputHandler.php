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

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Uses Symfony Console to present questions
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.input
 */
class ConsoleInputHandler implements InputHandler
{
    /**
     * @var resource
     */
    private $inputStream;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param resource        $inputStream
     * @param OutputInterface $output
     */
    public function __construct($inputStream, OutputInterface $output)
    {
        $this->inputStream = $inputStream;
        $this->output      = $output;
    }

    /**
     * Handle the request encapsulated in the argument.
     *
     * <p>Precondition: the request.getPrompt will return a non-null
     * value.</p>
     *
     * <p>Postcondition: request.getInput will return a non-null
     * value, request.isInputValid will return true.</p>
     *
     * @param InputRequest $request
     *
     * @return void
     */
    public function handleInput(InputRequest $request): void
    {
        $questionHelper = new QuestionHelper();
        if (method_exists($questionHelper, 'setInputStream')) {
            $questionHelper->setInputStream($this->inputStream);
        }

        $question = $this->getQuestion($request);

        if ($request->isHidden()) {
            $question->setHidden(true);
        }

        $input = new StringInput('');
        if (method_exists($input, 'setStream')) {
            $input->setStream($this->inputStream);
        }

        $result = $questionHelper->ask($input, $this->output, $question);

        $request->setInput((string) $result);
    }

    /**
     * @param InputRequest $inputRequest
     *
     * @return Question
     */
    protected function getQuestion(InputRequest $inputRequest): Question
    {
        $prompt = $this->getPrompt($inputRequest);

        if ($inputRequest instanceof YesNoInputRequest) {
            return new ConfirmationQuestion($prompt);
        }

        if ($inputRequest instanceof MultipleChoiceInputRequest) {
            return new ChoiceQuestion($prompt, $inputRequest->getChoices(), $inputRequest->getDefaultValue());
        }

        return new Question($prompt, $inputRequest->getDefaultValue());
    }

    /**
     * @param InputRequest $inputRequest
     *
     * @return string
     */
    protected function getPrompt(InputRequest $inputRequest): string
    {
        $prompt       = $inputRequest->getPrompt();
        $defaultValue = $inputRequest->getDefaultValue();

        if ($defaultValue !== null) {
            if ($inputRequest instanceof YesNoInputRequest) {
                $defaultValue = $inputRequest->getChoices()[$defaultValue];
            }

            $prompt .= ' [' . $defaultValue . ']';
        }

        $pchar = $inputRequest->getPromptChar();

        return $prompt . ($pchar ? $pchar . ' ' : ' ');
    }
}
